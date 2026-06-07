<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Site;
use Illuminate\Http\JsonResponse;

class SiteController extends Controller
{
    public function index(): JsonResponse
    {
        $sites = Site::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn($site) => [
                'name'     => $site->name,
                'title'    => $site->title,
                'slug'     => $site->slug,
                'logo'     => $site->logo ? asset('storage/' . $site->logo) : null,
                'nav_icon' => $site->nav_icon ? asset('storage/' . $site->nav_icon) : null,
            ]);

        return response()->json([
            'success' => true,
            'data'    => $sites,
        ]);
    }

    public function show(string $siteSlug): JsonResponse
    {
        $site = Site::where('slug', $siteSlug)->where('is_active', true)->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => [
                'name'     => $site->name,
                'title'    => $site->title,
                'slug'     => $site->slug,
                'logo'     => $site->logo ? asset('storage/' . $site->logo) : null,
                'nav_icon' => $site->nav_icon ? asset('storage/' . $site->nav_icon) : null,
            ],
        ]);
    }
}
