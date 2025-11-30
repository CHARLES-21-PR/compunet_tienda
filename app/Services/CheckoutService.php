<?php

namespace App\Services;

use App\Models\Product;

class CheckoutService
{
    protected $cartService;

    protected $inventory;

    protected $payment;

    protected $invoice;

    protected $shipping;

    public function __construct(CartService $cartService, InventoryService $inventory, PaymentService $payment, InvoiceService $invoice, ShippingService $shipping)
    {
        $this->cartService = $cartService;
        $this->inventory = $inventory;
        $this->payment = $payment;
        $this->invoice = $invoice;
        $this->shipping = $shipping;
    }

    /**
     * Prepare an order: validate inventory and compute total
     */
    public function prepareOrder(callable $priceResolver): array
    {
        $cart = $this->cartService->getCart();
        $items = [];
        foreach ($cart as $key => $entry) {
            // support different cart entry shapes: ['id'=>..] or keyed by product id
            $productId = null;
            if (is_array($entry)) {
                $productId = $entry['id'] ?? $entry['product_id'] ?? null;
            }
            if (empty($productId)) {
                // if entry key looks like an id, use it
                if (is_int($key) || ctype_digit((string) $key)) {
                    $productId = (int) $key;
                }
            }
            if (empty($productId)) {
                continue;
            }
            $product = Product::find($productId);
            if (! $product) {
                continue;
            }
            $quantity = 1;
            if (is_array($entry)) {
                $quantity = intval($entry['quantity'] ?? 1);
            }
            // check availability
            $available = $this->inventory->available($product);
            if ($quantity > $available) {
                return [
                    'success' => false,
                    'message' => 'Stock insuficiente para '.$product->name,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'requested' => $quantity,
                    'available' => $available,
                ];
            }
            $price = $priceResolver(is_array($entry) ? $entry : ['id' => $productId, 'quantity' => $quantity]);
            $items[] = ['product' => $product, 'quantity' => $quantity, 'price' => $price];
        }

        $total = $this->cartService->total($priceResolver);

        return ['success' => true, 'items' => $items, 'total' => $total];
    }

    public function chargePayment(array $payload): array
    {
        return $this->payment->charge($payload);
    }

    public function createInvoice(array $orderData): array
    {
        return $this->invoice->createInvoice($orderData);
    }

    public function estimateShipping(array $address): float
    {
        return $this->shipping->estimate($address, $this->cartService->getCart());
    }
}
