<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Http\{Request, JsonResponse};
use Illuminate\Support\Facades\{Http, Storage, Cache};

class TranslateController extends Controller
{
    // MyMemory gratuit : 500 caractères max par requête
    private const CHUNK_SIZE = 450;

    public function translate(Request $request, Book $book): JsonResponse
    {
        $data = $request->validate([
            'page'        => 'required|integer|min:1',
            'target_lang' => 'required|string|max:10',
        ]);

        $targetLang = strtolower($data['target_lang']);
        $page       = (int) $data['page'];

        // ── 1. Extraire le texte de la page PDF ──────────────────────────────
        $pageText = $this->extractPageText($book, $page);

        // Fallback sur la description si l'extraction échoue
        if (empty(trim($pageText))) {
            $pageText = $book->description ?? $book->title ?? '';
        }

        if (empty(trim($pageText))) {
            return response()->json(['success' => false, 'message' => 'Aucun texte à traduire.'], 422);
        }

        // ── 2. Cache (évite de retraduire la même page) ──────────────────────
        $cacheKey = 'translate_v2_' . md5($book->id . '|' . $page . '|' . $targetLang);
        if ($cached = Cache::get($cacheKey)) {
            return response()->json(['success' => true, 'data' => $cached + ['cached' => true]]);
        }

        // ── 3. Détecter la langue source depuis le texte réel ────────────────
        $sourceLang = $this->detectLanguage($pageText);

        // Si source == cible, pas besoin de traduire
        if ($sourceLang === $targetLang) {
            return response()->json(['success' => true, 'data' => [
                'original'    => $pageText,
                'translated'  => $pageText,
                'source_lang' => $sourceLang,
                'target_lang' => $targetLang,
                'page'        => $page,
                'cached'      => false,
                'note'        => 'Le contenu est déjà dans la langue cible.',
            ]]);
        }

        // ── 4. Traduire via MyMemory (gratuit, sans clé) ─────────────────────
        try {
            $translated = $this->translateWithMyMemory($pageText, $sourceLang, $targetLang);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de traduction : ' . $e->getMessage(),
            ], 502);
        }

        $result = [
            'original'    => $pageText,
            'translated'  => $translated,
            'source_lang' => $sourceLang,
            'target_lang' => $targetLang,
            'page'        => $page,
            'cached'      => false,
        ];

        Cache::put($cacheKey, $result, now()->addHours(24));

        return response()->json(['success' => true, 'data' => $result]);
    }

    // ── Extraction texte PDF ──────────────────────────────────────────────────

    private function extractPageText(Book $book, int $page): string
    {
        if (!$book->file_path || !Storage::disk('local')->exists($book->file_path)) {
            return '';
        }

        try {
            if (!class_exists('\Smalot\PdfParser\Parser')) {
                return '';
            }
            $pdfPath = Storage::disk('local')->path($book->file_path);
            $parser  = new \Smalot\PdfParser\Parser();
            $pdf     = $parser->parseFile($pdfPath);
            $pages   = $pdf->getPages();
            $idx     = $page - 1;

            if (!isset($pages[$idx])) return '';

            $text = trim($pages[$idx]->getText());
            // Nettoyer les artefacts PDF
            $text = preg_replace('/\s+/', ' ', $text);
            $text = preg_replace('/[^\x20-\x7E\x80-\xFF\n]/u', '', $text);

            return $text;
        } catch (\Exception $e) {
            return '';
        }
    }

    // ── MyMemory API (gratuit, ~1000 mots/jour sans clé) ─────────────────────

    private function translateWithMyMemory(string $text, string $from, string $to): string
    {
        // Découper en morceaux de 450 chars max
        $chunks = $this->splitText($text, self::CHUNK_SIZE);
        $parts  = [];

        foreach ($chunks as $chunk) {
            if (empty(trim($chunk))) continue;

            $response = Http::timeout(15)
                ->retry(2, 500)
                ->get('https://api.mymemory.translated.net/get', [
                    'q'        => $chunk,
                    'langpair' => "{$from}|{$to}",
                    'de'       => 'lirex@lirex.africa', // email optionnel → 10 000 mots/jour
                ]);

            if (!$response->ok()) {
                throw new \Exception("MyMemory HTTP {$response->status()}");
            }

            $json = $response->json();

            // Vérifier quota dépassé
            if (isset($json['responseStatus']) && $json['responseStatus'] === 429) {
                throw new \Exception('Quota journalier MyMemory atteint. Réessayez demain.');
            }

            $translated = $json['responseData']['translatedText'] ?? $chunk;

            // MyMemory renvoie parfois "PLEASE SELECT TWO DISTINCT LANGUAGES" si langues identiques
            if (str_contains(strtoupper($translated), 'PLEASE SELECT')) {
                $translated = $chunk;
            }

            $parts[] = $translated;
        }

        return implode(' ', $parts);
    }

    // ── Découpage intelligent par phrase ─────────────────────────────────────

    private function splitText(string $text, int $maxLen): array
    {
        if (strlen($text) <= $maxLen) return [$text];

        $chunks    = [];
        $sentences = preg_split('/(?<=[.!?;])\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        $current   = '';

        foreach ($sentences as $sentence) {
            if (strlen($current) + strlen($sentence) + 1 <= $maxLen) {
                $current .= ($current ? ' ' : '') . $sentence;
            } else {
                if ($current) $chunks[] = $current;
                // Si la phrase seule dépasse la limite, la couper par mots
                if (strlen($sentence) > $maxLen) {
                    $words   = explode(' ', $sentence);
                    $current = '';
                    foreach ($words as $word) {
                        if (strlen($current) + strlen($word) + 1 <= $maxLen) {
                            $current .= ($current ? ' ' : '') . $word;
                        } else {
                            if ($current) $chunks[] = $current;
                            $current = $word;
                        }
                    }
                } else {
                    $current = $sentence;
                }
            }
        }

        if ($current) $chunks[] = $current;

        return $chunks;
    }

    // ── Détection automatique de la langue du texte ──────────────────────────

    private function detectLanguage(string $text): string
    {
        $text = strtolower($text);
        $words = preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);

        // Mots-outils fréquents par langue
        $stopwords = [
            'en' => ['the','and','of','to','a','is','in','that','for','it','with','as','be',
                     'this','by','are','was','or','an','at','from','which','has','have','on'],
            'fr' => ['le','la','les','et','de','du','une','en','que','qui','il','elle','est',
                     'dans','pour','par','avec','sur','au','des','nous','vous','leur','ils'],
            'es' => ['el','la','los','las','de','que','en','y','un','una','se','por','con','al'],
            'pt' => ['o','a','os','as','de','que','em','e','um','uma','se','por','com','ao'],
            'ar' => ['في','من','إلى','على','هذا','هذه','التي','الذي','مع','عن','أن','كان'],
            'de' => ['der','die','das','und','in','von','zu','den','ist','sich','mit','auf','dem'],
            'ru' => ['в','и','на','не','что','с','по','это','а','из','как','но','он','она'],
            'zh' => ['的','了','在','是','我','有','和','就','不','人','都','一','个','上'],
        ];

        $scores = [];
        foreach ($stopwords as $lang => $sw) {
            $scores[$lang] = 0;
            foreach ($words as $word) {
                if (in_array($word, $sw, true)) $scores[$lang]++;
            }
        }

        // Retourner la langue avec le score le plus élevé, défaut 'en'
        arsort($scores);
        $top = array_key_first($scores);
        return ($scores[$top] > 0) ? $top : 'en';
    }

    // ── Normalisation codes de langue ─────────────────────────────────────────

    private function mapLang(string $lang): string
    {
        return match(strtolower($lang)) {
            'french', 'francais', 'français' => 'fr',
            'english', 'anglais'             => 'en',
            'spanish', 'espagnol'            => 'es',
            'portuguese', 'portugais'        => 'pt',
            'arabic', 'arabe'               => 'ar',
            'german', 'allemand'            => 'de',
            'russian', 'russe'              => 'ru',
            'chinese', 'chinois'            => 'zh',
            default                         => strtolower(substr($lang, 0, 2)),
        };
    }
}
