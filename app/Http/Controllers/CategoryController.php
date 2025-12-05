<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Display all categories (public)
     */
    public function index()
    {
        $categories = Category::where('is_active', true)
            ->whereNull('parent_id')
            ->with(['children' => function($query) {
                $query->where('is_active', true)->orderBy('order');
            }])
            ->orderBy('order')
            ->get();
        
        return view('categories.index', compact('categories'));
    }

    /**
     * Display category with listings (public)
     */
    public function show($slug)
    {
        $category = Category::where('slug', $slug)->where('is_active', true)->firstOrFail();
        
        $listings = Listing::where('is_active', true)
            ->where('category_id', $category->id)
            ->with(['vendor', 'images'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        $subcategories = Category::where('parent_id', $category->id)
            ->where('is_active', true)
            ->orderBy('order')
            ->get();
        
        return view('categories.show', compact('category', 'listings', 'subcategories'));
    }

    /**
     * Display admin categories index
     */
    public function adminIndex()
    {
        $categories = Category::withCount('listings')
            ->with('parent')
            ->orderBy('order')
            ->paginate(20);
        
        $parentCategories = Category::whereNull('parent_id')->get();
        
        return view('admin.categories.index', compact('categories', 'parentCategories'));
    }

    /**
     * Show create category form
     */
    public function create()
    {
        $parentCategories = Category::whereNull('parent_id')->get();
        return view('admin.categories.create', compact('parentCategories'));
    }

    /**
     * Store new category
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:50',
            'order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['order'] = $validated['order'] ?? 0;
        $validated['is_active'] = $validated['is_active'] ?? true;

        // Check if slug exists
        if (Category::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = $validated['slug'] . '-' . time();
        }

        Category::create($validated);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category created successfully.');
    }

    /**
     * Show edit category form
     */
    public function edit(Category $category)
    {
        $parentCategories = Category::whereNull('parent_id')
            ->where('id', '!=', $category->id)
            ->get();
        
        return view('admin.categories.edit', compact('category', 'parentCategories'));
    }

    /**
     * Update category
     */
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:50',
            'order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        
        // Check if slug exists (excluding current category)
        if (Category::where('slug', $validated['slug'])
            ->where('id', '!=', $category->id)
            ->exists()) {
            $validated['slug'] = $validated['slug'] . '-' . time();
        }

        $category->update($validated);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category updated successfully.');
    }

    /**
     * Toggle category status
     */
    public function toggle(Category $category)
    {
        $category->update(['is_active' => !$category->is_active]);
        
        return back()->with('success', 'Category status updated.');
    }

    /**
     * Delete category
     */
    public function destroy(Category $category)
    {
        // Check if category has listings
        if ($category->listings()->count() > 0) {
            return back()->with('error', 'Cannot delete category with listings. Move listings first.');
        }

        // Update child categories
        Category::where('parent_id', $category->id)->update(['parent_id' => null]);
        
        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category deleted successfully.');
    }
}