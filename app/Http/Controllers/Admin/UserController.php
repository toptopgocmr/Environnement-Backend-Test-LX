<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{User, Book, Order, Royalty, Category, WithdrawalRequest};
use App\Models\PlatformSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Storage};

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::when($request->role, fn($q) => $q->where('role', $request->role))
            ->when($request->search, fn($q) => $q->where(function ($q) use ($request) {
                $q->where('name',  'LIKE', "%{$request->search}%")
                  ->orWhere('email', 'LIKE', "%{$request->search}%");
            }))
            ->latest()->paginate(20);
        return view('admin.users.index', compact('users'));
    }

    public function show(User $user)
    {
        $user->load('books', 'orders.book');
        $stats = [
            'books_count'    => $user->books()->count(),
            'orders_count'   => $user->orders()->where('payment_status', 'paid')->count(),
            'total_earnings' => $user->royalties()->where('status', 'paid')->sum('net_amount'),
            'pending_balance'=> $user->royalties()->where('status', 'pending')->sum('net_amount'),
        ];
        return view('admin.users.show', compact('user', 'stats'));
    }

    public function toggleActive(User $user)
    {
        if ($user->isAdmin()) return back()->with('error', 'Impossible de suspendre un administrateur.');
        $user->update(['is_active' => !$user->is_active]);
        return back()->with('success', 'Statut mis à jour.');
    }

    public function verifyAuthor(User $user)
    {
        $user->update(['is_verified_author' => true, 'role' => 'author']);
        return back()->with('success', "Auteur {$user->name} vérifié.");
    }

    public function updateBio(Request $request, User $user)
    {
        $request->validate([
            'bio'    => 'nullable|string|max:3000',
            'domain' => 'nullable|string|max:255',
        ]);
        $user->update([
            'bio'    => $request->bio,
            'domain' => $request->domain,
        ]);
        return back()->with('success', 'Biographie mise à jour.');
    }

    public function destroy(User $user)
    {
        if ($user->isAdmin()) return back()->with('error', 'Impossible de supprimer un administrateur.');
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'Utilisateur supprimé.');
    }
}
