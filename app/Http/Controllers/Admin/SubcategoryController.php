<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubcategoryRequest;
use App\Models\Category;
use App\Models\Subcategory;
use App\Support\Search;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubcategoryController extends Controller
{
    /** List subcategories (filter by category / name, paginated). */
    public function index(Request $request): View
    {
        $subcategories = Subcategory::query()
            ->with('category')
            ->withCount('products')
            ->when($request->filled('category_id'), fn ($query) => $query->where('category_id', $request->integer('category_id')))
            ->when($request->filled('q'), fn ($query) => $query->whereRaw("subcategories.name like ? escape '!'", [Search::like((string) $request->string('q'))]))
            ->latest('id')
            ->paginate($this->perPage($request))
            ->withQueryString();

        return view('admin.subcategories.index', [
            'subcategories' => $subcategories,
            'categories' => Category::orderBy('name')->get(['id', 'name']),
        ]);
    }

    /** Show the "new subcategory" form (optionally preselecting ?category_id=). */
    public function create(Request $request): View
    {
        $subcategory = new Subcategory(['category_id' => $request->integer('category_id') ?: null]);

        return view('admin.subcategories.create', [
            'subcategory' => $subcategory,
            'categories' => Category::orderBy('name')->get(['id', 'name']),
        ]);
    }

    /** Persist a new subcategory. */
    public function store(SubcategoryRequest $request): RedirectResponse
    {
        $subcategory = Subcategory::create($request->validated());

        return redirect()
            ->route('admin.subcategories.index')
            ->with('success', "Subcategory “{$subcategory->name}” was created.");
    }

    /** Show a subcategory with its products. */
    public function show(Request $request, Subcategory $subcategory): View
    {
        $subcategory->load('category');
        $products = $subcategory->products()
            ->latest('id')
            ->paginate($this->perPage($request))
            ->withQueryString();

        return view('admin.subcategories.show', compact('subcategory', 'products'));
    }

    /** Show the "edit subcategory" form. */
    public function edit(Subcategory $subcategory): View
    {
        return view('admin.subcategories.edit', [
            'subcategory' => $subcategory,
            'categories' => Category::orderBy('name')->get(['id', 'name']),
        ]);
    }

    /** Update a subcategory (it may also be moved to another category). */
    public function update(SubcategoryRequest $request, Subcategory $subcategory): RedirectResponse
    {
        $subcategory->update($request->validated());

        // Products must always point at the category their subcategory lives in.
        if ($subcategory->wasChanged('category_id')) {
            $subcategory->products()->update(['category_id' => $subcategory->category_id]);
        }

        return redirect()
            ->route('admin.subcategories.index')
            ->with('success', "Subcategory “{$subcategory->name}” was updated.");
    }

    /** Delete a subcategory together with its products. */
    public function destroy(Subcategory $subcategory): RedirectResponse
    {
        $name = $subcategory->name;
        // All-or-nothing: if anything fails, nothing is deleted.
        DB::transaction(fn () => $subcategory->delete());

        return redirect()
            ->route('admin.subcategories.index')
            ->with('success', "Subcategory “{$name}” and its products were deleted.");
    }
}
