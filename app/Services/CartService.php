<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\Item;
use App\Repositories\CartRepository;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CartService
{
    protected $cartRepo;

    public function __construct(CartRepository $cartRepo)
    {
        $this->cartRepo = $cartRepo;
    }

    public function getCart($userId)
    {
        $cart = $this->cartRepo->getActiveCart($userId);

        if (!$cart) {
            $cart = $this->cartRepo->createCart($userId);
        }

        return $cart->load('cartItems.item');
    }

    public function addItemToCart($userId, $itemId, $quantity)
    {
        $cart = $this->getCart($userId);

        $item = Item::findOrFail($itemId);

        $existingCartItem = CartItem::where('cart_id', $cart->id)
            ->where('item_id', $itemId)
            ->first();

        $currentQuantity = $existingCartItem ? $existingCartItem->quantity : 0;
        $newQuantity = $currentQuantity + $quantity;

        if ($newQuantity > $item->stock) {
            throw new Exception("Quantity melebihi stock. Stock tersedia: {$item->stock}");
        }

        $cartItem = $this->cartRepo->addItem($cart->id, $itemId, $quantity);

        return $cartItem->load('item');
    }

    public function updateQuantity($userId, $cartItemId, $quantity)
    {
        $cartItem = CartItem::with('item', 'cart')->findOrFail($cartItemId);

        if ($cartItem->cart->user_id !== $userId) {
            throw new Exception("Kamu tidak punya izin untuk update item ini.");
        }

        // cek stok
        if ($quantity > $cartItem->item->stock) {
            throw new Exception("Quantity melebihi stock. Stock tersedia: {$cartItem->item->stock}");
        }

        $cartItem = $this->cartRepo->updateQuantity($cartItemId, $quantity);

        return $cartItem->load('item');
    }

    public function removeItemFromCart($cartItemId)
    {
        try {
            $this->cartRepo->removeItem($cartItemId);
        } catch (ModelNotFoundException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new Exception("Gagal menghapus item dari cart: " . $e->getMessage());
        }
    }

    public function clearCart($userId)
    {
        try {
            $this->cartRepo->clearCart($userId);
        } catch (ModelNotFoundException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new Exception("Gagal mengosongkan cart: " . $e->getMessage());
        }
    }
}