<?php

namespace App\Repositories;

use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Exception;

class CartRepository
{
    public function getActiveCart($userId)
    {
        return Cart::with('cartItems.item')
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->first();
    }

    public function createCart($userId)
    {
        return Cart::create([
            'user_id' => $userId,
            'status' => 'active'
        ]);
    }

    public function addItem($cartId, $itemId, $quantity)
    {
        return DB::transaction(function () use ($cartId, $itemId, $quantity) {
            $cartItem = CartItem::firstOrCreate(
                ['cart_id' => $cartId, 'item_id' => $itemId],
                ['quantity' => 0]
            );

            $cartItem->increment('quantity', $quantity);
            $cartItem->refresh();

            return $cartItem->load('item');
        });
    }

    public function updateQuantity($cartItemId, $quantity)
    {
        return DB::transaction(function () use ($cartItemId, $quantity) {
            $cartItem = CartItem::findOrFail($cartItemId);

            $cartItem->update([
                'quantity' => $quantity
            ]);

            return $cartItem;
        });
    }

    public function removeItem($cartItemId)
    {
        $userId = Auth::id();
        $cartItem = CartItem::find($cartItemId);

        if (! $cartItem) {
            throw new ModelNotFoundException("Item cart tidak ditemukan.");
        }

        if ($cartItem->cart->user_id !== $userId) {
            throw new Exception("Kamu tidak punya izin untuk menghapus item ini.");
        }

        $cartItem->delete();
        return true;
    }

    public function removeMultipleItem(array $cartItemIds)
    {
        return CartItem::whereIn('id', $cartItemIds)->delete();
    }

    public function clearCart($userId)
    {
        $cart = $this->getActiveCart($userId);

        if (! $cart) {
            throw new ModelNotFoundException("Cart tidak ditemukan atau sudah tidak aktif.");
        }

        DB::transaction(function () use ($cart) {
            $cart->cartItems()->delete();
            $cart->update(['status' => 'checked_out']);
        });

        return true;
    }
}