<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Site;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class SiteContactController extends Controller
{
    public function show(string $siteSlug): JsonResponse
    {
        $site = Site::where('slug', $siteSlug)->where('is_active', true)->firstOrFail();

        $contact = $site->contact;

        if (!$contact) {
            return response()->json(['success' => true, 'data' => null]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'address'      => $contact->address,
                'phones'       => $contact->phones
                    ? array_map('trim', explode(',', $contact->phones))
                    : [],
                'email'        => $contact->email,
                'opening_time' => $contact->opening_time,
                'social_links' => collect($contact->social_links ?? [])->map(fn($item) => [
                    'label' => $item['label'] ?? null,
                    'url'   => $item['url'] ?? null,
                    'icon'  => isset($item['icon']) ? Storage::url($item['icon']) : null,
                ])->values(),
                'map_embed_url' => $contact->map_embed_url,
            ],
        ]);
    }
}
