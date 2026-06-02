<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;

class EnquiryController extends Controller
{
    public function download(string $orderNumber)
    {
        $order = Order::with(['site', 'items'])->where('order_number', $orderNumber)->firstOrFail();

        $pdf = Pdf::loadView('enquiry.order', compact('order'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('enquiry-' . $order->order_number . '.pdf');
    }
}
