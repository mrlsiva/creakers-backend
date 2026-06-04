<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SafetyTip;
use App\Models\Site;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class SafetyTipController extends Controller
{
    public function index(string $siteSlug): JsonResponse
    {
        $site = Site::where('slug', $siteSlug)->where('is_active', true)->firstOrFail();

        $items = SafetyTip::where('site_id', $site->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $images = $items->where('type', 'image')->map(fn($item) => [
            'image' => Storage::url($item->image),
        ])->values();

        $tips = $items->where('type', 'tip')->map(fn($item) => [
            'title'       => $item->title,
            'description' => $item->description,
        ])->values();

        return response()->json([
            'success' => true,
            'data'    => [
                'images' => $images,
                'tips'   => $tips,
            ],
        ]);
    }
}
