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

class OrderController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Menampilkan daftar pesanan user yang sedang login
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
     * Membuat pesanan langsung dari produk (tanpa cart)
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
     * Membuat pesanan baru dari keranjang
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
     * Menampilkan detail pesanan tertentu
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