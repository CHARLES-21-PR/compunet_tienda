<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class shoppingCart extends Model
{
    protected $table = 'shoppings_carts';

    protected $fillable = ['user_id', 'product_id', 'quantity', 'price'];

    /**
     * Get the product associated with this cart item.
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Get the user who owns this cart item.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
