<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $guarded = [];

    protected $casts = [
        'shipping_address' => 'array',
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments()
    {
        return $this->hasMany(\App\Models\Payment::class);
    }

    public function invoice()
    {
        // Return the most recent invoice for this order (latest) so the UI and controllers
        // pick the newest generated comprobante (usually contains the PDF).
        return $this->hasOne(\App\Models\Invoice::class)->latestOfMany();
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
