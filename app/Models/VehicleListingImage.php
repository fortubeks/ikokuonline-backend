<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class VehicleListingImage extends Model
{
    protected $fillable = [
        'vehicle_listing_id',
        'path',
        'is_display'
    ];

    public function getPathAttribute()
    {
        return asset('storage/' . $this->attributes['path']);
    }
}
