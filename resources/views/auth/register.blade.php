<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LireX – Inscription Auteur</title>
    <link rel="icon" type="image/x-icon" href="/favicon.ico" />
    <link rel="icon" type="image/svg+xml" href="/favicon.svg" />
    <link rel="apple-touch-icon" href="/favicon-192.png" />
    <meta name="theme-color" content="#1a1a2e" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,700;1,700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f0ede8; min-height: 100vh; overflow-x: hidden; }

        /* ── Formes géométriques flottantes (identique à la page login) ── */
        .shape {
            position: fixed;
            border-radius: 14px;
            z-index: 0;
        }
        .s1  { width:120px; height:120px; background:#f59e0b; opacity:.50; top:3%;  left:2%;  transform:rotate(18deg); }
        .s2  { width:72px;  height:72px;  background:#10b981; opacity:.45; top:14%; left:17%; transform:rotate(-12deg); }
        .s3  { width:95px;  height:95px;  background:#f97316; opacity:.45; top:2%;  left:54%; transform:rotate(32deg); }
        .s4  { width:58px;  height:58px;  background:#8b5cf6; opacity:.40; top:7%;  left:76%; transform:rotate(-22deg); }
        .s5  { width:140px; height:140px; background:#f59e0b; opacity:.28; top:54%; left:1%;  transform:rotate(10deg); }
        .s6  { width:68px;  height:68px;  background:#10b981; opacity:.42; top:73%; left:14%; transform:rotate(-38deg); }
        .s7  { width:84px;  height:84px;  background:#ef4444; opacity:.35; top:80%; left:62%; transform:rotate(26deg); }
        .s8  { width:52px;  height:52px;  background:#f59e0b; opacity:.45; top:88%; left:83%; transform:rotate(-18deg); }
        .s9  { width:105px; height:105px; background:#3b82f6; opacity:.28; top:40%; left:0%;  transform:rotate(46deg); }
        .s10 { width:64px;  height:64px;  background:#8b5cf6; opacity:.38; top:66%; left:89%; transform:rotate(-32deg); }
        .s11 { width:88px;  height:88px;  background:#10b981; opacity:.30; top:28%; left:91%; transform:rotate(22deg); }
        .s12 { width:48px;  height:48px;  background:#f97316; opacity:.22; top:50%; left:51%; transform:rotate(-8deg); }
        .s13 { width:76px;  height:76px;  background:#ef4444; opacity:.30; top:35%; left:38%; transform:rotate(42deg); }

        /* ── Logo ── */
        .logo-sub { font-size:.58rem; letter-spacing:.2em; color:#64748b; text-transform:uppercase; font-weight:500; margin-top:1px; }

        /* ── Carte ── */
        .card {
            background: rgba(255,255,255,0.93);
            backdrop-filter: blur(14px);
            border-radius: 22px;
            box-shadow: 0 24px 64px rgba(0,0,0,0.13);
            padding: 2.4rem 2.2rem;
            width: 100%;
            max-width: 430px;
            position: relative;
            z-index: 10;
        }

        /* ── Inputs ── */
        .field {
            width: 100%;
            padding: .78rem 1rem;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            font-size: .875rem;
            color: #1e293b;
            background: #f8fafc;
            outline: none;
            transition: border-color .2s, background .2s;
        }
        .field:focus { border-color: #f59e0b; background: #fff; box-shadow: 0 0 0 3px rgba(245,158,11,.12); }
        .field::placeholder { color: #94a3b8; }

        /* ── Bouton ── */
        .btn {
            width: 100%;
            padding: .88rem;
            background: #1e293b;
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: .9rem;
            font-weight: 600;
            cursor: pointer;
            letter-spacing: .01em;
            transition: background .2s, transform .1s;
        }
        .btn:hover  { background: #0f172a; }
        .btn:active { transform: scale(.98); }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen px-4 py-10">

    {{-- Formes de fond --}}
    <div class="shape s1"></div><div class="shape s2"></div><div class="shape s3"></div>
    <div class="shape s4"></div><div class="shape s5"></div><div class="shape s6"></div>
    <div class="shape s7"></div><div class="shape s8"></div><div class="shape s9"></div>
    <div class="shape s10"></div><div class="shape s11"></div><div class="shape s12"></div>
    <div class="shape s13"></div>

    <div class="card">

        {{-- Logo LireX --}}
        <div class="flex items-center gap-3 mb-7">
            <div style="border:2px solid #1e293b; border-radius:9px; width:44px; height:44px; display:flex; align-items:center; justify-content:center;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M2 6C2 4.9 2.9 4 4 4H11V20H4C2.9 20 2 19.1 2 18V6Z" stroke="#1e293b" stroke-width="1.8"/>
                    <path d="M13 4H20C21.1 4 22 4.9 22 6V18C22 19.1 21.1 20 20 20H13V4Z" stroke="#2563eb" stroke-width="1.8"/>
                    <line x1="12" y1="4" x2="12" y2="20" stroke="#94a3b8" stroke-width="1.2"/>
                </svg>
            </div>
            <div>
                <div style="font-family:'Playfair Display',serif; font-size:1.55rem; font-weight:700; color:#1e293b; line-height:1;">
                    Lire<span style="color:#2563eb;">X</span>
                </div>
                <div class="logo-sub">Plateforme de lecture</div>
            </div>
        </div>

        <h2 class="text-xl font-bold text-slate-800 mb-1">Rejoindre LireX en tant qu'auteur</h2>
        <p class="text-slate-500 text-sm mb-6">Publiez vos ouvrages et touchez vos lecteurs.</p>

        @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl p-3 mb-4 text-sm">{{ $errors->first() }}</div>
        @endif

        <form action="{{ route('register.post') }}" method="POST" class="space-y-4">
            @csrf
            {{-- Rôle auteur fixé — cette page est réservée aux auteurs --}}
            <input type="hidden" name="role" value="author">

            <div>
                <label class="block text-slate-700 text-sm font-medium mb-1.5">Nom complet</label>
                <input type="text" name="name" value="{{ old('name') }}" required
                    placeholder="Jean-Pierre Mbemba" class="field">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-slate-700 text-sm font-medium mb-1.5">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                        placeholder="jean@exemple.cg" class="field">
                </div>
                <div>
                    <label class="block text-slate-700 text-sm font-medium mb-1.5">
                        Téléphone <span class="font-normal text-slate-400 text-xs">(optionnel)</span>
                    </label>
                    <input type="text" name="phone" value="{{ old('phone') }}"
                        placeholder="+242 06 xxx xxxx" class="field">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-slate-700 text-sm font-medium mb-1.5">Mot de passe</label>
                    <input type="password" name="password" required
                        placeholder="Min. 8 caractères" class="field">
                </div>
                <div>
                    <label class="block text-slate-700 text-sm font-medium mb-1.5">Confirmer</label>
                    <input type="password" name="password_confirmation" required
                        placeholder="••••••••" class="field">
                </div>
            </div>

            <button type="submit" class="btn">Créer mon compte auteur →</button>
        </form>

        <div class="mt-5 pt-5 border-t border-slate-100 text-center text-sm text-slate-500">
            Déjà inscrit ?
            <a href="{{ route('login') }}" class="text-amber-600 font-semibold hover:text-amber-700 ml-1">Se connecter</a>
        </div>

        <p class="text-center text-[11px] text-slate-400 mt-5">
            © {{ date('Y') }} LireX — Développé avec <span style="color:#ef4444;">❤</span> par <strong class="text-slate-500">Basile Marius NGASSAKI ZONI</strong>
        </p>
    </div>

</body>
</html>
