<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrderStep;
use App\Models\Site;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class OrderStepController extends Controller
{
    public function index(string $siteSlug): JsonResponse
    {
        $site = Site::where('slug', $siteSlug)->where('is_active', true)->firstOrFail();

        $steps = OrderStep::where('site_id', $site->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get(['sort_order', 'title', 'description', 'icon'])
            ->map(fn($step) => [
                'step'        => $step->sort_order,
                'title'       => $step->title,
                'description' => $step->description,
                'icon'        => $step->icon ? Storage::url($step->icon) : null,
            ]);

        return response()->json(['success' => true, 'data' => $steps]);
    }
}
