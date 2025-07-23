<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CarMake extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function models()
    {
        return $this->hasMany(CarModel::class);
    }
}
