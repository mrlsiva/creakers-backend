<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Models\Site;
use Illuminate\Http\JsonResponse;

class FaqController extends Controller
{
    public function index(string $siteSlug): JsonResponse
    {
        $site = Site::where('slug', $siteSlug)->where('is_active', true)->firstOrFail();

        $faqs = Faq::where('site_id', $site->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get(['question', 'answer']);

        return response()->json([
            'success' => true,
            'data' => $faqs,
        ]);
    }
}
