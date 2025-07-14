<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Str;

class Product extends Model
{
    protected $fillable = [
        'name',
        'description',
        'price',
        'stock',
        'category_id',
        'seller_id',
        'image_url',
    ];

    /**
     * Get all product images.
     */
    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    /**
     * Get the display image of the product.
     */
    public function displayImage()
    {
        return $this->hasOne(ProductImage::class)->where('is_display', true);
    }

    /**
     * Get the category that owns the product.
     */
    public function category()
    {
        return $this->belongsTo(ProductCategory::class);
    }

    /**
     * Get the seller that owns the product.
     */
    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }

    /**
     * Get the reviews for the product.
     */
    public function reviews()
    {
        return $this->morphMany(Review::class, 'reviewable');
    }

    protected static function booted()
    {
        static::creating(function ($product) {
            $product->slug = Str::slug($product->name);

            $originalSlug = $product->slug;
            $count = 1;

            while (static::where('slug', $product->slug)->exists()) {
                $product->slug = "{$originalSlug}-{$count}";
                $count++;
            }
        });
    }
}
