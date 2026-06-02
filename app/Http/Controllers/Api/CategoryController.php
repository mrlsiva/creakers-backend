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

        $categories = Category::with('children')
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn($cat) => $this->formatCategory($cat));

        return response()->json([
            'success' => true,
            'site' => ['name' => $site->name, 'slug' => $site->slug],
            'data' => $categories,
        ]);
    }

    private function formatCategory(Category $category): array
    {
        return [
            'id' => $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
            'image' => $category->image ? asset('storage/' . $category->image) : null,
            'children' => $category->children
                ->where('is_active', true)
                ->sortBy('sort_order')
                ->values()
                ->map(fn($child) => $this->formatCategory($child)),
        ];
    }
}
