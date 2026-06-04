<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClientLogo;
use App\Models\Site;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class ClientLogoController extends Controller
{
    public function index(string $siteSlug): JsonResponse
    {
        $site = Site::where('slug', $siteSlug)->where('is_active', true)->firstOrFail();

        $logos = ClientLogo::where('site_id', $site->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get(['name', 'logo'])
            ->map(fn($item) => [
                'name' => $item->name,
                'logo' => Storage::url($item->logo),
            ]);

        return response()->json(['success' => true, 'data' => $logos]);
    }
}
