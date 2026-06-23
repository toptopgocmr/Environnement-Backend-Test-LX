<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{User, Book, Order, Royalty, Category, WithdrawalRequest};
use App\Models\PlatformSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Storage};

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('books')->orderBy('sort_order')->paginate(20);
        return view('admin.categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string',
            'icon'        => 'nullable|string|max:10',
            'color'       => 'nullable|string|max:10',
            'parent_id'   => 'nullable|exists:categories,id',
        ]);
        $data['slug'] = \Str::slug($data['name']);
        Category::create($data);
        return back()->with('success', 'Catégorie créée.');
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:100',
            'description' => 'nullable|string',
            'color'     => 'nullable|string|max:10',
            'icon'      => 'nullable|string|max:10',
            'is_active' => 'boolean',
        ]);
        $category->update($data);
        return back()->with('success', 'Catégorie mise à jour.');
    }

    public function destroy(Category $category)
    {
        if ($category->books()->exists()) return back()->with('error', 'Cette catégorie contient des livres.');
        $category->delete();
        return back()->with('success', 'Catégorie supprimée.');
    }
}
