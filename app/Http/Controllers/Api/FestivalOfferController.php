<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Site;
use Illuminate\Http\JsonResponse;

class FestivalOfferController extends Controller
{
    public function show(string $siteSlug): JsonResponse
    {
        $site = Site::where('slug', $siteSlug)->where('is_active', true)->firstOrFail();

        $offer = $site->festivalOffer;

        if (!$offer || !$offer->is_active) {
            return response()->json(['success' => true, 'data' => null]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'title'                  => $offer->title,
                'sub_title'              => $offer->sub_title,
                'ends_at'                => $offer->ends_at?->toIso8601String(),
                'button_label'           => $offer->button_label,
                'button_url'             => $offer->button_url,
                'button_open_in_new_tab' => (bool) $offer->button_open_in_new_tab,
            ],
        ]);
    }
}
