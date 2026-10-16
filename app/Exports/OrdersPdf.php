<?php

namespace App\Exports;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * PDF counterpart of OrdersExport.
 *
 * - summary: one table listing every order (landscape)
 * - items:   one enquiry page per order, same layout as the customer enquiry PDF
 */
class OrdersPdf
{
    public static function download(Builder $query, bool $withItems, string $filename): StreamedResponse
    {
        if ($withItems) {
            $pdf = Pdf::loadView('enquiry.order', [
                'orders' => $query->with(['site', 'items'])->get(),
            ])->setPaper('a4', 'portrait');
        } else {
            $pdf = Pdf::loadView('pdf.orders-summary', [
                'orders'   => $query->with('site')->withCount('items')->withSum('items', 'quantity')->get(),
                'statuses' => Order::statuses(),
            ])->setPaper('a4', 'landscape');
        }

        // Livewire can only send streamed/file responses as downloads
        return response()->streamDownload(
            fn() => print($pdf->output()),
            $filename,
            ['Content-Type' => 'application/pdf']
        );
    }
}
