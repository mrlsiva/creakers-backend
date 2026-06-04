<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Site;
use Illuminate\Http\JsonResponse;

class SiteContentController extends Controller
{
    public function index(string $siteSlug): JsonResponse
    {
        $site = Site::where('slug', $siteSlug)->where('is_active', true)->firstOrFail();

        $contents = $site->contents()
            ->where('is_active', true)
            ->orderBy('key')
            ->get(['key', 'title', 'body', 'updated_at']);

        return response()->json(['success' => true, 'data' => $contents]);
    }

    public function show(string $siteSlug, string $key): JsonResponse
    {
        $site = Site::where('slug', $siteSlug)->where('is_active', true)->firstOrFail();

        $content = $site->contents()
            ->where('key', $key)
            ->where('is_active', true)
            ->firstOrFail(['key', 'title', 'body', 'updated_at']);

        return response()->json(['success' => true, 'data' => $content]);
    }
}
