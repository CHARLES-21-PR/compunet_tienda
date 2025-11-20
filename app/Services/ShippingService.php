<?php

namespace App\Services;

class ShippingService
{
    public function estimate(array $address, array $cart): float
    {
        // Placeholder: simple flat rate or logic
        return 0.0; // free shipping by default
    }
}
