<?php

namespace App\Services\PaymentGateway;

use App\Contracts\PaymentGatewayInterface;
use Exception;
use Illuminate\Support\Facades\Log;
use Xendit\Configuration;
use Xendit\Invoice\InvoiceApi;

class XenditGateway implements PaymentGatewayInterface
{
    protected InvoiceApi $invoiceApi;

    public function __construct()
    {
        Configuration::setXenditKey(config('services.xendit.secret_key'));
        $this->invoiceApi = new InvoiceApi();
    }

    public function createPayment(string $externalId, int $amount, array $options = []): array
    {
        try {
            $invoiceRequest = [
                'external_id' => $externalId,
                'amount' => $amount,
                'description' => "Payment for {$externalId}",
                'currency' => 'IDR',
                'invoice_duration' => 3600,
            ];

            $invoice = $this->invoiceApi->createInvoice($invoiceRequest);

            return [
                'status' => 'pending',
                'invoice_url' => $invoice['invoice_url'] ?? null,
                'payment_reference' => $externalId,
                'meta' => $invoice,
            ];
        } catch (Exception $e) {
            Log::error("Xendit createPayment failed: " . $e->getMessage());
            throw new Exception("Xendit payment creation failed.");
        }
    }

    public function getPaymentReferenceFromPayload(array $payload): string
    {
        return $payload['external_id'] ?? '';
    }

    public function getStatusFromPayload(array $payload): string
    {
        return strtolower($payload['status'] ?? 'pending');
    }
}