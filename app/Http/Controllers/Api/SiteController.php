<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Site;
use Illuminate\Http\JsonResponse;

class SiteController extends Controller
{
    public function show(string $siteSlug): JsonResponse
    {
        $site = Site::where('slug', $siteSlug)->where('is_active', true)->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => [
                'name' => $site->name,
                'slug' => $site->slug,
                'logo' => $site->logo ? asset('storage/' . $site->logo) : null,
            ],
        ]);
    }
}
