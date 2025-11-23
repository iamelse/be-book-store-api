<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderRequest;
use App\Http\Resources\OrderResource;
use App\Services\OrderService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;
use OpenApi\Annotations as OA;

class OrderController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * @OA\Get(
     *     path="api/v1/orders",
     *     summary="Get authenticated user's orders",
     *     security={{"bearerAuth":{}}},
     *     tags={"Orders"},
     *     @OA\Response(
     *         response=200,
     *         description="Successfully retrieved orders"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to retrieve orders"
     *     )
     * )
     */
    public function index()
    {
        try {
            $userId = Auth::id();
            $orders = $this->orderService->getUserOrders($userId);

            return OrderResource::collection($orders)
                ->additional(['success' => true]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil daftar pesanan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="api/v1/items/{productId}/order",
     *     summary="Create order directly from a product",
     *     security={{"bearerAuth":{}}},
     *     tags={"Orders"},
     *     @OA\Parameter(
     *         name="productId",
     *         in="path",
     *         required=true,
     *         description="Product ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"quantity"},
     *             @OA\Property(property="quantity", type="integer", example=2)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Order created successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found"
     *     )
     * )
     */
    public function storeFromProduct(OrderRequest $request, $productId)
    {
        try {
            $userId = Auth::id();
            $validated = $request->validated();

            $order = $this->orderService->createOrderFromProduct($userId, $productId, $validated);

            return (new OrderResource($order))
                ->additional([
                    'success' => true,
                    'message' => 'Pesanan berhasil dibuat dari produk langsung',
                ])
                ->response()
                ->setStatusCode(201);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan',
            ], 404);
        } catch (Exception $e) {
            Log::error('Gagal membuat pesanan dari produk: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat pesanan dari produk',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="api/v1/orders",
     *     summary="Create order from cart",
     *     security={{"bearerAuth":{}}},
     *     tags={"Orders"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"address"},
     *             @OA\Property(property="address", type="string", example="Jl. Sudirman No. 123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Order created successfully from cart"
     *     )
     * )
     */
    public function storeFromCart(OrderRequest $request)
    {
        try {
            $userId = Auth::id();
            $validated = $request->validated();

            $order = $this->orderService->createOrderFromCart($userId, $validated);

            return (new OrderResource($order))
                ->additional([
                    'success' => true,
                    'message' => 'Pesanan berhasil dibuat dari keranjang',
                ])
                ->response()
                ->setStatusCode(201);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan',
            ], 404);
        } catch (Exception $e) {
            Log::error('Gagal membuat pesanan: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat pesanan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="api/v1/orders/{orderId}",
     *     summary="Get detail of a single authenticated user order",
     *     security={{"bearerAuth":{}}},
     *     tags={"Orders"},
     *     @OA\Parameter(
     *         name="orderId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", example="order-12345")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order details fetched successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Order not found"
     *     )
     * )
     */
    public function show($orderId)
    {
        try {
            $userId = Auth::id();
            $order = $this->orderService->getOrderDetail($userId, $orderId);

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pesanan tidak ditemukan'
                ], 404);
            }

            return new OrderResource($order);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil detail pesanan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}