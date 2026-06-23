@extends('layouts.author')
@section('title','Publier un livre – LireX')
@section('page-title','Publier un nouveau livre')
@section('page-subtitle','Remplissez toutes les informations pour soumettre votre ouvrage à validation')

@section('content')
<form method="POST" action="{{ route('author.books.store') }}" enctype="multipart/form-data" id="bookForm">
@csrf

<div style="display:grid;grid-template-columns:1fr 300px;gap:20px;align-items:start;">

{{-- ════════════════════ COLONNE PRINCIPALE ════════════════════ --}}
<div style="display:flex;flex-direction:column;gap:16px;">

  {{-- ① Informations générales --}}
  <div class="stat-card">
    <h3 style="font-size:.8rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#6b7280;border-bottom:1px solid #e5e7eb;padding-bottom:10px;margin-bottom:16px;">
      <i class="fa-solid fa-circle-info mr-2" style="color:var(--aws-orange);"></i>Informations générales
    </h3>
    <div style="display:flex;flex-direction:column;gap:14px;">

      <div>
        <label class="lx-label">Titre du livre <span style="color:#dc2626;">*</span></label>
        <input type="text" name="title" value="{{ old('title') }}" required maxlength="200"
          class="lx-input @error('title') lx-input-err @enderror" placeholder="Titre complet de votre ouvrage"/>
        @error('title')<p class="lx-err">{{ $message }}</p>@enderror
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
        <div>
          <label class="lx-label">Type de document <span style="color:#dc2626;">*</span></label>
          <select name="document_type" id="docType" required class="lx-select" onchange="toggleAcademic()">
            <option value="">— Choisir un type —</option>

            <optgroup label="📖 Fiction générale">
              <option value="roman"              {{ old('document_type')==='roman'?'sel':''}}>Roman</option>
              <option value="roman_policier"     {{ old('document_type')==='roman_policier'?'sel':''}}>Roman policier / Thriller</option>
              <option value="roman_historique"   {{ old('document_type')==='roman_historique'?'sel':''}}>Roman historique</option>
              <option value="roman_sentimental"  {{ old('document_type')==='roman_sentimental'?'sel':''}}>Roman sentimental / Romance</option>
              <option value="roman_aventure"     {{ old('document_type')==='roman_aventure'?'sel':''}}>Roman d'aventure</option>
              <option value="roman_sf"           {{ old('document_type')==='roman_sf'?'sel':''}}>Science-fiction</option>
              <option value="roman_fantastique"  {{ old('document_type')==='roman_fantastique'?'sel':''}}>Fantastique / Fantasy</option>
              <option value="roman_graphique"    {{ old('document_type')==='roman_graphique'?'sel':''}}>Roman graphique</option>
              <option value="nouvelle"           {{ old('document_type')==='nouvelle'?'sel':''}}>Nouvelle</option>
              <option value="recueil_nouvelles"  {{ old('document_type')==='recueil_nouvelles'?'sel':''}}>Recueil de nouvelles</option>
            </optgroup>

            <optgroup label="🖊️ Poésie & Expression orale">
              <option value="poesie"             {{ old('document_type')==='poesie'?'sel':''}}>Recueil de poésie</option>
              <option value="slam"               {{ old('document_type')==='slam'?'sel':''}}>Slam / Spoken word</option>
              <option value="chanson"            {{ old('document_type')==='chanson'?'sel':''}}>Paroles de chansons / Textes musicaux</option>
              <option value="proverbes"          {{ old('document_type')==='proverbes'?'sel':''}}>Proverbes & Sagesses africaines</option>
            </optgroup>

            <optgroup label="🌙 Tradition orale & Culture africaine">
              <option value="conte"              {{ old('document_type')==='conte'?'sel':''}}>Conte traditionnel</option>
              <option value="fable"              {{ old('document_type')==='fable'?'sel':''}}>Fable</option>
              <option value="legende"            {{ old('document_type')==='legende'?'sel':''}}>Légende / Mythe</option>
              <option value="epopee"             {{ old('document_type')==='epopee'?'sel':''}}>Épopée africaine</option>
              <option value="folklore"           {{ old('document_type')==='folklore'?'sel':''}}>Folklore & Traditions</option>
            </optgroup>

            <optgroup label="🎭 Théâtre & Spectacle">
              <option value="theatre"            {{ old('document_type')==='theatre'?'sel':''}}>Pièce de théâtre</option>
              <option value="comedie"            {{ old('document_type')==='comedie'?'sel':''}}>Comédie</option>
              <option value="comedie_musicale"   {{ old('document_type')==='comedie_musicale'?'sel':''}}>Comédie musicale / Livret</option>
              <option value="scenario"           {{ old('document_type')==='scenario'?'sel':''}}>Scénario / Script</option>
            </optgroup>

            <optgroup label="👤 Biographie & Récit de vie">
              <option value="biographie"         {{ old('document_type')==='biographie'?'sel':''}}>Biographie</option>
              <option value="autobiographie"     {{ old('document_type')==='autobiographie'?'sel':''}}>Autobiographie</option>
              <option value="memoires"           {{ old('document_type')==='memoires'?'sel':''}}>Mémoires</option>
              <option value="journal"            {{ old('document_type')==='journal'?'sel':''}}>Journal intime / Récit personnel</option>
              <option value="temoignage"         {{ old('document_type')==='temoignage'?'sel':''}}>Témoignage / Récit de vie</option>
              <option value="portrait"           {{ old('document_type')==='portrait'?'sel':''}}>Portrait / Grande figure africaine</option>
            </optgroup>

            <optgroup label="📝 Essai & Réflexion">
              <option value="essai"              {{ old('document_type')==='essai'?'sel':''}}>Essai</option>
              <option value="critique"           {{ old('document_type')==='critique'?'sel':''}}>Critique littéraire / artistique</option>
              <option value="pamphlet"           {{ old('document_type')==='pamphlet'?'sel':''}}>Pamphlet / Tribune</option>
              <option value="chronique"          {{ old('document_type')==='chronique'?'sel':''}}>Chronique</option>
              <option value="philosophie"        {{ old('document_type')==='philosophie'?'sel':''}}>Philosophie</option>
              <option value="politique"          {{ old('document_type')==='politique'?'sel':''}}>Politique & Société</option>
            </optgroup>

            <optgroup label="🌍 Histoire, Géographie & Culture">
              <option value="histoire"           {{ old('document_type')==='histoire'?'sel':''}}>Histoire générale</option>
              <option value="histoire_afrique"   {{ old('document_type')==='histoire_afrique'?'sel':''}}>Histoire de l'Afrique</option>
              <option value="geographie"         {{ old('document_type')==='geographie'?'sel':''}}>Géographie</option>
              <option value="culture"            {{ old('document_type')==='culture'?'sel':''}}>Culture & Civilisation</option>
              <option value="linguistique"       {{ old('document_type')==='linguistique'?'sel':''}}>Linguistique / Langues africaines</option>
              <option value="anthropologie"      {{ old('document_type')==='anthropologie'?'sel':''}}>Anthropologie / Ethnologie</option>
              <option value="sociologie"         {{ old('document_type')==='sociologie'?'sel':''}}>Sociologie</option>
              <option value="voyage"             {{ old('document_type')==='voyage'?'sel':''}}>Carnet de voyage / Guide</option>
            </optgroup>

            <optgroup label="💡 Développement personnel & Bien-être">
              <option value="developpement"      {{ old('document_type')==='developpement'?'sel':''}}>Développement personnel</option>
              <option value="leadership"         {{ old('document_type')==='leadership'?'sel':''}}>Leadership / Coaching</option>
              <option value="entrepreneuriat"    {{ old('document_type')==='entrepreneuriat'?'sel':''}}>Entrepreneuriat / Business</option>
              <option value="finance_perso"      {{ old('document_type')==='finance_perso'?'sel':''}}>Finance personnelle</option>
              <option value="sante"              {{ old('document_type')==='sante'?'sel':''}}>Santé & Bien-être</option>
              <option value="nutrition"          {{ old('document_type')==='nutrition'?'sel':''}}>Nutrition / Médecine traditionnelle</option>
              <option value="sport"              {{ old('document_type')==='sport'?'sel':''}}>Sport & Fitness</option>
            </optgroup>

            <optgroup label="✝️ Religion & Spiritualité">
              <option value="religion"           {{ old('document_type')==='religion'?'sel':''}}>Religion & Spiritualité</option>
              <option value="theologie"          {{ old('document_type')==='theologie'?'sel':''}}>Théologie / Études religieuses</option>
              <option value="spiritualite"       {{ old('document_type')==='spiritualite'?'sel':''}}>Méditation / Spiritualité africaine</option>
            </optgroup>

            <optgroup label="🍽️ Cuisine, Art & Loisirs">
              <option value="cuisine"            {{ old('document_type')==='cuisine'?'sel':''}}>Cuisine africaine / Gastronomie</option>
              <option value="art"                {{ old('document_type')==='art'?'sel':''}}>Art & Architecture</option>
              <option value="musique"            {{ old('document_type')==='musique'?'sel':''}}>Musique</option>
              <option value="cinema"             {{ old('document_type')==='cinema'?'sel':''}}>Cinéma & Arts visuels</option>
              <option value="mode"               {{ old('document_type')==='mode'?'sel':''}}>Mode & Création africaine</option>
              <option value="jardinage"          {{ old('document_type')==='jardinage'?'sel':''}}>Agriculture vivrière / Jardinage</option>
            </optgroup>

            <optgroup label="🧒 Jeunesse & Enfants">
              <option value="album"              {{ old('document_type')==='album'?'sel':''}}>Album illustré (0–6 ans)</option>
              <option value="jeunesse_7_12"      {{ old('document_type')==='jeunesse_7_12'?'sel':''}}>Livre jeunesse (7–12 ans)</option>
              <option value="ado"                {{ old('document_type')==='ado'?'sel':''}}>Roman adolescent (13–17 ans)</option>
              <option value="eveil"              {{ old('document_type')==='eveil'?'sel':''}}>Livre d'éveil / éducatif</option>
              <option value="bd"                 {{ old('document_type')==='bd'?'sel':''}}>Bande dessinée (BD)</option>
              <option value="manga"              {{ old('document_type')==='manga'?'sel':''}}>Manga / Comic africain</option>
            </optgroup>

            <optgroup label="🎓 Académique & Recherche">
              <option value="these"              {{ old('document_type')==='these'?'sel':''}}>Thèse de doctorat</option>
              <option value="memoire_master"     {{ old('document_type')==='memoire_master'?'sel':''}}>Mémoire de master</option>
              <option value="memoire_licence"    {{ old('document_type')==='memoire_licence'?'sel':''}}>Mémoire de licence</option>
              <option value="rapport_stage"      {{ old('document_type')==='rapport_stage'?'sel':''}}>Rapport de stage</option>
              <option value="rapport"            {{ old('document_type')==='rapport'?'sel':''}}>Rapport de recherche</option>
              <option value="article"            {{ old('document_type')==='article'?'sel':''}}>Article / Publication scientifique</option>
            </optgroup>

            <optgroup label="📚 Manuels & Références">
              <option value="manuel"             {{ old('document_type')==='manuel'?'sel':''}}>Manuel universitaire / scolaire</option>
              <option value="cours"              {{ old('document_type')==='cours'?'sel':''}}>Cours / Polycopié</option>
              <option value="guide"              {{ old('document_type')==='guide'?'sel':''}}>Guide pratique</option>
              <option value="dictionnaire"       {{ old('document_type')==='dictionnaire'?'sel':''}}>Dictionnaire / Lexique</option>
              <option value="encyclopedie"       {{ old('document_type')==='encyclopedie'?'sel':''}}>Encyclopédie / Référence</option>
              <option value="droit"              {{ old('document_type')==='droit'?'sel':''}}>Droit / Jurisprudence</option>
              <option value="medecine"           {{ old('document_type')==='medecine'?'sel':''}}>Médecine & Sciences de la santé</option>
              <option value="informatique"       {{ old('document_type')==='informatique'?'sel':''}}>Informatique & Numérique</option>
              <option value="sciences"           {{ old('document_type')==='sciences'?'sel':''}}>Sciences (Maths, Physique, Chimie…)</option>
              <option value="economie"           {{ old('document_type')==='economie'?'sel':''}}>Économie / Gestion / Commerce</option>
              <option value="agronomie"          {{ old('document_type')==='agronomie'?'sel':''}}>Agronomie / Sciences de la terre</option>
            </optgroup>

            <option value="autre">✏️ Autre type de document</option>
          </select>
        </div>
        <div>
          <label class="lx-label">Langue <span style="color:#dc2626;">*</span></label>
          <select name="language" required class="lx-select">
            @foreach(['fr'=>'Français','en'=>'Anglais','ln'=>'Lingala','kg'=>'Kikongo','sw'=>'Swahili','es'=>'Espagnol','pt'=>'Portugais','ar'=>'Arabe'] as $v=>$l)
            <option value="{{ $v }}" {{ old('language',$v)===$v?'selected':'' }}>{{ $l }}</option>
            @endforeach
          </select>
        </div>
      </div>

      <div>
        <label class="lx-label">
          Catégorie <span style="color:#dc2626;">*</span>
        </label>
        <select name="category_id" required class="lx-select">
          <option value="">Choisir une catégorie</option>
          @foreach($categories as $cat)
          <option value="{{ $cat->id }}" @selected(old('category_id')==$cat->id)>{{ $cat->icon }} {{ $cat->name }}</option>
          @endforeach
        </select>
      </div>

      <div>
        <label class="lx-label">Description / Résumé <span style="color:#dc2626;">*</span></label>
        <textarea name="description" rows="6" required minlength="100" maxlength="3000"
          class="lx-input @error('description') lx-input-err @enderror"
          placeholder="Présentez votre ouvrage : thème, intrigue, apport… (min. 100 caractères)"
          oninput="document.getElementById('desc_count').textContent=this.value.length">{{ old('description') }}</textarea>
        <p style="font-size:.72rem;color:#9ca3af;margin-top:3px;"><span id="desc_count">0</span>/3000</p>
        @error('description')<p class="lx-err">{{ $message }}</p>@enderror
      </div>

      <div>
        <label class="lx-label">Mots-clés <span style="font-weight:400;color:#9ca3af;">(séparés par des virgules)</span></label>
        <input type="text" name="tags" value="{{ old('tags') }}" class="lx-input"
          placeholder="ex: afrique, roman historique, Congo, indépendance…"/>
        <p style="font-size:.72rem;color:#9ca3af;margin-top:3px;">Améliorent la découvrabilité de votre livre sur la plateforme.</p>
      </div>
    </div>
  </div>

  {{-- ② Détails de publication --}}
  <div class="stat-card">
    <h3 style="font-size:.8rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#6b7280;border-bottom:1px solid #e5e7eb;padding-bottom:10px;margin-bottom:16px;">
      <i class="fa-solid fa-book mr-2" style="color:var(--aws-orange);"></i>Détails de publication
    </h3>
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;">
      <div>
        <label class="lx-label">Format numérique <span style="color:#dc2626;">*</span></label>
        <select name="format" required class="lx-select">
          <option value="pdf"  {{ old('format')==='pdf'?'selected':'' }}>PDF</option>
          <option value="epub" {{ old('format')==='epub'?'selected':'' }}>ePub</option>
          <option value="both" {{ old('format','both')==='both'?'selected':'' }}>PDF + ePub</option>
        </select>
      </div>
      <div>
        <label class="lx-label">Nombre de pages</label>
        <input type="number" name="pages" value="{{ old('pages') }}" min="1" class="lx-input" placeholder="ex: 250"/>
      </div>
      <div>
        <label class="lx-label">Année de publication</label>
        <input type="number" name="publication_year" value="{{ old('publication_year',date('Y')) }}"
          min="1800" max="{{ date('Y') }}" class="lx-input"/>
      </div>
      <div>
        <label class="lx-label">ISBN</label>
        <input type="text" name="isbn" value="{{ old('isbn') }}" maxlength="20" class="lx-input" placeholder="978-…"/>
        <p style="font-size:.7rem;color:#9ca3af;margin-top:3px;">Facultatif · identifiant international unique</p>
      </div>
      <div class="col-span-2" style="grid-column:span 2;">
        <label class="lx-label">Éditeur / Maison d'édition</label>
        <input type="text" name="publisher" value="{{ old('publisher') }}" class="lx-input"
          placeholder="Auto-édition, Éditions XYZ, CEDA, Présence Africaine…"/>
      </div>
      <div style="grid-column:span 3;">
        <label class="lx-label">Droits & Licence</label>
        <select name="rights" class="lx-select">
          <option value="all_rights" {{ old('rights','all_rights')==='all_rights'?'selected':'' }}>© Tous droits réservés</option>
          <option value="cc_by"      {{ old('rights')==='cc_by'?'selected':'' }}>Creative Commons – Attribution (CC BY)</option>
          <option value="cc_by_nc"   {{ old('rights')==='cc_by_nc'?'selected':'' }}>Creative Commons – Attribution Non-Commerciale (CC BY-NC)</option>
          <option value="cc_by_sa"   {{ old('rights')==='cc_by_sa'?'selected':'' }}>Creative Commons – Attribution Partage à l'identique (CC BY-SA)</option>
          <option value="public"     {{ old('rights')==='public'?'selected':'' }}>Domaine public</option>
        </select>
      </div>
    </div>
  </div>

  {{-- ③ Section académique (conditionnelle) --}}
  <div class="stat-card" id="academicSection" style="display:none;">
    <h3 style="font-size:.8rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#6b7280;border-bottom:1px solid #e5e7eb;padding-bottom:10px;margin-bottom:16px;">
      <i class="fa-solid fa-graduation-cap mr-2" style="color:var(--aws-orange);"></i>Informations académiques
    </h3>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
      <div>
        <label class="lx-label">Université / Institution</label>
        <input type="text" name="university" value="{{ old('university') }}" class="lx-input"
          placeholder="Université Marien Ngouabi, UNIKIN…"/>
      </div>
      <div>
        <label class="lx-label">Directeur de recherche / Encadreur</label>
        <input type="text" name="supervisor" value="{{ old('supervisor') }}" class="lx-input"
          placeholder="Prof. Dupont, Dr. Mbeki…"/>
      </div>
      <div style="grid-column:span 2;">
        <label class="lx-label">Domaine / Filière</label>
        <input type="text" name="field_of_study" value="{{ old('field_of_study') }}" class="lx-input"
          placeholder="Lettres modernes, Sciences économiques, Droit…"/>
      </div>
    </div>
  </div>

  {{-- ④ Fichiers --}}
  <div class="stat-card">
    <h3 style="font-size:.8rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#6b7280;border-bottom:1px solid #e5e7eb;padding-bottom:10px;margin-bottom:16px;">
      <i class="fa-solid fa-file-arrow-up mr-2" style="color:var(--aws-orange);"></i>Fichiers
    </h3>
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px;">

      {{-- Couverture --}}
      <div>
        <label class="lx-label">Couverture <span style="color:#dc2626;">*</span></label>
        <div id="cover_drop" class="lx-dropzone" onclick="document.getElementById('cover_input').click()">
          <i class="fa-solid fa-image" style="font-size:1.6rem;color:#d1d5db;display:block;margin-bottom:6px;"></i>
          <p style="font-size:.78rem;font-weight:600;color:#6b7280;">JPG, PNG, WebP</p>
          <p style="font-size:.7rem;color:#9ca3af;margin-top:2px;">Max 10 Mo · 400×600 px</p>
        </div>
        <input type="file" id="cover_input" name="cover_image" accept="image/jpeg,image/png,image/webp" required class="hidden" onchange="previewCover(this)"/>
        <img id="cover_preview" style="display:none;margin:8px auto 0;width:60px;height:80px;object-fit:cover;border-radius:4px;box-shadow:0 1px 4px rgba(0,0,0,.15);" alt=""/>
        @error('cover_image')<p class="lx-err">{{ $message }}</p>@enderror
      </div>

      {{-- Fichier principal --}}
      <div>
        <label class="lx-label">Fichier du livre <span style="color:#dc2626;">*</span></label>
        <div id="book_drop" class="lx-dropzone" onclick="document.getElementById('book_input').click()">
          <i class="fa-solid fa-file-lines" style="font-size:1.6rem;color:#d1d5db;display:block;margin-bottom:6px;"></i>
          <p style="font-size:.78rem;font-weight:600;color:#6b7280;">PDF ou ePub</p>
          <p style="font-size:.7rem;color:#9ca3af;margin-top:2px;">Max 500 Mo</p>
        </div>
        <input type="file" id="book_input" name="book_file" accept=".pdf,.epub" required class="hidden" onchange="showBookFile(this,'book_drop','book_info')"/>
        <div id="book_info" style="display:none;margin-top:6px;">
          <div style="display:flex;justify-content:space-between;font-size:.72rem;color:#374151;margin-bottom:3px;">
            <span id="book_fname" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:#15803d;font-weight:600;"></span>
            <span id="book_fsize" style="color:#9ca3af;flex-shrink:0;margin-left:6px;"></span>
          </div>
          <div style="background:#e5e7eb;border-radius:4px;height:4px;">
            <div id="book_bar" style="height:4px;border-radius:4px;width:0%;background:#3b82f6;transition:width .3s;"></div>
          </div>
          <p id="book_pct" style="font-size:.7rem;color:#9ca3af;margin-top:2px;"></p>
        </div>
        @error('book_file')<p class="lx-err">{{ $message }}</p>@enderror
      </div>

      {{-- Aperçu/Extrait --}}
      <div>
        <label class="lx-label">Extrait / Aperçu <span style="font-weight:400;color:#9ca3af;">(optionnel)</span></label>
        <div id="preview_drop" class="lx-dropzone" onclick="document.getElementById('preview_input').click()">
          <i class="fa-solid fa-eye" style="font-size:1.6rem;color:#d1d5db;display:block;margin-bottom:6px;"></i>
          <p style="font-size:.78rem;font-weight:600;color:#6b7280;">PDF ou ePub</p>
          <p style="font-size:.7rem;color:#9ca3af;margin-top:2px;">Max 20 Mo · 1–3 chapitres</p>
        </div>
        <input type="file" id="preview_input" name="preview_file" accept=".pdf,.epub" class="hidden" onchange="showBookFile(this,'preview_drop','preview_info')"/>
        <div id="preview_info" style="display:none;margin-top:6px;">
          <span id="preview_fname" style="font-size:.72rem;color:#15803d;font-weight:600;"></span>
        </div>
        <p style="font-size:.7rem;color:#9ca3af;margin-top:4px;">Permet aux lecteurs de lire un extrait avant d'acheter.</p>
        @error('preview_file')<p class="lx-err">{{ $message }}</p>@enderror
      </div>
    </div>
  </div>

