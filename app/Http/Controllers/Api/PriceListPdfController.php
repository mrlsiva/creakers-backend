<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PriceListPdf;
use App\Models\Site;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class PriceListPdfController extends Controller
{
    public function index(string $siteSlug): JsonResponse
    {
        $site = Site::where('slug', $siteSlug)->where('is_active', true)->firstOrFail();

        $pdfs = PriceListPdf::where('site_id', $site->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get(['title', 'file', 'updated_at'])
            ->map(fn($pdf) => [
                'title'      => $pdf->title,
                'url'        => Storage::url($pdf->file),
                'updated_at' => $pdf->updated_at->format('d M Y'),
            ]);

        return response()->json(['success' => true, 'data' => $pdfs]);
    }
}
