<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'description',
        'price',
        'stock',
        'brand',
        'condition',
        'can_negotiate',
        'product_category_id',
        'car_make_id',
        'car_model_id',
        'user_id'
    ];

    protected $appends = ['display_image_url', 'product_category_slug'];
    protected $hidden = ['displayImage', 'category'];

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

    public function getDisplayImageUrlAttribute()
    {
         $image = $this->relationLoaded('displayImage')
            ? $this->displayImage
            : $this->displayImage()->first();

        return optional($image)->path;
    }

    /**
     * Get the category that owns the product.
     */
    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    public function getProductCategorySlugAttribute()
    {
        return $this->category ? $this->category->slug : null;
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
        // static::creating(function ($product) {
        //     $product->slug = Str::slug($product->name);

        //     $originalSlug = $product->slug;
        //     $count = 1;

        //     while (static::where('slug', $product->slug)->exists()) {
        //         $product->slug = "{$originalSlug}-{$count}";
        //         $count++;
        //     }
        // });

        // Slug generation on create
        static::creating(function ($product) {
            $product->slug = static::generateUniqueSlug($product->name);
        });

        // Slug regeneration on update only if name is changed
        static::updating(function ($product) {
            if ($product->isDirty('name')) {
                $product->slug = static::generateUniqueSlug($product->name, $product->id);
            }
        });

        static::deleting(function ($product) {
            $product->loadMissing('images');
            foreach ($product->images as $image) {
                Storage::disk('public')->delete($image->getRawOriginal('path'));
            }
        });
    }

    protected static function generateUniqueSlug($name, $ignoreId = null)
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $count = 1;

        while (static::where('slug', $slug)
            ->when($ignoreId, fn($query) => $query->where('id', '!=', $ignoreId))
            ->exists()
        ) {
            $slug = "{$originalSlug}-{$count}";
            $count++;
        }

        return $slug;
    }
}
