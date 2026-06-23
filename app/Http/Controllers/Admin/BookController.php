<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{User, Book, Order, Royalty, Category, WithdrawalRequest};
use App\Models\PlatformSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Storage};

class BookController extends Controller
{
    public function index(Request $request)
    {
        $books = Book::with(['author', 'category'])
            ->when($request->status,      fn($q) => $q->where('status', $request->status))
            ->when($request->search,      fn($q) => $q->where('title', 'LIKE', "%{$request->search}%"))
            ->when($request->category_id, fn($q) => $q->where('category_id', $request->category_id))
            ->latest()->paginate(20);

        $categories = Category::where('is_active', true)->orderBy('name')->get();
        return view('admin.books.index', compact('books', 'categories'));
    }

    public function show(Book $book)
    {
        $book->load(['author', 'category', 'reviews.user', 'tags']);
        $orderStats = [
            'total'   => $book->orders()->where('payment_status', 'paid')->count(),
            'revenue' => $book->orders()->where('payment_status', 'paid')->sum('amount'),
        ];
        return view('admin.books.show', compact('book', 'orderStats'));
    }

    public function approve(Book $book)
    {
        $book->update(['status' => 'published']);
        $book->author->notify(new \App\Notifications\BookApproved($book));
        return back()->with('success', "Livre « {$book->title} » publié.");
    }

    public function reject(Request $request, Book $book)
    {
        $request->validate(['reason' => 'required|string|max:500']);
        $book->update(['status' => 'rejected', 'rejection_reason' => $request->reason]);
        $book->author->notify(new \App\Notifications\BookRejected($book, $request->reason));
        return back()->with('success', "Livre rejeté. L'auteur a été notifié.");
    }

    public function toggleFeatured(Book $book)
    {
        $book->update(['is_featured' => !$book->is_featured]);
        return back()->with('success', 'Statut "mis en avant" modifié.');
    }

    public function updateCover(Request $request, Book $book)
    {
        $request->validate(['cover' => 'required|image|mimes:jpg,jpeg,png,webp|max:4096']);

        // Supprimer l'ancienne couverture si c'est un fichier storage (pas /covers/*.svg)
        if ($book->cover_image && !str_starts_with($book->cover_image, '/covers/')) {
            Storage::disk('public')->delete($book->cover_image);
        }

        $path = $request->file('cover')->store('covers', 'public');
        $book->update(['cover_image' => $path]);

        return back()->with('success', 'Couverture mise à jour.');
    }

    public function updateInfo(Request $request, Book $book)
    {
        $request->validate([
            'title'            => 'required|string|max:255',
            'description'      => 'nullable|string|max:5000',
            'price'            => 'nullable|numeric|min:0',
            'is_free'          => 'boolean',
            'publication_year' => 'nullable|integer|min:1800|max:2100',
            'publisher'        => 'nullable|string|max:255',
            'pages'            => 'nullable|integer|min:1',
        ]);

        $book->update([
            'title'            => $request->title,
            'description'      => $request->description,
            'price'            => $request->boolean('is_free') ? 0 : $request->price,
            'is_free'          => $request->boolean('is_free'),
            'publication_year' => $request->publication_year,
            'publisher'        => $request->publisher,
            'pages'            => $request->pages,
        ]);

        return back()->with('success', 'Informations du livre mises à jour.');
    }

    public function destroy(Book $book)
    {
        if ($book->cover_image)  Storage::disk('public')->delete($book->cover_image);
        if ($book->file_path)    Storage::disk('local')->delete($book->file_path);
        $book->delete();
        return redirect()->route('admin.books.index')->with('success', 'Livre supprimé.');
    }
}
