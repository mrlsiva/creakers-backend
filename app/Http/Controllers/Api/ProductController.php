<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Site;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request, string $siteSlug): JsonResponse
    {
        $site = Site::where('slug', $siteSlug)->where('is_active', true)->firstOrFail();

        $query = Product::with(['category', 'prices' => fn($q) => $q->where('site_id', $site->id)])
            ->where('is_active', true)
            ->whereHas('prices', fn($q) => $q->where('site_id', $site->id));

        if ($request->filled('category')) {
            $query->whereHas('category', fn($q) => $q->where('slug', $request->category));
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $perPage = min((int) $request->input('per_page', 20), 100);
        $paginated = $query->orderBy('sort_order')->orderBy('name')->paginate($perPage);

        return response()->json([
            'success' => true,
            'site' => ['name' => $site->name, 'slug' => $site->slug],
            'data' => $paginated->getCollection()->map(fn($p) => $this->formatProduct($p, $site->id)),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
            ],
        ]);
    }

    public function show(string $siteSlug, string $slug): JsonResponse
    {
        $site = Site::where('slug', $siteSlug)->where('is_active', true)->firstOrFail();

        $product = Product::with(['category', 'prices' => fn($q) => $q->where('site_id', $site->id)])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $this->formatProduct($product, $site->id),
        ]);
    }

    public function byCategory(Request $request, string $siteSlug, string $categorySlug): JsonResponse
    {
        $site = Site::where('slug', $siteSlug)->where('is_active', true)->firstOrFail();
        $category = Category::where('slug', $categorySlug)->where('is_active', true)->firstOrFail();

        $categoryIds = collect([$category->id]);
        $childIds = Category::where('parent_id', $category->id)->pluck('id');
        $categoryIds = $categoryIds->merge($childIds);

        $perPage = min((int) $request->input('per_page', 20), 100);
        $paginated = Product::with(['category', 'prices' => fn($q) => $q->where('site_id', $site->id)])
            ->whereIn('category_id', $categoryIds)
            ->where('is_active', true)
            ->whereHas('prices', fn($q) => $q->where('site_id', $site->id))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'category' => ['name' => $category->name, 'slug' => $category->slug],
            'data' => $paginated->getCollection()->map(fn($p) => $this->formatProduct($p, $site->id)),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
            ],
        ]);
    }

    private function formatProduct(Product $product, int $siteId): array
    {
        $price = $product->prices->first();

        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'description' => $product->description,
            'image' => $product->image ? asset('storage/' . $product->image) : null,
            'gallery' => collect($product->gallery ?? [])->map(fn($img) => asset('storage/' . $img)),
            'unit' => $product->unit,
            'category' => [
                'id' => $product->category->id,
                'name' => $product->category->name,
                'slug' => $product->category->slug,
            ],
            'pricing' => $price ? [
                'mrp' => (float) $price->mrp,
                'discount_percent' => (float) $price->discount_percent,
                'our_price' => (float) $price->our_price,
                'savings' => round((float) $price->mrp - (float) $price->our_price, 2),
            ] : null,
        ];
    }
}
