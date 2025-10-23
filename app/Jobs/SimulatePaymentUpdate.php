<?php

namespace App\Jobs;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SimulatePaymentUpdate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $paymentReference;
    protected $status;

    public function __construct(string $paymentReference, string $status)
    {
        $this->paymentReference = $paymentReference;
        $this->status = $status;
    }

    public function handle()
    {
        Log::info("Job started for {$this->paymentReference} with status {$this->status}");

        // Simulasi delay acak
        sleep(rand(0, 2));

        // Race condition muncul di sini kalau dua job update bersamaan
        $payment = Payment::where('payment_reference', $this->paymentReference)->first();
        
        if (!$payment) {
            Log::warning("Payment not found: {$this->paymentReference}");
            return;
        }

        $payment->update([
            'status' => $this->status,
        ]);

        // Update order jika status success
        if (strtolower($this->status) === 'success') {
            $payment->order()->update([
                'status' => 'paid',
            ]);
        }

        Log::info("Job finished for {$this->paymentReference} with status {$this->status}");
    }
}