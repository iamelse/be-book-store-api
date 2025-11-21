<?php

namespace App\Http\Controllers;

use App\Http\Requests\CartItemRequest;
use App\Services\CartService;
use Illuminate\Support\Facades\Auth;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Traits\APIResponse;

class CartController extends Controller
{
    use APIResponse;

    protected CartService $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    public function index()
    {
        try {
            $cart = $this->cartService->getCart(Auth::id());
            return $this->successResponse($cart, 'Cart retrieved successfully');
        } catch (Exception $e) {
            return $this->errorResponse('Failed to retrieve cart', 500, ['error' => $e->getMessage()]);
        }
    }

    public function add(CartItemRequest $request)
    {
        try {
            $cartItem = $this->cartService->addItemToCart(
                Auth::id(),
                $request->item_id,
                $request->quantity
            );

            return $this->successResponse($cartItem, 'Item added to cart successfully', 201);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Item not found', 404, ['error' => $e->getMessage()]);
        } catch (Exception $e) {
            return $this->errorResponse('Failed to add item to cart', 500, ['error' => $e->getMessage()]);
        }
    }

    public function update(CartItemRequest $request, $cartItemId)
    {
        try {
            $cartItem = $this->cartService->updateQuantity(
                Auth::id(),
                $cartItemId,
                $request->quantity
            );

            return $this->successResponse($cartItem, 'Cart item updated successfully');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Cart item not found', 404, ['error' => $e->getMessage()]);
        } catch (Exception $e) {
            return $this->errorResponse('Failed to update cart item', 500, ['error' => $e->getMessage()]);
        }
    }

    public function remove($cartItemId)
    {
        try {
            $this->cartService->removeItemFromCart($cartItemId);
            return $this->successResponse([], 'Item removed from cart successfully');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Cart item not found', 404, ['error' => $e->getMessage()]);
        } catch (Exception $e) {
            return $this->errorResponse('Failed to remove item from cart', 500, ['error' => $e->getMessage()]);
        }
    }

    public function clear()
    {
        try {
            $this->cartService->clearCart(Auth::id());
            return $this->successResponse([], 'Cart cleared successfully');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Cart not found', 404, ['error' => $e->getMessage()]);
        } catch (Exception $e) {
            return $this->errorResponse('Failed to clear cart', 500, ['error' => $e->getMessage()]);
        }
    }
}