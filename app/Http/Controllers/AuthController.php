<?php
// ─── app/Http/Controllers/AuthController.php (Web sessions) ──────────────────
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Hash, Password};
use App\Models\User;

class AuthController extends Controller
{
    public function showLogin()  { return view('auth.login'); }
    public function showRegister() { return view('auth.register'); }

    public function login(Request $request)
    {
        $creds = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if (!Auth::attempt($creds, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Identifiants incorrects.'])->withInput();
        }

        $user = Auth::user();
        $request->session()->regenerate();

        return match ($user->role) {
            'admin'  => redirect()->route('admin.dashboard'),
            'author' => redirect()->route('author.dashboard'),
            default  => redirect()->intended('/'),
        };
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users',
            'phone'    => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'role'     => 'required|in:reader,author',
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'phone'    => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
            'role'     => $data['role'],
        ]);

        Auth::login($user);

        return match ($user->role) {
            'author' => redirect()->route('author.dashboard'),
            default  => redirect('/'),
        };
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    public function showForgot() { return view('auth.forgot-password'); }

    public function sendReset(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $status = Password::sendResetLink($request->only('email'));
        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', 'Lien de réinitialisation envoyé !')
            : back()->withErrors(['email' => 'Email introuvable.']);
    }

    public function showReset(string $token) { return view('auth.reset-password', ['token' => $token]); }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            fn ($user, $password) => $user->forceFill(['password' => Hash::make($password)])->save()
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('success', 'Mot de passe réinitialisé !')
            : back()->withErrors(['email' => __($status)]);
    }
}
