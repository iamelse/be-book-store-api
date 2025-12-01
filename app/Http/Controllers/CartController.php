<?php

namespace App\Http\Controllers;

use App\Http\Requests\CartItemRequest;
use App\Services\CartService;
use Illuminate\Support\Facades\Auth;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Traits\APIResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class CartController extends Controller
{
    use APIResponse;

    protected CartService $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    /**
     * @OA\Get(
     *     path="api/v1/cart",
     *     summary="Get cart items",
     *     tags={"Cart"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Cart retrieved successfully"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to retrieve cart"
     *     )
     * )
     */
    public function index()
    {
        try {
            $cart = $this->cartService->getCart(Auth::id());
            return $this->successResponse($cart, 'Cart retrieved successfully');
        } catch (Exception $e) {
            return $this->errorResponse('Failed to retrieve cart', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * @OA\Post(
     *     path="api/v1/cart",
     *     summary="Add item to cart",
     *     tags={"Cart"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"item_id", "quantity"},
     *             @OA\Property(property="item_id", type="integer", example=1),
     *             @OA\Property(property="quantity", type="integer", example=2)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Item added to cart successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Item not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to add item to cart"
     *     )
     * )
     */
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

    /**
     * @OA\Put(
     *     path="api/v1/cart/{cartItemId}",
     *     summary="Update cart item quantity",
     *     tags={"Cart"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="cartItemId",
     *         in="path",
     *         required=true,
     *         description="ID of cart item",
     *         @OA\Schema(type="integer", example=5)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"quantity"},
     *             @OA\Property(property="quantity", type="integer", example=3)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cart item updated successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Cart item not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to update cart item"
     *     )
     * )
     */
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

    /**
     * @OA\Delete(
     *     path="api/v1/cart/{cartItemId}",
     *     summary="Remove item from cart",
     *     tags={"Cart"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="cartItemId",
     *         in="path",
     *         required=true,
     *         description="ID of cart item",
     *         @OA\Schema(type="integer", example=5)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Item removed from cart successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Cart item not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to remove item from cart"
     *     )
     * )
     */
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

    /**
     * @OA\Delete(
     *     path="api/v1/cart/multiple",
     *     summary="Remove multiple items from cart",
     *     tags={"Cart"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"cart_item_ids"},
     *             @OA\Property(
     *                 property="cart_item_ids",
     *                 type="array",
     *                 @OA\Items(type="integer"),
     *                 example={1, 2, 3}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Items removed successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Cart item not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to remove items from cart"
     *     )
     * )
     */
    public function removeMultiple(Request $request)
    {
        $request->validate([
            'cart_item_ids' => 'required|array',
            'cart_item_ids.*' => 'integer|distinct',
        ]);

        try {
            $this->cartService->removeMultipleItemsFromCart($request->cart_item_ids);
            return $this->successResponse([], 'Items removed successfully');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Some cart items not found', 404, ['error' => $e->getMessage()]);
        } catch (Exception $e) {
            return $this->errorResponse('Failed to remove items from cart', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * @OA\Delete(
     *     path="api/v1/cart",
     *     summary="Clear all items in cart",
     *     tags={"Cart"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Cart cleared successfully"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to clear cart"
     *     )
     * )
     */
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