@extends('layouts.author')
@section('title', 'Mon Profil – LireX')
@section('page-title', 'Mon Profil Auteur')
@section('page-subtitle', 'Complétez votre profil pour gagner en crédibilité auprès des lecteurs')

@section('content')
<div class="max-w-3xl space-y-5">

  @if(session('success'))
    <div class="aws-alert-ok"><i class="fa-solid fa-circle-check"></i> {{ session('success') }}</div>
  @endif
  @if($errors->any())
    <div class="aws-alert-err">
      <i class="fa-solid fa-circle-xmark"></i>
      <ul class="list-disc list-inside space-y-0.5">
        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
      </ul>
    </div>
  @endif

  <form action="{{ route('author.profile.update') }}" method="POST" enctype="multipart/form-data">
    @csrf @method('PUT')

    {{-- ── Avatar card ── --}}
    <div class="stat-card mb-5">
      <div class="flex items-center gap-5">
        <div class="relative shrink-0">
          <img id="avatar-preview" src="{{ $user->avatar_url }}" alt="Avatar"
               style="width:80px;height:80px;border-radius:50%;object-fit:cover;border:3px solid var(--aws-orange);">
          <label style="position:absolute;bottom:-4px;right:-4px;width:26px;height:26px;background:var(--aws-orange);border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;" title="Changer la photo">
            <i class="fa-solid fa-camera" style="font-size:.65rem;color:#111;"></i>
            <input type="file" name="avatar" accept="image/jpeg,image/png,image/webp" class="hidden" onchange="previewAvatar(this)">
          </label>
        </div>
        <div>
          <p style="font-size:1.1rem;font-weight:700;color:#16191f;">{{ $user->name }}</p>
          <p style="font-size:.8rem;color:#6b7280;">{{ $user->email }}</p>
          <div style="margin-top:6px;display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
            @if($user->is_verified_author)
              <span style="display:inline-flex;align-items:center;gap:4px;padding:2px 10px;border-radius:12px;font-size:.7rem;font-weight:700;background:#d1fae5;color:#065f46;">
                <i class="fa-solid fa-circle-check" style="font-size:.6rem;"></i> Auteur vérifié
              </span>
            @else
              <span style="display:inline-flex;align-items:center;gap:4px;padding:2px 10px;border-radius:12px;font-size:.7rem;font-weight:700;background:#fef3c7;color:#92400e;">
                <i class="fa-solid fa-clock" style="font-size:.6rem;"></i> Vérification en attente
              </span>
            @endif
            @if($user->domain)
              <span style="font-size:.75rem;color:#6b7280;"><i class="fa-solid fa-pen-nib" style="color:var(--aws-orange);"></i> {{ $user->domain }}</span>
            @endif
          </div>
        </div>
        <div style="margin-left:auto;text-align:right;">
          <p style="font-size:.7rem;color:#9ca3af;">JPG, PNG, WebP · max 2 Mo</p>
          <p style="font-size:.7rem;color:#9ca3af;">Recommandé : carré 300×300 px</p>
        </div>
      </div>
    </div>

    {{-- ── Identité auteur ── --}}
    <div class="stat-card mb-5">
      <h2 style="font-size:.8rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#6b7280;border-bottom:1px solid #e5e7eb;padding-bottom:10px;margin-bottom:16px;">
        <i class="fa-solid fa-user mr-2" style="color:var(--aws-orange);"></i>Identité & biographie
      </h2>
      <div class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label style="display:block;font-size:.78rem;font-weight:600;color:#374151;margin-bottom:5px;">Nom complet <span style="color:#dc2626;">*</span></label>
            <input type="text" name="name" value="{{ old('name', $user->name) }}" required maxlength="100"
              style="width:100%;border:1px solid #d1d5db;border-radius:4px;padding:7px 10px;font-size:.82rem;outline:none;"
              onfocus="this.style.borderColor='#ff9900'" onblur="this.style.borderColor='#d1d5db'">
          </div>
          <div>
            <label style="display:block;font-size:.78rem;font-weight:600;color:#374151;margin-bottom:5px;">Téléphone</label>
            <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="+242 06 xxx xxxx"
              style="width:100%;border:1px solid #d1d5db;border-radius:4px;padding:7px 10px;font-size:.82rem;outline:none;"
              onfocus="this.style.borderColor='#ff9900'" onblur="this.style.borderColor='#d1d5db'">
          </div>
        </div>

        <div>
          <label style="display:block;font-size:.78rem;font-weight:600;color:#374151;margin-bottom:5px;">
            Domaine / Spécialité littéraire
          </label>
          <select name="domain"
            style="width:100%;border:1px solid #d1d5db;border-radius:4px;padding:7px 10px;font-size:.82rem;outline:none;background:#fff;"
            onfocus="this.style.borderColor='#ff9900'" onblur="this.style.borderColor='#d1d5db'">
            <option value="">— Choisir un domaine —</option>
            @foreach([
              'Roman'              => '📖 Roman',
              'Nouvelle'           => '📄 Nouvelle',
              'Poésie'             => '🖊️ Poésie',
              'Essai'              => '📝 Essai & Critique',
              'Conte'              => '🌙 Conte & Fable',
              'Biographie'         => '👤 Biographie / Mémoire',
              'Théâtre'            => '🎭 Théâtre',
              'Science-fiction'    => '🚀 Science-fiction / Fantasy',
              'Policier'           => '🔍 Policier / Thriller',
              'Jeunesse'           => '🧒 Littérature jeunesse',
              'Développement personnel' => '💡 Développement personnel',
              'Académique'         => '🎓 Académique / Universitaire',
              'Philosophie'        => '🧠 Philosophie',
              'Religion & Spiritualité' => '✝️ Religion & Spiritualité',
              'Histoire'           => '🏺 Histoire & Patrimoine',
              'Autre'              => '✏️ Autre',
            ] as $val => $label)
            <option value="{{ $val }}" {{ old('domain', $user->domain) === $val ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
          </select>
          <p style="font-size:.72rem;color:#9ca3af;margin-top:4px;">Affiché sur votre page auteur et aide les lecteurs à vous trouver.</p>
        </div>

        <div>
          <label style="display:block;font-size:.78rem;font-weight:600;color:#374151;margin-bottom:5px;">
            Biographie <span style="font-weight:400;color:#9ca3af;">(max 2 000 caractères)</span>
          </label>
          <textarea name="bio" rows="5" maxlength="2000"
            placeholder="Décrivez votre parcours, vos influences, vos œuvres marquantes, ce qui vous a poussé à écrire…"
            style="width:100%;border:1px solid #d1d5db;border-radius:4px;padding:8px 10px;font-size:.82rem;outline:none;resize:vertical;"
            onfocus="this.style.borderColor='#ff9900'" onblur="this.style.borderColor='#d1d5db'"
            oninput="document.getElementById('bio_count').textContent = this.value.length">{{ old('bio', $user->bio) }}</textarea>
          <p style="font-size:.72rem;color:#9ca3af;margin-top:3px;">
            <span id="bio_count">{{ strlen($user->bio ?? '') }}</span>/2000 caractères · Une bonne biographie augmente la confiance des lecteurs.
          </p>
        </div>
      </div>
    </div>

    {{-- ── Localisation ── --}}
    <div class="stat-card mb-5">
      <h2 style="font-size:.8rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#6b7280;border-bottom:1px solid #e5e7eb;padding-bottom:10px;margin-bottom:16px;">
        <i class="fa-solid fa-location-dot mr-2" style="color:var(--aws-orange);"></i>Localisation & Web
      </h2>
      <div class="grid grid-cols-3 gap-4">
        <div>
          <label style="display:block;font-size:.78rem;font-weight:600;color:#374151;margin-bottom:5px;">Pays</label>
          <select name="country"
            style="width:100%;border:1px solid #d1d5db;border-radius:4px;padding:7px 10px;font-size:.82rem;outline:none;background:#fff;"
            onfocus="this.style.borderColor='#ff9900'" onblur="this.style.borderColor='#d1d5db'">
            @foreach(['CG'=>'🇨🇬 Congo-Brazzaville','CD'=>'🇨🇩 RD Congo','CM'=>'🇨🇲 Cameroun','GA'=>'🇬🇦 Gabon','CF'=>'🇨🇫 Centrafrique','SN'=>'🇸🇳 Sénégal','CI'=>'🇨🇮 Côte d\'Ivoire','ML'=>'🇲🇱 Mali','BF'=>'🇧🇫 Burkina Faso','TG'=>'🇹🇬 Togo','BJ'=>'🇧🇯 Bénin','NE'=>'🇳🇪 Niger','MG'=>'🇲🇬 Madagascar','FR'=>'🇫🇷 France','BE'=>'🇧🇪 Belgique','CA'=>'🇨🇦 Canada','OTHER'=>'🌍 Autre'] as $code => $name)
            <option value="{{ $code }}" {{ old('country', $user->country ?? 'CG') === $code ? 'selected' : '' }}>{{ $name }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label style="display:block;font-size:.78rem;font-weight:600;color:#374151;margin-bottom:5px;">Ville</label>
          <input type="text" name="city" value="{{ old('city', $user->city) }}" placeholder="Brazzaville, Kinshasa…"
            style="width:100%;border:1px solid #d1d5db;border-radius:4px;padding:7px 10px;font-size:.82rem;outline:none;"
            onfocus="this.style.borderColor='#ff9900'" onblur="this.style.borderColor='#d1d5db'">
        </div>
        <div>
          <label style="display:block;font-size:.78rem;font-weight:600;color:#374151;margin-bottom:5px;">Site web</label>
          <input type="url" name="website" value="{{ old('website', $user->website) }}" placeholder="https://monsite.com"
            style="width:100%;border:1px solid #d1d5db;border-radius:4px;padding:7px 10px;font-size:.82rem;outline:none;"
            onfocus="this.style.borderColor='#ff9900'" onblur="this.style.borderColor='#d1d5db'">
        </div>
      </div>
    </div>

    {{-- ── Paiement Mobile Money ── --}}
    <div class="stat-card mb-5">
      <h2 style="font-size:.8rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#6b7280;border-bottom:1px solid #e5e7eb;padding-bottom:10px;margin-bottom:16px;">
        <i class="fa-solid fa-mobile-screen mr-2" style="color:var(--aws-orange);"></i>Coordonnées de paiement Mobile Money
      </h2>
      <p style="font-size:.78rem;color:#6b7280;margin-bottom:14px;">Ces numéros sont utilisés pour vous verser vos royalties lors des retraits.</p>
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label style="display:block;font-size:.78rem;font-weight:600;color:#374151;margin-bottom:5px;">
            <span style="display:inline-block;width:10px;height:10px;background:#FFCC00;border-radius:50%;margin-right:5px;"></span>
            MTN Mobile Money
          </label>
          <input type="text" name="mtn_number" value="{{ old('mtn_number', $user->mtn_number) }}" placeholder="+242 06 xxx xxxx"
            style="width:100%;border:1px solid #d1d5db;border-radius:4px;padding:7px 10px;font-size:.82rem;outline:none;"
            onfocus="this.style.borderColor='#ff9900'" onblur="this.style.borderColor='#d1d5db'">
        </div>
        <div>
          <label style="display:block;font-size:.78rem;font-weight:600;color:#374151;margin-bottom:5px;">
            <span style="display:inline-block;width:10px;height:10px;background:#E40026;border-radius:50%;margin-right:5px;"></span>
            Airtel Money
          </label>
          <input type="text" name="airtel_number" value="{{ old('airtel_number', $user->airtel_number) }}" placeholder="+242 05 xxx xxxx"
            style="width:100%;border:1px solid #d1d5db;border-radius:4px;padding:7px 10px;font-size:.82rem;outline:none;"
            onfocus="this.style.borderColor='#ff9900'" onblur="this.style.borderColor='#d1d5db'">
        </div>
      </div>
    </div>

    {{-- ── Mot de passe ── --}}
    <div class="stat-card mb-5">
      <h2 style="font-size:.8rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#6b7280;border-bottom:1px solid #e5e7eb;padding-bottom:10px;margin-bottom:16px;">
        <i class="fa-solid fa-lock mr-2" style="color:var(--aws-orange);"></i>Changer le mot de passe
      </h2>
      <p style="font-size:.78rem;color:#6b7280;margin-bottom:14px;">Laissez vide pour conserver le mot de passe actuel.</p>
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label style="display:block;font-size:.78rem;font-weight:600;color:#374151;margin-bottom:5px;">Nouveau mot de passe</label>
          <input type="password" name="password" placeholder="Min. 8 caractères"
            style="width:100%;border:1px solid #d1d5db;border-radius:4px;padding:7px 10px;font-size:.82rem;outline:none;"
            onfocus="this.style.borderColor='#ff9900'" onblur="this.style.borderColor='#d1d5db'">
        </div>
        <div>
          <label style="display:block;font-size:.78rem;font-weight:600;color:#374151;margin-bottom:5px;">Confirmer</label>
          <input type="password" name="password_confirmation" placeholder="••••••••"
            style="width:100%;border:1px solid #d1d5db;border-radius:4px;padding:7px 10px;font-size:.82rem;outline:none;"
            onfocus="this.style.borderColor='#ff9900'" onblur="this.style.borderColor='#d1d5db'">
        </div>
      </div>
    </div>

    {{-- ── Actions ── --}}
    <div class="flex items-center gap-3">
      <button type="submit" class="btn-aws px-8 py-2.5 text-sm">
        <i class="fa-solid fa-floppy-disk mr-2"></i> Enregistrer le profil
      </button>
      <a href="{{ route('author.dashboard') }}"
         style="padding:7px 18px;border:1px solid #d1d5db;border-radius:4px;font-size:.82rem;color:#374151;text-decoration:none;">
        Annuler
      </a>
    </div>

  </form>
</div>

@push('scripts')
<script>
function previewAvatar(input) {
  if (input.files?.[0]) {
    const mb = input.files[0].size / 1048576;
    if (mb > 2) { alert('Photo trop lourde (max 2 Mo)'); input.value=''; return; }
    const r = new FileReader();
    r.onload = e => document.getElementById('avatar-preview').src = e.target.result;
    r.readAsDataURL(input.files[0]);
  }
}
</script>
@endpush
@endsection