</div>{{-- fin colonne principale --}}

{{-- ════════════════════ SIDEBAR ════════════════════ --}}
<div style="display:flex;flex-direction:column;gap:14px;position:sticky;top:68px;">

  {{-- Tarification --}}
  <div class="stat-card">
    <h3 style="font-size:.8rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#6b7280;border-bottom:1px solid #e5e7eb;padding-bottom:10px;margin-bottom:14px;">
      <i class="fa-solid fa-tag mr-2" style="color:var(--aws-orange);"></i>Tarification numérique
    </h3>

    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;margin-bottom:12px;">
      <input type="checkbox" name="is_free" id="isFree" {{ old('is_free')?'checked':'' }}
        style="width:14px;height:14px;accent-color:var(--aws-orange);" onchange="togglePrice()"/>
      <span style="font-size:.82rem;font-weight:600;color:#374151;">Livre gratuit</span>
    </label>

    <div id="priceField">
      <div style="display:grid;grid-template-columns:1fr auto;gap:8px;align-items:end;margin-bottom:6px;">
        <div>
          <label class="lx-label">Prix</label>
          <input type="number" name="price" value="{{ old('price',0) }}" min="0" step="100"
            class="lx-input" style="padding-right:52px;position:relative;"/>
        </div>
        <div>
          <label class="lx-label">Devise</label>
          <select name="currency" class="lx-select" style="width:76px;">
            <option value="XAF" {{ old('currency','XAF')==='XAF'?'selected':'' }}>XAF</option>
            <option value="USD" {{ old('currency')==='USD'?'selected':'' }}>USD</option>
            <option value="EUR" {{ old('currency')==='EUR'?'selected':'' }}>EUR</option>
          </select>
        </div>
      </div>
      <p style="font-size:.72rem;color:#6b7280;">Votre part : <strong style="color:#15803d;">selon votre forfait</strong></p>
    </div>
  </div>

  {{-- Location --}}
  <div class="stat-card">
    <h3 style="font-size:.8rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#6b7280;border-bottom:1px solid #e5e7eb;padding-bottom:10px;margin-bottom:14px;">
      <i class="fa-solid fa-clock mr-2" style="color:var(--aws-orange);"></i>Location de lecture
    </h3>
    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;margin-bottom:12px;">
      <input type="checkbox" name="allow_rental" id="isRental" {{ old('allow_rental')?'checked':'' }}
        style="width:14px;height:14px;accent-color:var(--aws-orange);" onchange="toggleRental()"/>
      <span style="font-size:.82rem;font-weight:600;color:#374151;">Autoriser la location</span>
    </label>
    <div id="rentalField" style="display:none;">
      <label class="lx-label">Prix / heure (XAF)</label>
      <input type="number" name="rental_price_hour" value="{{ old('rental_price_hour',100) }}" min="0" step="50" class="lx-input"/>
      <p style="font-size:.72rem;color:#9ca3af;margin-top:3px;">Le lecteur paie pour un accès temporaire.</p>
    </div>
  </div>

  {{-- Impression physique --}}
  <div class="stat-card">
    <h3 style="font-size:.8rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#6b7280;border-bottom:1px solid #e5e7eb;padding-bottom:10px;margin-bottom:14px;">
      <i class="fa-solid fa-print mr-2" style="color:var(--aws-orange);"></i>Impression à la demande
    </h3>
    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;margin-bottom:12px;">
      <input type="checkbox" name="print_on_demand" id="isPrint" {{ old('print_on_demand')?'checked':'' }}
        style="width:14px;height:14px;accent-color:var(--aws-orange);" onchange="togglePrint()"/>
      <span style="font-size:.82rem;font-weight:600;color:#374151;">Activer l'impression physique</span>
    </label>
    <div id="printField" style="display:none;">
      <label class="lx-label">Prix impression (XAF)</label>
      <input type="number" name="print_price" value="{{ old('print_price') }}" min="0" step="500" class="lx-input"/>
    </div>
  </div>

  {{-- Infos validation --}}
  <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:6px;padding:14px;">
    <p style="font-size:.78rem;font-weight:700;color:#92400e;margin-bottom:6px;">
      <i class="fa-solid fa-circle-info mr-1"></i> Délai de validation
    </p>
    <p style="font-size:.73rem;color:#78350f;line-height:1.5;">
      Votre livre sera examiné sous <strong>48 h ouvrées</strong>. Vous recevrez une notification par email à chaque étape.
    </p>
  </div>

  <button type="submit" class="btn-aws" style="width:100%;justify-content:center;padding:10px;font-size:.9rem;" id="submitBtn">
    <i class="fa-solid fa-paper-plane mr-2"></i> Soumettre pour validation
  </button>
  <a href="{{ route('author.books.index') }}"
     style="display:block;text-align:center;font-size:.8rem;color:#6b7280;padding:6px;text-decoration:none;">
     Annuler
  </a>

