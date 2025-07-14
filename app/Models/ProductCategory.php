<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Support\Str;

class ProductCategory extends Model
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    //
    protected $fillable = ['name', 'parent_id'];

    public function parent()
    {
        return $this->belongsTo(ProductCategory::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(ProductCategory::class, 'parent_id');
    }

    protected static function booted()
    {
        static::creating(function ($category) {
            $category->slug = Str::slug($category->name);

            // Ensure uniqueness
            $originalSlug = $category->slug;
            $count = 1;

            while (static::where('slug', $category->slug)->exists()) {
                $category->slug = "{$originalSlug}-{$count}";
                $count++;
            }
        });
    }
}
