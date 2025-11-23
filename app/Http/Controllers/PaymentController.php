<?php

namespace App\Http\Controllers;

use App\Services\PaymentService;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\PaymentResource;
use App\Http\Requests\CreatePaymentRequest;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class PaymentController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * @OA\Post(
     *     path="api/v1/payments",
     *     summary="Create a payment request for an order",
     *     tags={"Payments"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"order_id", "gateway"},
     *             @OA\Property(property="order_id", type="string", example="order-23091"),
     *             @OA\Property(property="gateway", type="string", example="midtrans")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment created successfully"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to create payment"
     *     )
     * )
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
     * @OA\Post(
     *     path="api/v1/payments/webhook/midtrans",
     *     summary="Handle Midtrans webhook",
     *     tags={"Payments"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             example={"transaction_status": "settlement", "order_id": "order-23091"}
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Webhook processed successfully"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Webhook processing error"
     *     )
     * )
     */
    public function webhookMidtrans(Request $request): JsonResponse
    {
        return $this->handleWebhook('midtrans', $request);
    }

    /**
     * @OA\Post(
     *     path="api/v1/payments/webhook/xendit",
     *     summary="Handle Xendit webhook",
     *     tags={"Payments"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             example={"status": "PAID", "order_id": "order-23091"}
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Webhook processed successfully"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Webhook processing error"
     *     )
     * )
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