<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CarModel extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'car_make_id'];

    public function make()
    {
        return $this->belongsTo(CarMake::class, 'car_make_id');
    }
}
