<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Models\SiteContent;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class SiteContentController extends Controller
{
    public function index(string $siteSlug): JsonResponse
    {
        $site = Site::where('slug', $siteSlug)->where('is_active', true)->firstOrFail();

        $contents = $site->contents()
            ->where('is_active', true)
            ->orderBy('key')
            ->get()
            ->map(fn(SiteContent $content) => $this->transform($content));

        return response()->json(['success' => true, 'data' => $contents]);
    }

    public function show(string $siteSlug, string $key): JsonResponse
    {
        $site = Site::where('slug', $siteSlug)->where('is_active', true)->firstOrFail();

        $content = $site->contents()
            ->where('key', $key)
            ->where('is_active', true)
            ->firstOrFail();

        return response()->json(['success' => true, 'data' => $this->transform($content)]);
    }

    private function transform(SiteContent $content): array
    {
        return [
            'key'                    => $content->key,
            'title'                  => $content->title,
            'tag'                    => $content->tag,
            'body'                   => $content->body,
            'image'                  => $content->image ? Storage::url($content->image) : null,
            'features'               => collect($content->features ?? [])->map(fn($item) => [
                'icon'     => $item['icon'] ?? null,
                'title'    => $item['title'] ?? null,
                'subtitle' => $item['subtitle'] ?? null,
            ])->values(),
            'button_label'           => $content->button_label,
            'button_url'             => $content->button_url,
            'button_open_in_new_tab' => (bool) $content->button_open_in_new_tab,
            'updated_at'             => $content->updated_at,
        ];
    }
}
