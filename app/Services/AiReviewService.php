<?php
namespace App\Services;

use App\Models\{Book, AiReview};
use Illuminate\Support\Facades\{Http, Log, Storage};

class AiReviewService
{
    private ?string $apiKey;
    private string $model = 'claude-opus-4-6';

    public function __construct()
    {
        $this->apiKey = config('services.anthropic.api_key') ?? null;
    }

    private function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * Lance l'analyse IA d'un livre soumis.
     * Extrait les métadonnées + les premières pages du PDF pour analyse.
     */
    public function analyze(Book $book): AiReview
    {
        $review = AiReview::updateOrCreate(
            ['book_id' => $book->id],
            ['status'  => 'processing']
        );

        if (!$this->isConfigured()) {
            $review->update([
                'status' => 'failed',
                'notes'  => 'Clé API Anthropic non configurée. Ajoutez ANTHROPIC_API_KEY dans le fichier .env.',
            ]);
            return $review->fresh();
        }

        try {
            // Prépare le contexte du livre pour l'IA
            $context = $this->buildContext($book);

            // Appel Claude API
            $response = Http::withHeaders([
                'x-api-key'         => $this->apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type'      => 'application/json',
            ])->timeout(120)->post('https://api.anthropic.com/v1/messages', [
                'model'      => $this->model,
                'max_tokens' => 2000,
                'system'     => $this->systemPrompt(),
                'messages'   => [
                    ['role' => 'user', 'content' => $context],
                ],
            ]);

            if (!$response->successful()) {
                throw new \Exception('Anthropic API error: ' . $response->body());
            }

            $text   = $response->json('content.0.text', '');
            $parsed = $this->parseResponse($text);

            $review->update(array_merge($parsed, [
                'status'      => 'completed',
                'analyzed_at' => now(),
            ]));

        } catch (\Exception $e) {
            Log::error('AI Review failed for book #' . $book->id . ': ' . $e->getMessage());
            $review->update(['status' => 'failed']);
        }

        return $review->fresh();
    }

    private function buildContext(Book $book): string
    {
        $type     = $book->document_type ?? 'roman';
        $lang     = $book->language;
        $isbn     = $book->isbn ?? 'Non fourni';
        $pages    = $book->pages ?? 'Inconnu';
        $year     = $book->publication_year ?? date('Y');
        $field    = $book->field_of_study ?? 'Non précisé';
        $univ     = $book->university ?? 'Non précisé';
        $keywords = $book->keywords ?? 'Non précisé';
        $desc     = mb_substr($book->description ?? '', 0, 1500);

        return <<<PROMPT
Tu es un expert en analyse éditoriale et normes académiques internationales.

Analyse le document soumis sur la plateforme LireX avec les informations suivantes :

**Titre :** {$book->title}
**Type de document :** {$type}
**Langue :** {$lang}
**ISBN :** {$isbn}
**Pages :** {$pages}
**Année :** {$year}
**Domaine d'étude :** {$field}
**Université/Institution :** {$univ}
**Mots-clés :** {$keywords}
**Description/Résumé :**
{$desc}

Effectue une analyse complète et retourne UNIQUEMENT un JSON valide avec cette structure exacte :
{
  "score_overall": <0-100>,
  "score_originality": <0-100>,
  "score_structure": <0-100>,
  "score_language": <0-100>,
  "score_norms": <0-100>,
  "summary": "<résumé de l'analyse en 2-3 phrases>",
  "issues": ["<problème 1>", "<problème 2>"],
  "suggestions": ["<suggestion 1>", "<suggestion 2>"],
  "isbn_valid": <true|false|null>,
  "detected_language": "<code langue>",
  "detected_document_type": "<type détecté>",
  "plagiarism_flag": <true|false>,
  "plagiarism_score": <0-100>,
  "recommendation": "<approve|review|reject>"
}

Critères d'évaluation :
- score_originality : originalité du contenu, absence de plagiat apparent
- score_structure : organisation, chapitres, introduction, conclusion, bibliographie
- score_language : qualité linguistique, orthographe, grammaire
- score_norms : respect des normes (APA/MLA/ISO si académique, ISBN si applicable)
- recommendation : approve (>70), review (40-70), reject (<40 ou plagiat détecté)
PROMPT;
    }

