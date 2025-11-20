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
        foreach ($cart as $entry) {
            $product = Product::find($entry['id']);
            if (!$product) continue;
            $available = $this->inventory->available($product);
            if ($entry['quantity'] > $available) {
                return [
                    'success' => false,
                    'message' => 'Stock insuficiente para ' . $product->name,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'requested' => intval($entry['quantity']),
                    'available' => $available,
                ];
            }
            $price = $priceResolver($entry);
            $items[] = ['product' => $product, 'quantity' => $entry['quantity'], 'price' => $price];
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
