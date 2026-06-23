<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\{User, Book, Order, Category, Review, Royalty, ReadingProgress};
use App\Services\PaymentService;
use Illuminate\Http\{Request, JsonResponse};
use Illuminate\Support\Facades\{Auth, Hash, Storage};
use Tymon\JWTAuth\Facades\JWTAuth;

// LIBRARY / WISHLIST / PROGRESS
class LibraryController extends Controller
{
    public function myLibrary(): JsonResponse
    {
        $orders = Auth::user()->orders()
            ->where('payment_status', 'paid')
            ->with(['book:id,title,cover_image,author_id,pages,language,format', 'book.author:id,name'])
            ->latest()->paginate(20);
        return response()->json(['success' => true, 'data' => $orders]);
    }

    public function wishlist(): JsonResponse
    {
        $books = Auth::user()->wishlists()
            ->with(['author:id,name', 'category:id,name'])
            ->paginate(20);
        return response()->json(['success' => true, 'data' => $books]);
    }

    public function toggleWishlist(Book $book): JsonResponse
    {
        $user = Auth::user();
        if ($user->wishlists()->where('book_id', $book->id)->exists()) {
            $user->wishlists()->detach($book->id);
            $inWishlist = false;
            $msg = 'Retiré de la liste de souhaits.';
        } else {
            $user->wishlists()->attach($book->id);
            $inWishlist = true;
            $msg = 'Ajouté à la liste de souhaits.';
        }
        return response()->json(['success' => true, 'message' => $msg, 'data' => ['in_wishlist' => $inWishlist]]);
    }

    public function removeWishlist(Book $book): JsonResponse
    {
        Auth::user()->wishlists()->detach($book->id);
        return response()->json(['success' => true, 'message' => 'Retiré de la liste de souhaits.', 'data' => ['in_wishlist' => false]]);
    }

    public function checkWishlist(Book $book): JsonResponse
    {
        $inWishlist = Auth::user()->wishlists()->where('book_id', $book->id)->exists();
        return response()->json(['success' => true, 'data' => ['in_wishlist' => $inWishlist]]);
    }

    public function inLibrary(Book $book): JsonResponse
    {
        $inLibrary = Auth::user()->orders()
            ->where('book_id', $book->id)
            ->where('payment_status', 'paid')
            ->exists();
        return response()->json(['success' => true, 'data' => ['in_library' => $inLibrary]]);
    }

    public function updateProgress(Request $request, Book $book): JsonResponse
    {
        $data = $request->validate([
            'current_page' => 'required|integer|min:1',
            'total_pages'  => 'required|integer|min:1',
        ]);
        $percentage = round(($data['current_page'] / $data['total_pages']) * 100, 2);

        ReadingProgress::updateOrCreate(
            ['user_id' => Auth::id(), 'book_id' => $book->id],
            array_merge($data, ['percentage' => $percentage, 'last_read_at' => now()])
        );
        return response()->json(['success' => true, 'data' => ['percentage' => $percentage]]);
    }
}
