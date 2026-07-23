<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\{User, Book, Order, Category, Review, Royalty, ReadingProgress};
use App\Services\PaymentService;
use Illuminate\Http\{Request, JsonResponse};
use Illuminate\Support\Facades\{Auth, DB, Hash, Storage};
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users',
            'phone'    => 'nullable|string|max:20|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        try {
            [$user, $token] = DB::transaction(function () use ($data) {
                $user  = User::create(array_merge($data, ['role' => 'reader']));
                $token = JWTAuth::fromUser($user);
                return [$user, $token];
            });
        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => "Une erreur technique est survenue. Le compte n'a pas été créé, veuillez réessayer.",
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Compte créé avec succès.',
            'data'    => ['user' => $user, 'token' => $token, 'token_type' => 'Bearer'],
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['success' => false, 'message' => 'Identifiants incorrects.'], 401);
        }

        $user = Auth::user();
        if (!$user->is_active) {
            return response()->json(['success' => false, 'message' => 'Compte suspendu. Contactez le support.'], 403);
        }

        return response()->json([
            'success' => true,
            'data'    => ['user' => $user, 'token' => $token, 'token_type' => 'Bearer', 'expires_in' => config('jwt.ttl') * 60],
        ]);
    }

    public function logout(): JsonResponse
    {
        JWTAuth::invalidate(JWTAuth::getToken());
        return response()->json(['success' => true, 'message' => 'Déconnecté.']);
    }

    public function refresh(): JsonResponse
    {
        return response()->json(['token' => JWTAuth::refresh()]);
    }

    public function me(): JsonResponse
    {
        $user = Auth::user();
        $user->load('subscriptions');
        return response()->json(['success' => true, 'data' => array_merge($user->toArray(), [
            'has_active_subscription' => $user->hasActiveSubscription(),
            'pending_balance'         => $user->pending_balance,
        ])]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $user = Auth::user();
        $data = $request->validate([
            'name'   => 'sometimes|string|max:100',
            'bio'    => 'nullable|string|max:1000',
            'phone'  => 'nullable|string|max:20|unique:users,phone,' . $user->id,
            'city'   => 'nullable|string|max:100',
            'avatar' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('avatar')) {
            if ($user->avatar) Storage::disk('public')->delete($user->avatar);
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }
        $user->update($data);
        return response()->json(['success' => true, 'data' => $user->fresh()]);
    }

    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required',
            'password'         => 'required|min:8|confirmed',
        ]);
        if (!Hash::check($request->current_password, Auth::user()->password)) {
            return response()->json(['success' => false, 'message' => 'Mot de passe actuel incorrect.'], 400);
        }
        Auth::user()->update(['password' => bcrypt($request->password)]);
        return response()->json(['success' => true, 'message' => 'Mot de passe modifié.']);
    }
}