</div>{{-- fin sidebar --}}
</div>{{-- fin grid --}}
</form>

<style>
.lx-label  { display:block;font-size:.78rem;font-weight:600;color:#374151;margin-bottom:5px; }
.lx-input  { width:100%;border:1px solid #d1d5db;border-radius:4px;padding:7px 10px;font-size:.82rem;outline:none;box-sizing:border-box; }
.lx-input:focus  { border-color:var(--aws-orange);box-shadow:0 0 0 2px rgba(255,153,0,.15); }
.lx-input-err    { border-color:#dc2626; }
.lx-select { width:100%;border:1px solid #d1d5db;border-radius:4px;padding:7px 10px;font-size:.82rem;outline:none;background:#fff;box-sizing:border-box; }
.lx-select:focus { border-color:var(--aws-orange); }
.lx-err    { font-size:.72rem;color:#dc2626;margin-top:3px; }
.lx-dropzone {
  border:2px dashed #d1d5db;border-radius:6px;padding:20px 12px;text-align:center;
  cursor:pointer;transition:border-color .15s,background .15s;
}
.lx-dropzone:hover { border-color:var(--aws-orange);background:#fffbeb; }
textarea.lx-input { resize:vertical; }
</style>

@push('scripts')
<script>
const ACADEMIC_TYPES = ['these','memoire_master','memoire_licence','rapport_stage','rapport','article','manuel','cours'];
const MAX_BOOK_MB = 500, MAX_COVER_MB = 10, MAX_PREVIEW_MB = 20;

function togglePrice()  { document.getElementById('priceField').style.display   = document.getElementById('isFree').checked    ? 'none' : 'block'; }
function togglePrint()  { document.getElementById('printField').style.display   = document.getElementById('isPrint').checked   ? 'block' : 'none'; }
function toggleRental() { document.getElementById('rentalField').style.display  = document.getElementById('isRental').checked  ? 'block' : 'none'; }

function toggleAcademic() {
  const t = document.getElementById('docType').value;
  document.getElementById('academicSection').style.display = ACADEMIC_TYPES.includes(t) ? 'block' : 'none';
}

function previewCover(input) {
  const f = input.files?.[0]; if (!f) return;
  if (f.size/1048576 > MAX_COVER_MB) { alert(`Max ${MAX_COVER_MB} Mo pour la couverture.`); input.value=''; return; }
  const p = document.getElementById('cover_preview');
  p.src = URL.createObjectURL(f); p.style.display = 'block';
  document.getElementById('cover_drop').style.borderColor = '#22c55e';
}

function fmt(b) {
  return b >= 1073741824 ? (b/1073741824).toFixed(2)+' Go'
       : b >= 1048576    ? (b/1048576).toFixed(1)+' Mo'
       :                   (b/1024).toFixed(0)+' Ko';
}

function showBookFile(input, dropId, infoId) {
  const f = input.files?.[0]; if (!f) return;
  const maxMb = dropId === 'preview_drop' ? MAX_PREVIEW_MB : MAX_BOOK_MB;
  const mb = f.size / 1048576;
  if (mb > maxMb) { alert(`Fichier trop grand. Max ${maxMb} Mo.`); input.value=''; return; }
  document.getElementById(dropId).style.borderColor = '#22c55e';
  document.getElementById(infoId).style.display = 'block';
  if (dropId === 'book_drop') {
    document.getElementById('book_fname').textContent = '✓ '+f.name;
    document.getElementById('book_fsize').textContent = fmt(f.size);
    const pct = Math.min((mb/MAX_BOOK_MB)*100,100).toFixed(1);
    document.getElementById('book_bar').style.width = pct+'%';
    document.getElementById('book_bar').style.background = pct > 80 ? '#f59e0b' : '#3b82f6';
    document.getElementById('book_pct').textContent = pct+'% de la limite (500 Mo)';
  } else {
    document.getElementById('preview_fname').textContent = '✓ '+f.name+' — '+fmt(f.size);
  }
}

// spinner à la soumission
document.getElementById('bookForm').addEventListener('submit', function() {
  const btn = document.getElementById('submitBtn');
  btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i> Envoi en cours…';
  btn.disabled = true;
});

// si on revient avec old() et doc_type académique
document.addEventListener('DOMContentLoaded', () => {
  toggleAcademic();
  if ('{{ old('is_free') }}') togglePrice();
  if ('{{ old('print_on_demand') }}') togglePrint();
  if ('{{ old('allow_rental') }}') toggleRental();
});
</script>
@endpush
@endsection
