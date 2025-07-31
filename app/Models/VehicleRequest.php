<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class VehicleRequest extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'user_id',
        'car_make_id',
        'car_model_id',
        'year',
        'trim',
        'budget_min',
        'budget_max',
    ];

    // Relationships (optional but useful)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function make()
    {
        return $this->belongsTo(CarMake::class, 'car_make_id');
    }

    public function model()
    {
        return $this->belongsTo(CarModel::class, 'car_model_id');
    }
}
