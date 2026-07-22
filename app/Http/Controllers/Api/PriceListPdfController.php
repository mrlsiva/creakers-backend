<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Site;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class PriceListPdfController extends Controller
{
    public function download(string $siteSlug): Response
    {
        $site = Site::where('slug', $siteSlug)->where('is_active', true)->firstOrFail();

        $categories = Category::where('is_active', true)
            ->with(['products' => function ($query) use ($site) {
                $query->where('is_active', true)
                    ->with(['prices' => fn($q) => $q->where('site_id', $site->id)])
                    ->whereHas('prices', fn($q) => $q->where('site_id', $site->id))
                    ->orderBy('sort_order')
                    ->orderBy('name');
            }])
            ->orderBy('sort_order')
            ->get()
            ->filter(fn($category) => $category->products->isNotEmpty())
            ->values();

        $logoPath = $this->resolveLogoForPdf($site->logo);

        $pdf = Pdf::loadView('pdf.price-list', [
            'site'        => $site,
            'categories'  => $categories,
            'logoPath'    => $logoPath,
            'generatedAt' => now()->format('d M Y'),
        ])->setPaper('a4', 'portrait');

        $filename = 'price-list-' . $site->slug . '-' . now()->format('Y-m-d') . '.pdf';

        return $pdf->download($filename)->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }

    /**
     * dompdf/php-svg-lib can't render Figma-style SVGs that fill a <rect> via
     * a <pattern> referencing an embedded base64 <image> (no visible output).
     * When that shape is detected, pull the embedded raster image out and
     * hand dompdf a data URI instead of the raw SVG path.
     */
    private function resolveLogoForPdf(?string $logo): ?string
    {
        if (!$logo) {
            return null;
        }

        $path = public_path('storage/' . $logo);
        if (!file_exists($path)) {
            return null;
        }

        if (strtolower(pathinfo($path, PATHINFO_EXTENSION)) !== 'svg') {
            return $path;
        }

        $svg = file_get_contents($path);
        if ($svg !== false && preg_match('/xlink:href="data:(image\/[a-zA-Z+]+);base64,([^"]+)"/', $svg, $m)) {
            return 'data:' . $m[1] . ';base64,' . $m[2];
        }

        return null;
    }
}