    private function parseResponse(string $text): array
    {
        // Extraire le JSON de la réponse
        preg_match('/\{[\s\S]*\}/m', $text, $matches);
        if (empty($matches)) {
            return $this->defaultResponse();
        }

        try {
            $data = json_decode($matches[0], true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            return $this->defaultResponse();
        }

        return [
            'score_overall'          => max(0, min(100, (int)($data['score_overall'] ?? 50))),
            'score_originality'      => max(0, min(100, (int)($data['score_originality'] ?? 50))),
            'score_structure'        => max(0, min(100, (int)($data['score_structure'] ?? 50))),
            'score_language'         => max(0, min(100, (int)($data['score_language'] ?? 50))),
            'score_norms'            => max(0, min(100, (int)($data['score_norms'] ?? 50))),
            'summary'                => substr($data['summary'] ?? '', 0, 1000),
            'issues'                 => array_slice((array)($data['issues'] ?? []), 0, 10),
            'suggestions'            => array_slice((array)($data['suggestions'] ?? []), 0, 10),
            'isbn_valid'             => isset($data['isbn_valid']) ? (bool)$data['isbn_valid'] : null,
            'detected_language'      => substr($data['detected_language'] ?? '', 0, 10),
            'detected_document_type' => substr($data['detected_document_type'] ?? '', 0, 50),
            'plagiarism_flag'        => (bool)($data['plagiarism_flag'] ?? false),
            'plagiarism_score'       => max(0, min(100, (float)($data['plagiarism_score'] ?? 0))),
            'recommendation'         => in_array($data['recommendation'] ?? '', ['approve','review','reject'])
                                        ? $data['recommendation'] : 'review',
        ];
    }

    private function defaultResponse(): array
    {
        return [
            'score_overall' => 50, 'score_originality' => 50,
            'score_structure' => 50, 'score_language' => 50, 'score_norms' => 50,
            'summary'         => "Analyse automatique non disponible. Révision manuelle requise.",
            'issues'          => [], 'suggestions'  => [],
            'isbn_valid'      => null, 'detected_language' => null,
            'detected_document_type' => null,
            'plagiarism_flag' => false, 'plagiarism_score' => 0,
            'recommendation'  => 'review',
        ];
    }

    private function systemPrompt(): string
    {
        return "Tu es un expert en analyse éditoriale, normes académiques internationales (APA, MLA, Chicago, IEEE, ISO), et en détection de plagiat. Tu réponds UNIQUEMENT avec du JSON valide, sans explication ni markdown.";
    }

    /**
     * Génère une citation formatée pour un livre
     */
    public function generateCitation(Book $book, string $style = 'apa'): string
    {
        $author = $book->author->name ?? 'Auteur inconnu';
        $year   = $book->publication_year ?? date('Y');
        $title  = $book->title;
        $pub    = $book->publisher ?? 'LireX';
        $isbn   = $book->isbn ?? '';

        return match($style) {
            'apa'     => "{$author}. ({$year}). *{$title}*. {$pub}." . ($isbn ? " ISBN: {$isbn}" : ''),
            'mla'     => "{$author}. \"{$title}.\" {$pub}, {$year}.",
            'chicago' => "{$author}. {$year}. \"{$title}.\" {$pub}.",
            'ieee'    => "[A] {$author}, \"{$title},\" {$pub}, {$year}.",
            'harvard' => "{$author} ({$year}) *{$title}*. {$pub}.",
            default   => "{$author}. ({$year}). {$title}. {$pub}.",
        };
    }
}
