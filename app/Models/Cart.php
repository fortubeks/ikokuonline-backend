<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    //
    protected $fillable = ['user_id', 'guest_id'];

    protected $hidden = ['user_id', 'guest_id'];

    public function items()
    {
        return $this->hasMany(CartItem::class);
    }
}
