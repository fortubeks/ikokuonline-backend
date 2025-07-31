<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleFeature extends Model
{
    protected $fillable = ['name'];

    public function listings()
    {
        return $this->belongsToMany(VehicleListing::class, 'vehicle_feature_vehicle_listing');
    }
}
