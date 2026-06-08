<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Site;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class HomeBannerController extends Controller
{
    public function show(string $siteSlug): JsonResponse
    {
        $site = Site::where('slug', $siteSlug)->where('is_active', true)->firstOrFail();

        $banner = $site->homeBanner;

        if (!$banner) {
            return response()->json(['success' => true, 'data' => null]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'image'                 => $banner->image ? Storage::url($banner->image) : null,
                'mobile_image'          => $banner->mobile_image ? Storage::url($banner->mobile_image) : null,
                'title'                 => $banner->title,
                'second_title'          => $banner->second_title,
                'description'           => $banner->description,
                'top_small_description' => $banner->top_small_description,
                'buttons'               => collect($banner->buttons ?? [])->map(fn($item) => [
                    'label'           => $item['label'] ?? null,
                    'url'             => $item['url'] ?? null,
                    'open_in_new_tab' => (bool) ($item['open_in_new_tab'] ?? false),
                ])->values(),
            ],
        ]);
    }
}
