<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Seller extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'description',
        'image_url',
    ];

    /**
     * Get the products for the seller.
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get the reviews for the seller.
     */
    public function reviews()
    {
        return $this->morphMany(Review::class, 'reviewable');
    }
}
