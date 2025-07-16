<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    protected $fillable = [
        'product_id',
        'path',
        'is_display'
    ];

    public function getPathAttribute()
    {
        return asset('storage/' . $this->attributes['path']);
    }
}
