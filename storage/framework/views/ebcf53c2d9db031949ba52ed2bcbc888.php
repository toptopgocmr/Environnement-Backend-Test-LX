<?php $__env->startSection('title','Publier un livre – LireX'); ?>
<?php $__env->startSection('page-title','Publier un nouveau livre'); ?>
<?php $__env->startSection('page-subtitle','Remplissez toutes les informations pour soumettre votre ouvrage à validation'); ?>

<?php $__env->startSection('content'); ?>
<form method="POST" action="<?php echo e(route('author.books.store')); ?>" enctype="multipart/form-data" id="bookForm">
<?php echo csrf_field(); ?>

<div style="display:grid;grid-template-columns:1fr 300px;gap:20px;align-items:start;">


<div style="display:flex;flex-direction:column;gap:16px;">

  
  <div class="stat-card">
    <h3 style="font-size:.8rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#6b7280;border-bottom:1px solid #e5e7eb;padding-bottom:10px;margin-bottom:16px;">
      <i class="fa-solid fa-circle-info mr-2" style="color:var(--aws-orange);"></i>Informations générales
    </h3>
    <div style="display:flex;flex-direction:column;gap:14px;">

      <div>
        <label class="lx-label">Titre du livre <span style="color:#dc2626;">*</span></label>
        <input type="text" name="title" value="<?php echo e(old('title')); ?>" required maxlength="200"
          class="lx-input <?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> lx-input-err <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" placeholder="Titre complet de votre ouvrage"/>
        <?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="lx-err"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
        <div>
          <label class="lx-label">Type de document <span style="color:#dc2626;">*</span></label>
          <select name="document_type" id="docType" required class="lx-select" onchange="toggleAcademic()">
            <option value="">— Choisir un type —</option>

            <optgroup label="📖 Fiction générale">
              <option value="roman"              <?php echo e(old('document_type')==='roman'?'sel':''); ?>>Roman</option>
              <option value="roman_policier"     <?php echo e(old('document_type')==='roman_policier'?'sel':''); ?>>Roman policier / Thriller</option>
              <option value="roman_historique"   <?php echo e(old('document_type')==='roman_historique'?'sel':''); ?>>Roman historique</option>
              <option value="roman_sentimental"  <?php echo e(old('document_type')==='roman_sentimental'?'sel':''); ?>>Roman sentimental / Romance</option>
              <option value="roman_aventure"     <?php echo e(old('document_type')==='roman_aventure'?'sel':''); ?>>Roman d'aventure</option>
              <option value="roman_sf"           <?php echo e(old('document_type')==='roman_sf'?'sel':''); ?>>Science-fiction</option>
              <option value="roman_fantastique"  <?php echo e(old('document_type')==='roman_fantastique'?'sel':''); ?>>Fantastique / Fantasy</option>
              <option value="roman_graphique"    <?php echo e(old('document_type')==='roman_graphique'?'sel':''); ?>>Roman graphique</option>
              <option value="nouvelle"           <?php echo e(old('document_type')==='nouvelle'?'sel':''); ?>>Nouvelle</option>
              <option value="recueil_nouvelles"  <?php echo e(old('document_type')==='recueil_nouvelles'?'sel':''); ?>>Recueil de nouvelles</option>
            </optgroup>

            <optgroup label="🖊️ Poésie & Expression orale">
              <option value="poesie"             <?php echo e(old('document_type')==='poesie'?'sel':''); ?>>Recueil de poésie</option>
              <option value="slam"               <?php echo e(old('document_type')==='slam'?'sel':''); ?>>Slam / Spoken word</option>
              <option value="chanson"            <?php echo e(old('document_type')==='chanson'?'sel':''); ?>>Paroles de chansons / Textes musicaux</option>
              <option value="proverbes"          <?php echo e(old('document_type')==='proverbes'?'sel':''); ?>>Proverbes & Sagesses africaines</option>
            </optgroup>

            <optgroup label="🌙 Tradition orale & Culture africaine">
              <option value="conte"              <?php echo e(old('document_type')==='conte'?'sel':''); ?>>Conte traditionnel</option>
              <option value="fable"              <?php echo e(old('document_type')==='fable'?'sel':''); ?>>Fable</option>
              <option value="legende"            <?php echo e(old('document_type')==='legende'?'sel':''); ?>>Légende / Mythe</option>
              <option value="epopee"             <?php echo e(old('document_type')==='epopee'?'sel':''); ?>>Épopée africaine</option>
              <option value="folklore"           <?php echo e(old('document_type')==='folklore'?'sel':''); ?>>Folklore & Traditions</option>
            </optgroup>

            <optgroup label="🎭 Théâtre & Spectacle">
              <option value="theatre"            <?php echo e(old('document_type')==='theatre'?'sel':''); ?>>Pièce de théâtre</option>
              <option value="comedie"            <?php echo e(old('document_type')==='comedie'?'sel':''); ?>>Comédie</option>
              <option value="comedie_musicale"   <?php echo e(old('document_type')==='comedie_musicale'?'sel':''); ?>>Comédie musicale / Livret</option>
              <option value="scenario"           <?php echo e(old('document_type')==='scenario'?'sel':''); ?>>Scénario / Script</option>
            </optgroup>

            <optgroup label="👤 Biographie & Récit de vie">
              <option value="biographie"         <?php echo e(old('document_type')==='biographie'?'sel':''); ?>>Biographie</option>
              <option value="autobiographie"     <?php echo e(old('document_type')==='autobiographie'?'sel':''); ?>>Autobiographie</option>
              <option value="memoires"           <?php echo e(old('document_type')==='memoires'?'sel':''); ?>>Mémoires</option>
              <option value="journal"            <?php echo e(old('document_type')==='journal'?'sel':''); ?>>Journal intime / Récit personnel</option>
              <option value="temoignage"         <?php echo e(old('document_type')==='temoignage'?'sel':''); ?>>Témoignage / Récit de vie</option>
              <option value="portrait"           <?php echo e(old('document_type')==='portrait'?'sel':''); ?>>Portrait / Grande figure africaine</option>
            </optgroup>

            <optgroup label="📝 Essai & Réflexion">
              <option value="essai"              <?php echo e(old('document_type')==='essai'?'sel':''); ?>>Essai</option>
              <option value="critique"           <?php echo e(old('document_type')==='critique'?'sel':''); ?>>Critique littéraire / artistique</option>
              <option value="pamphlet"           <?php echo e(old('document_type')==='pamphlet'?'sel':''); ?>>Pamphlet / Tribune</option>
              <option value="chronique"          <?php echo e(old('document_type')==='chronique'?'sel':''); ?>>Chronique</option>
              <option value="philosophie"        <?php echo e(old('document_type')==='philosophie'?'sel':''); ?>>Philosophie</option>
              <option value="politique"          <?php echo e(old('document_type')==='politique'?'sel':''); ?>>Politique & Société</option>
            </optgroup>

            <optgroup label="🌍 Histoire, Géographie & Culture">
              <option value="histoire"           <?php echo e(old('document_type')==='histoire'?'sel':''); ?>>Histoire générale</option>
              <option value="histoire_afrique"   <?php echo e(old('document_type')==='histoire_afrique'?'sel':''); ?>>Histoire de l'Afrique</option>
              <option value="geographie"         <?php echo e(old('document_type')==='geographie'?'sel':''); ?>>Géographie</option>
              <option value="culture"            <?php echo e(old('document_type')==='culture'?'sel':''); ?>>Culture & Civilisation</option>
              <option value="linguistique"       <?php echo e(old('document_type')==='linguistique'?'sel':''); ?>>Linguistique / Langues africaines</option>
              <option value="anthropologie"      <?php echo e(old('document_type')==='anthropologie'?'sel':''); ?>>Anthropologie / Ethnologie</option>
              <option value="sociologie"         <?php echo e(old('document_type')==='sociologie'?'sel':''); ?>>Sociologie</option>
              <option value="voyage"             <?php echo e(old('document_type')==='voyage'?'sel':''); ?>>Carnet de voyage / Guide</option>
            </optgroup>

            <optgroup label="💡 Développement personnel & Bien-être">
              <option value="developpement"      <?php echo e(old('document_type')==='developpement'?'sel':''); ?>>Développement personnel</option>
              <option value="leadership"         <?php echo e(old('document_type')==='leadership'?'sel':''); ?>>Leadership / Coaching</option>
              <option value="entrepreneuriat"    <?php echo e(old('document_type')==='entrepreneuriat'?'sel':''); ?>>Entrepreneuriat / Business</option>
              <option value="finance_perso"      <?php echo e(old('document_type')==='finance_perso'?'sel':''); ?>>Finance personnelle</option>
              <option value="sante"              <?php echo e(old('document_type')==='sante'?'sel':''); ?>>Santé & Bien-être</option>
              <option value="nutrition"          <?php echo e(old('document_type')==='nutrition'?'sel':''); ?>>Nutrition / Médecine traditionnelle</option>
              <option value="sport"              <?php echo e(old('document_type')==='sport'?'sel':''); ?>>Sport & Fitness</option>
            </optgroup>

            <optgroup label="✝️ Religion & Spiritualité">
              <option value="religion"           <?php echo e(old('document_type')==='religion'?'sel':''); ?>>Religion & Spiritualité</option>
              <option value="theologie"          <?php echo e(old('document_type')==='theologie'?'sel':''); ?>>Théologie / Études religieuses</option>
              <option value="spiritualite"       <?php echo e(old('document_type')==='spiritualite'?'sel':''); ?>>Méditation / Spiritualité africaine</option>
            </optgroup>

            <optgroup label="🍽️ Cuisine, Art & Loisirs">
              <option value="cuisine"            <?php echo e(old('document_type')==='cuisine'?'sel':''); ?>>Cuisine africaine / Gastronomie</option>
              <option value="art"                <?php echo e(old('document_type')==='art'?'sel':''); ?>>Art & Architecture</option>
              <option value="musique"            <?php echo e(old('document_type')==='musique'?'sel':''); ?>>Musique</option>
              <option value="cinema"             <?php echo e(old('document_type')==='cinema'?'sel':''); ?>>Cinéma & Arts visuels</option>
              <option value="mode"               <?php echo e(old('document_type')==='mode'?'sel':''); ?>>Mode & Création africaine</option>
              <option value="jardinage"          <?php echo e(old('document_type')==='jardinage'?'sel':''); ?>>Agriculture vivrière / Jardinage</option>
            </optgroup>

            <optgroup label="🧒 Jeunesse & Enfants">
              <option value="album"              <?php echo e(old('document_type')==='album'?'sel':''); ?>>Album illustré (0–6 ans)</option>
              <option value="jeunesse_7_12"      <?php echo e(old('document_type')==='jeunesse_7_12'?'sel':''); ?>>Livre jeunesse (7–12 ans)</option>
              <option value="ado"                <?php echo e(old('document_type')==='ado'?'sel':''); ?>>Roman adolescent (13–17 ans)</option>
              <option value="eveil"              <?php echo e(old('document_type')==='eveil'?'sel':''); ?>>Livre d'éveil / éducatif</option>
              <option value="bd"                 <?php echo e(old('document_type')==='bd'?'sel':''); ?>>Bande dessinée (BD)</option>
              <option value="manga"              <?php echo e(old('document_type')==='manga'?'sel':''); ?>>Manga / Comic africain</option>
            </optgroup>

            <optgroup label="🎓 Académique & Recherche">
              <option value="these"              <?php echo e(old('document_type')==='these'?'sel':''); ?>>Thèse de doctorat</option>
              <option value="memoire_master"     <?php echo e(old('document_type')==='memoire_master'?'sel':''); ?>>Mémoire de master</option>
              <option value="memoire_licence"    <?php echo e(old('document_type')==='memoire_licence'?'sel':''); ?>>Mémoire de licence</option>
              <option value="rapport_stage"      <?php echo e(old('document_type')==='rapport_stage'?'sel':''); ?>>Rapport de stage</option>
              <option value="rapport"            <?php echo e(old('document_type')==='rapport'?'sel':''); ?>>Rapport de recherche</option>
              <option value="article"            <?php echo e(old('document_type')==='article'?'sel':''); ?>>Article / Publication scientifique</option>
            </optgroup>

            <optgroup label="📚 Manuels & Références">
              <option value="manuel"             <?php echo e(old('document_type')==='manuel'?'sel':''); ?>>Manuel universitaire / scolaire</option>
              <option value="cours"              <?php echo e(old('document_type')==='cours'?'sel':''); ?>>Cours / Polycopié</option>
              <option value="guide"              <?php echo e(old('document_type')==='guide'?'sel':''); ?>>Guide pratique</option>
              <option value="dictionnaire"       <?php echo e(old('document_type')==='dictionnaire'?'sel':''); ?>>Dictionnaire / Lexique</option>
              <option value="encyclopedie"       <?php echo e(old('document_type')==='encyclopedie'?'sel':''); ?>>Encyclopédie / Référence</option>
              <option value="droit"              <?php echo e(old('document_type')==='droit'?'sel':''); ?>>Droit / Jurisprudence</option>
              <option value="medecine"           <?php echo e(old('document_type')==='medecine'?'sel':''); ?>>Médecine & Sciences de la santé</option>
              <option value="informatique"       <?php echo e(old('document_type')==='informatique'?'sel':''); ?>>Informatique & Numérique</option>
              <option value="sciences"           <?php echo e(old('document_type')==='sciences'?'sel':''); ?>>Sciences (Maths, Physique, Chimie…)</option>
              <option value="economie"           <?php echo e(old('document_type')==='economie'?'sel':''); ?>>Économie / Gestion / Commerce</option>
              <option value="agronomie"          <?php echo e(old('document_type')==='agronomie'?'sel':''); ?>>Agronomie / Sciences de la terre</option>
            </optgroup>

            <option value="autre">✏️ Autre type de document</option>
          </select>
        </div>
        <div>
          <label class="lx-label">Langue <span style="color:#dc2626;">*</span></label>
          <select name="language" required class="lx-select">
            <?php $__currentLoopData = ['fr'=>'Français','en'=>'Anglais','ln'=>'Lingala','kg'=>'Kikongo','sw'=>'Swahili','es'=>'Espagnol','pt'=>'Portugais','ar'=>'Arabe']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v=>$l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($v); ?>" <?php echo e(old('language',$v)===$v?'selected':''); ?>><?php echo e($l); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
        </div>
      </div>

      <div>
        <label class="lx-label">
          Catégorie <span style="color:#dc2626;">*</span>
        </label>
        <select name="category_id" required class="lx-select">
          <option value="">Choisir une catégorie</option>
          <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <option value="<?php echo e($cat->id); ?>" <?php if(old('category_id')==$cat->id): echo 'selected'; endif; ?>><?php echo e($cat->icon); ?> <?php echo e($cat->name); ?></option>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
      </div>

      <div>
        <label class="lx-label">Description / Résumé <span style="color:#dc2626;">*</span></label>
        <textarea name="description" rows="6" required minlength="100" maxlength="3000"
          class="lx-input <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> lx-input-err <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
          placeholder="Présentez votre ouvrage : thème, intrigue, apport… (min. 100 caractères)"
          oninput="document.getElementById('desc_count').textContent=this.value.length"><?php echo e(old('description')); ?></textarea>
        <p style="font-size:.72rem;color:#9ca3af;margin-top:3px;"><span id="desc_count">0</span>/3000</p>
        <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="lx-err"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
      </div>

      <div>
        <label class="lx-label">Mots-clés <span style="font-weight:400;color:#9ca3af;">(séparés par des virgules)</span></label>
        <input type="text" name="tags" value="<?php echo e(old('tags')); ?>" class="lx-input"
          placeholder="ex: afrique, roman historique, Congo, indépendance…"/>
        <p style="font-size:.72rem;color:#9ca3af;margin-top:3px;">Améliorent la découvrabilité de votre livre sur la plateforme.</p>
      </div>
    </div>
  </div>

  
  <div class="stat-card">
    <h3 style="font-size:.8rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#6b7280;border-bottom:1px solid #e5e7eb;padding-bottom:10px;margin-bottom:16px;">
      <i class="fa-solid fa-book mr-2" style="color:var(--aws-orange);"></i>Détails de publication
    </h3>
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;">
      <div>
        <label class="lx-label">Format numérique <span style="color:#dc2626;">*</span></label>
        <select name="format" required class="lx-select">
          <option value="pdf"  <?php echo e(old('format')==='pdf'?'selected':''); ?>>PDF</option>
          <option value="epub" <?php echo e(old('format')==='epub'?'selected':''); ?>>ePub</option>
          <option value="both" <?php echo e(old('format','both')==='both'?'selected':''); ?>>PDF + ePub</option>
        </select>
      </div>
      <div>
        <label class="lx-label">Nombre de pages</label>
        <input type="number" name="pages" value="<?php echo e(old('pages')); ?>" min="1" class="lx-input" placeholder="ex: 250"/>
      </div>
      <div>
        <label class="lx-label">Année de publication</label>
        <input type="number" name="publication_year" value="<?php echo e(old('publication_year',date('Y'))); ?>"
          min="1800" max="<?php echo e(date('Y')); ?>" class="lx-input"/>
      </div>
      <div>
        <label class="lx-label">ISBN</label>
        <input type="text" name="isbn" value="<?php echo e(old('isbn')); ?>" maxlength="20" class="lx-input" placeholder="978-…"/>
        <p style="font-size:.7rem;color:#9ca3af;margin-top:3px;">Facultatif · identifiant international unique</p>
      </div>
      <div class="col-span-2" style="grid-column:span 2;">
        <label class="lx-label">Éditeur / Maison d'édition</label>
        <input type="text" name="publisher" value="<?php echo e(old('publisher')); ?>" class="lx-input"
          placeholder="Auto-édition, Éditions XYZ, CEDA, Présence Africaine…"/>
      </div>
      <div style="grid-column:span 3;">
        <label class="lx-label">Droits & Licence</label>
        <select name="rights" class="lx-select">
          <option value="all_rights" <?php echo e(old('rights','all_rights')==='all_rights'?'selected':''); ?>>© Tous droits réservés</option>
          <option value="cc_by"      <?php echo e(old('rights')==='cc_by'?'selected':''); ?>>Creative Commons – Attribution (CC BY)</option>
          <option value="cc_by_nc"   <?php echo e(old('rights')==='cc_by_nc'?'selected':''); ?>>Creative Commons – Attribution Non-Commerciale (CC BY-NC)</option>
          <option value="cc_by_sa"   <?php echo e(old('rights')==='cc_by_sa'?'selected':''); ?>>Creative Commons – Attribution Partage à l'identique (CC BY-SA)</option>
          <option value="public"     <?php echo e(old('rights')==='public'?'selected':''); ?>>Domaine public</option>
        </select>
      </div>
    </div>
  </div>

  
  <div class="stat-card" id="academicSection" style="display:none;">
    <h3 style="font-size:.8rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#6b7280;border-bottom:1px solid #e5e7eb;padding-bottom:10px;margin-bottom:16px;">
      <i class="fa-solid fa-graduation-cap mr-2" style="color:var(--aws-orange);"></i>Informations académiques
    </h3>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
      <div>
        <label class="lx-label">Université / Institution</label>
        <input type="text" name="university" value="<?php echo e(old('university')); ?>" class="lx-input"
          placeholder="Université Marien Ngouabi, UNIKIN…"/>
      </div>
      <div>
        <label class="lx-label">Directeur de recherche / Encadreur</label>
        <input type="text" name="supervisor" value="<?php echo e(old('supervisor')); ?>" class="lx-input"
          placeholder="Prof. Dupont, Dr. Mbeki…"/>
      </div>
      <div style="grid-column:span 2;">
        <label class="lx-label">Domaine / Filière</label>
        <input type="text" name="field_of_study" value="<?php echo e(old('field_of_study')); ?>" class="lx-input"
          placeholder="Lettres modernes, Sciences économiques, Droit…"/>
      </div>
    </div>
  </div>

  
  <div class="stat-card">
    <h3 style="font-size:.8rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#6b7280;border-bottom:1px solid #e5e7eb;padding-bottom:10px;margin-bottom:16px;">
      <i class="fa-solid fa-file-arrow-up mr-2" style="color:var(--aws-orange);"></i>Fichiers
    </h3>
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px;">

      
      <div>
        <label class="lx-label">Couverture <span style="color:#dc2626;">*</span></label>
        <div id="cover_drop" class="lx-dropzone" onclick="document.getElementById('cover_input').click()">
          <i class="fa-solid fa-image" style="font-size:1.6rem;color:#d1d5db;display:block;margin-bottom:6px;"></i>
          <p style="font-size:.78rem;font-weight:600;color:#6b7280;">JPG, PNG, WebP</p>
          <p style="font-size:.7rem;color:#9ca3af;margin-top:2px;">Max 10 Mo · 400×600 px</p>
        </div>
        <input type="file" id="cover_input" name="cover_image" accept="image/jpeg,image/png,image/webp" required class="hidden" onchange="previewCover(this)"/>
        <img id="cover_preview" style="display:none;margin:8px auto 0;width:60px;height:80px;object-fit:cover;border-radius:4px;box-shadow:0 1px 4px rgba(0,0,0,.15);" alt=""/>
        <?php $__errorArgs = ['cover_image'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="lx-err"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
      </div>

      
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
        <?php $__errorArgs = ['book_file'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="lx-err"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
      </div>

      
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
        <?php $__errorArgs = ['preview_file'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="lx-err"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
      </div>
    </div>
  </div>

</div>


<div style="display:flex;flex-direction:column;gap:14px;position:sticky;top:68px;">

  
  <div class="stat-card">
    <h3 style="font-size:.8rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#6b7280;border-bottom:1px solid #e5e7eb;padding-bottom:10px;margin-bottom:14px;">
      <i class="fa-solid fa-tag mr-2" style="color:var(--aws-orange);"></i>Tarification numérique
    </h3>

    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;margin-bottom:12px;">
      <input type="checkbox" name="is_free" id="isFree" <?php echo e(old('is_free')?'checked':''); ?>

        style="width:14px;height:14px;accent-color:var(--aws-orange);" onchange="togglePrice()"/>
      <span style="font-size:.82rem;font-weight:600;color:#374151;">Livre gratuit</span>
    </label>

    <div id="priceField">
      <div style="display:grid;grid-template-columns:1fr auto;gap:8px;align-items:end;margin-bottom:6px;">
        <div>
          <label class="lx-label">Prix</label>
          <input type="number" name="price" value="<?php echo e(old('price',0)); ?>" min="0" step="100"
            class="lx-input" style="padding-right:52px;position:relative;"/>
        </div>
        <div>
          <label class="lx-label">Devise</label>
          <select name="currency" class="lx-select" style="width:76px;">
            <option value="XAF" <?php echo e(old('currency','XAF')==='XAF'?'selected':''); ?>>XAF</option>
            <option value="USD" <?php echo e(old('currency')==='USD'?'selected':''); ?>>USD</option>
            <option value="EUR" <?php echo e(old('currency')==='EUR'?'selected':''); ?>>EUR</option>
          </select>
        </div>
      </div>
      <p style="font-size:.72rem;color:#6b7280;">Votre part : <strong style="color:#15803d;">selon votre forfait</strong></p>
    </div>
  </div>

  
  <div class="stat-card">
    <h3 style="font-size:.8rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#6b7280;border-bottom:1px solid #e5e7eb;padding-bottom:10px;margin-bottom:14px;">
      <i class="fa-solid fa-clock mr-2" style="color:var(--aws-orange);"></i>Location de lecture
    </h3>
    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;margin-bottom:12px;">
      <input type="checkbox" name="allow_rental" id="isRental" <?php echo e(old('allow_rental')?'checked':''); ?>

        style="width:14px;height:14px;accent-color:var(--aws-orange);" onchange="toggleRental()"/>
      <span style="font-size:.82rem;font-weight:600;color:#374151;">Autoriser la location</span>
    </label>
    <div id="rentalField" style="display:none;">
      <label class="lx-label">Prix / heure (XAF)</label>
      <input type="number" name="rental_price_hour" value="<?php echo e(old('rental_price_hour',100)); ?>" min="0" step="50" class="lx-input"/>
      <p style="font-size:.72rem;color:#9ca3af;margin-top:3px;">Le lecteur paie pour un accès temporaire.</p>
    </div>
  </div>

  
  <div class="stat-card">
    <h3 style="font-size:.8rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#6b7280;border-bottom:1px solid #e5e7eb;padding-bottom:10px;margin-bottom:14px;">
      <i class="fa-solid fa-print mr-2" style="color:var(--aws-orange);"></i>Impression à la demande
    </h3>
    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;margin-bottom:12px;">
      <input type="checkbox" name="print_on_demand" id="isPrint" <?php echo e(old('print_on_demand')?'checked':''); ?>

        style="width:14px;height:14px;accent-color:var(--aws-orange);" onchange="togglePrint()"/>
      <span style="font-size:.82rem;font-weight:600;color:#374151;">Activer l'impression physique</span>
    </label>
    <div id="printField" style="display:none;">
      <label class="lx-label">Prix impression (XAF)</label>
      <input type="number" name="print_price" value="<?php echo e(old('print_price')); ?>" min="0" step="500" class="lx-input"/>
    </div>
  </div>

  
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
  <a href="<?php echo e(route('author.books.index')); ?>"
     style="display:block;text-align:center;font-size:.8rem;color:#6b7280;padding:6px;text-decoration:none;">
     Annuler
  </a>

</div>
</div>
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

<?php $__env->startPush('scripts'); ?>
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
  if ('<?php echo e(old('is_free')); ?>') togglePrice();
  if ('<?php echo e(old('print_on_demand')); ?>') togglePrint();
  if ('<?php echo e(old('allow_rental')); ?>') toggleRental();
});
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.author', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Lenovo Yoga\Downloads\LireX-APP\backend\resources\views/author/books/create.blade.php ENDPATH**/ ?>