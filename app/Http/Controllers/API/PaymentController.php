<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\PaymentRequest;
use App\Http\Requests\API\WebhookRequest;
use App\Services\PaymentService;
use App\Models\Order;
use App\Services\MidtransCallbackService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    private PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function pay(PaymentRequest $request)
    {
        $order = Order::where('id', $request->validated()['order_id'])
                    ->where('user_id', $request->user()->id)
                    ->firstOrFail();

        $payment = $this->paymentService->initiatePayment($order, $request->validated()['method']);

        return response()->json([
            'message' => 'Payment initiated',
            'payment' => $payment,
        ]);
    }

    public function webhook(WebhookRequest $request)
    {
        $this->paymentService->handleWebhook(
            $request->validated()['order_id'],
            $request->validated()['status']
        );

        return response()->json(['message' => 'Webhook processed']);
    }

    public function callback(Request $request, MidtransCallbackService $callbackService)
    {
        $callbackService->handle($request);

        return response()->json(['message' => 'Callback processed']);
    }
}