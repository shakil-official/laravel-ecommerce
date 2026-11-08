<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\Subcategory;
use App\Services\ProductImageService;
use App\Support\Search;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Throwable;

class ProductController extends Controller
{
    /** The image service handles every upload / replace / delete on disk. */
    public function __construct(private readonly ProductImageService $images) {}

    /** List products (filter by category / subcategory / name, paginated). */
    public function index(Request $request): View
    {
        $products = Product::query()
            ->with(['category', 'subcategory'])
            ->when($request->filled('category_id'), fn ($query) => $query->where('category_id', $request->integer('category_id')))
            ->when($request->filled('subcategory_id'), fn ($query) => $query->where('subcategory_id', $request->integer('subcategory_id')))
            ->when($request->filled('q'), fn ($query) => $query->whereRaw("products.name like ? escape '!'", [Search::like((string) $request->string('q'))]))
            ->latest('id')
            ->paginate($this->perPage($request))
            ->withQueryString();

        return view('admin.products.index', [
            'products' => $products,
            'categories' => Category::orderBy('name')->get(['id', 'name']),
        ]);
    }

    /** Show the "new product" form. */
    public function create(Request $request): View
    {
        $product = new Product([
            'category_id' => $request->integer('category_id') ?: null,
            'subcategory_id' => $request->integer('subcategory_id') ?: null,
        ]);

        return view('admin.products.create', $this->formData($product));
    }

    /** Persist a new product and store its image. */
    public function store(ProductRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['image'] = $this->images->store($request->file('image'));

        try {
            $product = Product::create($data);
        } catch (Throwable $e) {
            // Do not leave an orphan file behind when the database insert fails.
            $this->images->delete($data['image']);

            throw $e;
        }

        return redirect()
            ->route('admin.products.index')
            ->with('success', "Product “{$product->name}” was created.");
    }

    /** Show product details inside the admin area. */
    public function show(Product $product): View
    {
        $product->load(['category', 'subcategory']);

        return view('admin.products.show', compact('product'));
    }

    /** Show the "edit product" form. */
    public function edit(Product $product): View
    {
        return view('admin.products.edit', $this->formData($product));
    }

    /** Update a product; the image is only replaced when a new file is uploaded. */
    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $data = Arr::except($request->validated(), ['image']);
        $oldImage = $product->image;
        $newImage = null;

        if ($request->hasFile('image')) {
            $newImage = $this->images->store($request->file('image'));
            $data['image'] = $newImage;
        }

        try {
            $product->update($data);
        } catch (Throwable $e) {
            // The update failed: drop the new file and keep the old one.
            $this->images->delete($newImage);

            throw $e;
        }

        // Only after the database points at the new file is the old one removed.
        if ($newImage !== null) {
            $this->images->delete($oldImage);
        }

        return redirect()
            ->route('admin.products.index')
            ->with('success', "Product “{$product->name}” was updated.");
    }

    /** Delete a product (its image file is removed by the model). */
    public function destroy(Product $product): RedirectResponse
    {
        $name = $product->name;
        $product->delete();

        return redirect()
            ->route('admin.products.index')
            ->with('success', "Product “{$name}” was deleted.");
    }

    /**
     * Data shared by the create and edit forms.
     *
     * @return array<string, mixed>
     */
    private function formData(Product $product): array
    {
        return [
            'product' => $product,
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            // Used by the dependent dropdown (category -> subcategory).
            'subcategories' => Subcategory::orderBy('name')->get(['id', 'category_id', 'name']),
        ];
    }
}
