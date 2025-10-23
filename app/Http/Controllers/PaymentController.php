<?php

namespace App\Http\Controllers;

use App\Services\PaymentService;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\PaymentResource;
use App\Http\Requests\CreatePaymentRequest;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Create a payment for an order
     */
    public function create(CreatePaymentRequest $request): JsonResponse
    {
        try {
            $result = $this->paymentService->createPayment(
                $request->gateway,
                $request->order_id,
                $request->all()
            );

            $payment = $result['payment'];
            $gatewayResponse = $result['gateway_response'];

            $payment->setAttribute('gateway_response', $gatewayResponse);

            return response()->json([
                'success' => true,
                'data' => new PaymentResource($payment),
            ]);
        } catch (\Exception $e) {
            Log::error("Payment creation failed: {$e->getMessage()}", [
                'gateway' => $request->gateway ?? null,
                'order_id' => $request->order_id ?? null
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create payment. Please try again later.',
            ], 500);
        }
    }

    /**
     * Handle Midtrans webhook
     */
    public function webhookMidtrans(Request $request): JsonResponse
    {
        return $this->handleWebhook('midtrans', $request);
    }

    /**
     * Handle Xendit webhook
     */
    public function webhookXendit(Request $request): JsonResponse
    {
        return $this->handleWebhook('xendit', $request);
    }

    /**
     * Centralized webhook handler
     */
    protected function handleWebhook(string $gateway, Request $request): JsonResponse
    {
        try {
            $payment = $this->paymentService->handleWebhook($gateway, $request->all());

            return response()->json([
                'success' => true,
                'message' => "Webhook processed successfully for {$gateway}",
                'data' => $payment ? new PaymentResource($payment) : null,
            ]);
        } catch (\Exception $e) {
            Log::error("Webhook processing failed for {$gateway}: {$e->getMessage()}", [
                'payload' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => "Webhook failed for {$gateway}"
            ], 500);
        }
    }
}