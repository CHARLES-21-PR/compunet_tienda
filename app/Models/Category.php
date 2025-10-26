<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name', 'slug'];

    // Usar 'slug' en la URL (asegúrate de tener columna slug única)
    public function getRouteKeyName()
    {
        return 'slug';
    }

    // relación con productos (ajusta el nombre del modelo Product si es distinto)
    public function products()
    {
        return $this->hasMany(\App\Models\Product::class);
    }
}
