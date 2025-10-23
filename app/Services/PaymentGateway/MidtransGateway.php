<?php

namespace App\Services\PaymentGateway;

use App\Contracts\PaymentGatewayInterface;
use Midtrans\Snap;
use Midtrans\Config;
use Exception;
use Illuminate\Support\Facades\Log;

class MidtransGateway implements PaymentGatewayInterface
{
    public function __construct()
    {
        Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        Config::$isProduction = env('MIDTRANS_IS_PRODUCTION', false);
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    /**
     * Create a payment
     */
    public function createPayment(string $externalId, int $amount, array $options = []): array
    {
        try {
            $params = [
                'transaction_details' => [
                    'order_id' => $externalId,
                    'gross_amount' => $amount,
                ],
                'customer_details' => [
                    'first_name' => $options['customer_name'] ?? 'John',
                    'last_name' => $options['customer_name'] ?? 'Doe',
                    'phone' =>  $options['phone'] ?? '0811111',
                    'email' => $options['customer_email'] ?? 'customer@example.com',
                ],
                'expiry' => $options['expiry'] ?? [
                    'start_time' => now()->format('Y-m-d H:i:s O'),
                    'unit' => 'minute',
                    'duration' => 60
                ],
            ];

            $snap = Snap::createTransaction($params);

            Log::info('Midtrans Snap response', (array)$snap);

            return [
                'payment_url' => $snap->redirect_url,
                'payment_reference' => $externalId,
                'status' => 'pending',
                'midtrans_response' => $snap,
            ];
        } catch (Exception $e) {
            Log::error("Midtrans createPayment failed: {$e->getMessage()}");
            throw new Exception("Midtrans payment creation failed.");
        }
    }


    /**
     * Get payment_reference from webhook payload
     */
    public function getPaymentReferenceFromPayload(array $payload): string
    {
        return $payload['order_id'] ?? '';
    }

    /**
     * Get status from webhook payload
     */
    public function getStatusFromPayload(array $payload): string
    {
        return strtolower($payload['transaction_status'] ?? 'pending');
    }
}