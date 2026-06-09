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

        $phones = $contact->phones
            ? array_values(array_filter(array_map('trim', explode(',', $contact->phones))))
            : [];

        return response()->json([
            'success' => true,
            'data' => [
                'phone'        => $phones[0] ?? null,
                'whatsapp'     => $contact->whatsapp ?: ($phones[0] ?? null),
                'email'        => $contact->email,
                'address'      => $contact->address,
                'opening_time' => $contact->opening_time,
                'map_embed_url' => $contact->map_embed_url,
                'social_links' => collect($contact->social_links ?? [])->map(fn($item) => [
                    'label' => $item['label'] ?? null,
                    'url'   => $item['url'] ?? null,
                    'icon'  => isset($item['icon']) ? Storage::url($item['icon']) : null,
                ])->values(),
                'phones'       => $phones,
            ],
        ]);
    }
}
