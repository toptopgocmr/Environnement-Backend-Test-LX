<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\{User, Book, Order, Category, Review, Royalty, ReadingProgress};
use App\Services\PaymentService;
use Illuminate\Http\{Request, JsonResponse};
use Illuminate\Support\Facades\{Auth, Hash, Storage};
use Tymon\JWTAuth\Facades\JWTAuth;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $cats = Category::where('is_active', true)
            ->withCount('books')
            ->orderBy('sort_order')
            ->get();
        return response()->json(['success' => true, 'data' => $cats]);
    }
}
