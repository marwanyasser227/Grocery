<?php

namespace App\Jobs;

use App\Mail\InvoiceMail;
use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendInvoiceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    public function __construct(public readonly int $orderId)
    {
    }

    public function handle():void
    {
        $order = Order::query()
            ->with(['user', 'items.meal'])
            ->findOrFail($this->orderId);

        if (! $order->user?->email) {
            throw new \RuntimeException("Cannot send invoice for order {$order->id}: customer email is missing.");
        }

        $pdf = Pdf::loadView('invoices.show', ['order' => $order]);

        Mail::to($order->user?->email)->send(
            new InvoiceMail(
                "Thank you for your payment. Your invoice for order {$order->order_number} is attached.",
                $pdf->output()
            )
        );

        Log::info('Invoice sent successfully to ', [
            'order_id' => $order->id,
            'email' => $order->user->email,
            ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Failed sending invoice.', [
            'order_id' => $this->orderId,
            'error' => $exception->getMessage(),
        ]);
    }
}