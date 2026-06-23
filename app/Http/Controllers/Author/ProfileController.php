<?php

namespace App\Http\Controllers\Author;

use App\Http\Controllers\Controller;
use App\Models\{Book, Order, Royalty, WithdrawalRequest, BookTag};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Storage, DB};
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('author.profile.edit', ['user' => Auth::user()]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $data = $request->validate([
            'name'          => 'required|string|max:100',
            'bio'           => 'nullable|string|max:2000',
            'domain'        => 'nullable|string|max:100',
            'website'       => 'nullable|url|max:200',
            'phone'         => 'nullable|string|max:20',
            'city'          => 'nullable|string|max:100',
            'country'       => 'nullable|string|max:5',
            'mtn_number'    => 'nullable|string|max:20',
            'airtel_number' => 'nullable|string|max:20',
            'avatar'        => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('avatar')) {
            if ($user->avatar) Storage::disk('public')->delete($user->avatar);
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        if ($request->filled('password')) {
            $request->validate(['password' => 'confirmed|min:8']);
            $data['password'] = bcrypt($request->password);
        }

        $user->update($data);
        return back()->with('success', 'Profil mis à jour.');
    }
}
