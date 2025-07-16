<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class VehicleListing extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'car_make_id', 'car_model_id', 'year', 'trim', 'color',
        'interior_color', 'transmission', 'vin', 'condition', 'price',
        'description', 'contact_info'
    ];

    protected $appends = ['display_image_url'];
    protected $hidden = ['displayImage'];

    public function images()
    {
        return $this->hasMany(VehicleListingImage::class);
    }

    public function displayImage()
    {
        return $this->hasOne(VehicleListingImage::class)->latestOfMany();
    }

    public function getDisplayImageUrlAttribute()
    {
         $image = $this->relationLoaded('displayImage')
            ? $this->displayImage
            : $this->displayImage()->first();

        return optional($image)->path;
    }

    /**
     * Get the seller that listed the vehicle.
     */
    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }

    protected static function booted()
    {
        // Slug generation on create
        static::creating(function ($listing) {
            $listing->slug = static::generateUniqueSlug($listing->name);
        });

        // Slug regeneration on update only if name is changed
        static::updating(function ($listing) {
            if ($listing->isDirty('name')) {
                $listing->slug = static::generateUniqueSlug($listing->name, $listing->id);
            }
        });

        static::deleting(function ($listing) {
            $listing->loadMissing('images');
            foreach ($listing->images as $image) {
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
