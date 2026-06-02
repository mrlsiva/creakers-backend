<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Site;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function index(string $siteSlug): JsonResponse
    {
        $site = Site::where('slug', $siteSlug)->where('is_active', true)->firstOrFail();

        $fallback = $site->logo
            ? asset('storage/' . $site->logo)
            : asset('images/default-product.svg');

        $categories = Category::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn($cat) => $this->formatCategory($cat, $fallback));

        return response()->json([
            'success' => true,
            'site' => ['name' => $site->name, 'slug' => $site->slug],
            'data' => $categories,
        ]);
    }

    private function formatCategory(Category $category, string $fallback): array
    {
        return [
            'id' => $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
            'image' => $category->image ? asset('storage/' . $category->image) : $fallback,
        ];
    }
}
