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

    public function carMake()
    {
        return $this->belongsTo(CarMake::class);
    }

    public function carModel()
    {
        return $this->belongsTo(CarModel::class);
    }

    public function features()
    {
        return $this->belongsToMany(VehicleFeature::class, 'vehicle_feature_vehicle_listing');
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
            $listing->slug = static::generateSlug($listing);
        });

        // Slug regeneration on update only if name is changed
        static::updating(function ($listing) {
            if (
                $listing->isDirty(['car_make_id', 'car_model_id', 'year', 'trim', 'color']) ||
                $listing->slug === null
            ) {
                $listing->slug = static::generateSlug($listing);
            }
        });

        static::deleting(function ($listing) {
            $listing->loadMissing('images');
            foreach ($listing->images as $image) {
                Storage::disk('public')->delete($image->getRawOriginal('path'));
            }
        });
    }

    protected static function generateSlug($listing)
    {
        $make = optional($listing->carMake)->name ?? '';
        $model = optional($listing->carModel)->name ?? '';

        $parts = [
            $make,
            $model,
            $listing->year,
            $listing->trim,
            $listing->color,
            $listing->id ?? Str::random(6), // fallback if ID not available yet
        ];

        $baseSlug = Str::slug(implode('-', array_filter($parts)));

        // Ensure uniqueness
        $originalSlug = $baseSlug;
        $count = 1;

        while (static::where('slug', $baseSlug)->exists()) {
            $baseSlug = "{$originalSlug}-{$count}";
            $count++;
        }

        return $baseSlug;
    }
}
