<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LireX – Mot de passe oublié</title>
    <link rel="icon" type="image/x-icon" href="/favicon.ico" />
    <link rel="icon" type="image/svg+xml" href="/favicon.svg" />
    <link rel="apple-touch-icon" href="/favicon-192.png" />
    <meta name="theme-color" content="#1a1a2e" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <style>body{font-family:'Inter',sans-serif;}.logo-font{font-family:'Playfair Display',serif;}</style>
</head>
<body class="min-h-screen flex items-center justify-center" style="background:#0A1628">
    <div class="w-full max-w-md p-8">
        <div class="text-center mb-8">
            <div class="text-white logo-font text-4xl font-bold">LireX</div>
            <div class="text-blue-300 text-sm mt-1 tracking-widest uppercase">Plateforme de Lecture</div>
        </div>

        <div class="bg-white rounded-2xl shadow-xl p-8">
            <h1 class="text-xl font-bold text-gray-900 mb-2">Mot de passe oublié</h1>
            <p class="text-sm text-gray-500 mb-6">Entrez votre email pour recevoir un lien de réinitialisation.</p>

            @if (session('status'))
                <div class="mb-4 p-3 bg-green-50 text-green-700 rounded-lg text-sm">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="mb-4 p-3 bg-red-50 text-red-700 rounded-lg text-sm">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                        placeholder="vous@email.com">
                </div>
                <button type="submit"
                    class="w-full py-2.5 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors">
                    Envoyer le lien
                </button>
            </form>

            <p class="text-center text-sm text-gray-600 mt-6">
                <a href="{{ route('login') }}" class="text-blue-600 font-medium hover:underline">← Retour à la connexion</a>
            </p>
        </div>
    </div>
</body>
</html>
