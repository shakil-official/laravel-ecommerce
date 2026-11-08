<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use App\Support\Search;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    /** List categories (with search + pagination). */
    public function index(Request $request): View
    {
        $categories = Category::query()
            ->withCount(['subcategories', 'products'])
            ->when($request->filled('q'), fn ($query) => $query->whereRaw("categories.name like ? escape '!'", [Search::like((string) $request->string('q'))]))
            ->latest('id')
            ->paginate($this->perPage($request))
            ->withQueryString();

        return view('admin.categories.index', compact('categories'));
    }

    /** Show the "new category" form. */
    public function create(): View
    {
        return view('admin.categories.create', ['category' => new Category]);
    }

    /** Persist a new category. */
    public function store(CategoryRequest $request): RedirectResponse
    {
        $category = Category::create($request->validated());

        return redirect()
            ->route('admin.categories.index')
            ->with('success', "Category “{$category->name}” was created.");
    }

    /** Show a category with its subcategories. */
    public function show(Request $request, Category $category): View
    {
        $category->loadCount('products');
        $subcategories = $category->subcategories()
            ->withCount('products')
            ->orderBy('name')
            ->paginate($this->perPage($request))
            ->withQueryString();

        return view('admin.categories.show', compact('category', 'subcategories'));
    }

    /** Show the "edit category" form. */
    public function edit(Category $category): View
    {
        return view('admin.categories.edit', compact('category'));
    }

    /** Update a category. */
    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $category->update($request->validated());

        return redirect()
            ->route('admin.categories.index')
            ->with('success', "Category “{$category->name}” was updated.");
    }

    /** Delete a category together with its subcategories and products. */
    public function destroy(Category $category): RedirectResponse
    {
        $name = $category->name;
        // All-or-nothing: if anything fails, nothing is deleted.
        DB::transaction(fn () => $category->delete());

        return redirect()
            ->route('admin.categories.index')
            ->with('success', "Category “{$name}” and everything inside it was deleted.");
    }
}
