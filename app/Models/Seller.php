<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Seller extends Model
{
    protected $fillable = [
        'store_name',
        'slug',
        'description',
        'email',
        'phone',
        'address',
        'image_url'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

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

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($seller) {
            $seller->slug = Str::slug($seller->store_name) . '-' . uniqid();
        });
    }
}
