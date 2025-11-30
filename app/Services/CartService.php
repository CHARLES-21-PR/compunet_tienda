<?php

namespace App\Services;

class CartService
{
    protected $sessionKey = 'cart';

    public function getCart(): array
    {
        // If a selection for checkout exists, prefer it (non-destructive selection)
        $selected = session('cart_selected');
        if (is_array($selected) && ! empty($selected)) {
            return $selected;
        }

        return session($this->sessionKey, []);
    }

    public function addItem(int $productId, int $quantity, array $meta = []): array
    {
        $cart = session($this->sessionKey, []);
        $key = $productId;
        if (isset($cart[$key])) {
            $cart[$key]['quantity'] = intval($cart[$key]['quantity']) + $quantity;
        } else {
            $cart[$key] = array_merge(['id' => $productId, 'quantity' => $quantity], $meta);
        }
        session([$this->sessionKey => $cart]);

        return $cart;
    }

    public function updateItem(int $productId, int $quantity): array
    {
        $cart = session($this->sessionKey, []);
        $key = $productId;
        if (isset($cart[$key])) {
            $cart[$key]['quantity'] = $quantity;
            session([$this->sessionKey => $cart]);
        }

        return $cart;
    }

    public function removeItem(int $productId): array
    {
        $cart = session($this->sessionKey, []);
        $key = $productId;
        if (isset($cart[$key])) {
            unset($cart[$key]);
            session([$this->sessionKey => $cart]);
        }

        return $cart;
    }

    public function clear(): void
    {
        session()->forget($this->sessionKey);
    }

    public function count(): int
    {
        $cart = $this->getCart();

        return array_sum(array_column($cart, 'quantity'));
    }

    public function total(callable $priceResolver): float
    {
        $cart = $this->getCart();
        $total = 0;
        foreach ($cart as $item) {
            $price = $priceResolver($item);
            $total += ($item['quantity'] * $price);
        }

        return round($total, 2);
    }
}
