<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CustomerOrderStatusUpdate extends Mailable
{
    use Queueable, SerializesModels;

    public string $statusName;
    public ?string $previousStatusName;

    public function __construct(public Order $order, ?string $previousStatus = null)
    {
        $statuses = Order::statuses();

        $this->statusName = $statuses[$order->status] ?? ucwords(str_replace('_', ' ', $order->status));
        $this->previousStatusName = $previousStatus
            ? ($statuses[$previousStatus] ?? ucwords(str_replace('_', ' ', $previousStatus)))
            : null;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Order #' . $this->order->order_number . ' — ' . $this->statusName,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.customer-order-status-update',
        );
    }
}
