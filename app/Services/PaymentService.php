<?php

namespace App\Services;

use App\Models\Order;
use App\Repositories\PaymentRepository;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentService
{
    protected PaymentRepository $paymentRepo;
    protected array $gateways;

    public function __construct(PaymentRepository $paymentRepo, array $gateways)
    {
        $this->paymentRepo = $paymentRepo;
        $this->gateways = $gateways;
    }

    /**
     * Create a payment for a given order using a specified gateway
     */
    public function createPayment(string $gateway, int $orderId, array $options = []): array
    {
        $this->ensureGatewayExists($gateway);

        $order = Order::findOrFail($orderId);

        if (in_array(strtolower($order->status), ['paid', 'settlement'])) {
            throw new Exception("Order {$order->id} sudah dibayar.");
        }

        $existingPayment = $this->paymentRepo->findPendingByOrder($order->id, $gateway);
        if ($existingPayment) {
            return [
                'payment' => $existingPayment,
                'gateway_response' => $existingPayment->meta ?? [],
            ];
        }

        $amount = $order->total_amount;

        $externalId = 'INV-' . now()->format('YmdHis') . '-' . strtoupper(Str::random(6));
        $paymentReference = $externalId;

        $payment = $this->paymentRepo->create([
            'order_id' => $order->id,
            'gateway' => $gateway,
            'external_id' => $externalId,
            'payment_reference' => $paymentReference,
            'amount' => $amount,
            'status' => 'pending',
        ]);

        try {
            $customerData = [
                'customer_name'  => $order->user->name ?? 'Customer',
                'customer_email' => $order->user->email ?? 'customer@example.com',
            ];

            $options = array_merge($options, $customerData);

            $gatewayService = $this->gateways[$gateway];
            $response = $gatewayService->createPayment($externalId, $amount, $options);

            $this->paymentRepo->updateStatus($paymentReference, 'pending', $response);

            return [
                'payment' => $payment,
                'gateway_response' => $response,
            ];
        } catch (Exception $e) {
            Log::error("Payment creation failed for {$gateway}: {$e->getMessage()}", [
                'order_id' => $orderId,
                'gateway' => $gateway
            ]);
            throw new Exception("Payment creation failed for {$gateway}");
        }
    }

    /**
     * Handle webhook
     */
    public function handleWebhook(string $gateway, array $payload): void
    {
        $this->ensureGatewayExists($gateway);
        $gatewayService = $this->gateways[$gateway];

        $paymentReference = $gatewayService->getPaymentReferenceFromPayload($payload);
        $status = $gatewayService->getStatusFromPayload($payload);

        $paymentMethod = null;
        switch ($gateway) {
            case 'midtrans':
                $paymentMethod = $payload['payment_type'] ?? null;
                break;

            case 'xendit':
                $paymentMethod = $payload['payment_method']
                    ?? $payload['channel_code']
                    ?? null;
                break;

            default:
                $paymentMethod = $payload['payment_method'] ?? null;
                break;
        }

        $meta = array_merge($payload, ['payment_method' => $paymentMethod]);

        $payment = $this->paymentRepo->updateStatus($paymentReference, $status, $meta);

        if (!$payment) {
            $this->logWarning("Payment not found for reference: {$paymentReference}");
            return;
        }

        $this->handleOrderUpdate($payment, $status, $paymentReference);
    }

    private function handleOrderUpdate($payment, string $status, string $paymentReference): void
    {
        $successStatuses = ['success', 'paid', 'settled', 'settlement', 'completed'];

        if (in_array(strtolower($status), $successStatuses)) {
            $payment->order()->update([
                'status' => 'paid',
            ]);
            $this->logInfo("Order updated to PAID for payment_reference: {$paymentReference}");
        } else {
            $this->logInfo("Payment status not successful yet: {$status}");
        }
    }

    private function ensureGatewayExists(string $gateway): void
    {
        if (!isset($this->gateways[$gateway])) {
            throw new Exception("Gateway $gateway is not available.");
        }
    }

    private function logInfo(string $message, array $context = []): void
    {
        Log::info($message, $context);
    }

    private function logWarning(string $message, array $context = []): void
    {
        Log::warning($message, $context);
    }

    private function logError(string $message, Exception $e, array $context = []): void
    {
        Log::error($message . ': ' . $e->getMessage(), $context);
    }
}