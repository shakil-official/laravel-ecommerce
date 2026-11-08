<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Subcategory;
use App\Support\Search;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * Public storefront: product listing grouped by subcategory + product details.
 */
class ShopController extends Controller
{
    /** All products, grouped into one section per subcategory (optional ?category=slug filter). */
    public function index(Request $request): View
    {
        return $this->listing($request, (string) $request->query('category', ''));
    }

    /** One category as an SEO friendly page: /category/{slug}. */
    public function category(Request $request, Category $category): View
    {
        return $this->listing($request, $category->slug);
    }

    /** Shared by index() and category(): sections of products, one per subcategory. */
    private function listing(Request $request, string $categorySlug): View
    {
        $search = trim((string) $request->query('q', ''));

        $matchingProducts = fn ($query) => $query
            ->when($search !== '', fn ($q) => $q->whereRaw("products.name like ? escape '!'", [Search::like($search)]));

        $sections = Subcategory::query()
            ->with(['category', 'products' => fn ($query) => $matchingProducts($query)->latest('id')])
            ->whereHas('products', $matchingProducts)
            ->when($categorySlug !== '', fn ($query) => $query->whereHas('category', fn ($q) => $q->where('slug', $categorySlug)))
            ->orderBy('category_id')
            ->orderBy('name')
            ->paginate(config('shop.listing_per_page'))
            ->withQueryString();

        return view('shop.index', [
            'sections' => $sections,
            'categories' => Category::has('products')->orderBy('name')->get(['id', 'name', 'slug']),
            'search' => $search,
            'activeCategory' => $categorySlug,
            'currentCategory' => $categorySlug !== '' ? Category::where('slug', $categorySlug)->first() : null,
            'stats' => [
                'products' => Product::count(),
                'categories' => Category::count(),
            ],
        ]);
    }

    /** All products of one subcategory. */
    public function subcategory(Subcategory $subcategory): View
    {
        $subcategory->load('category');

        return view('shop.subcategory', [
            'subcategory' => $subcategory,
            'products' => $subcategory->products()->latest('id')->paginate(12),
        ]);
    }

    /** Product details page (SEO friendly URL: /product/{slug}). */
    public function show(Product $product): View
    {
        $product->load(['category', 'subcategory']);

        $related = Product::query()
            ->where('subcategory_id', $product->subcategory_id)
            ->whereKeyNot($product->getKey())
            ->latest('id')
            ->limit(4)
            ->get();

        return view('shop.show', compact('product', 'related'));
    }
}
