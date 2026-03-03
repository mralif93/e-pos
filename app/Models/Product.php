<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'sku',
        'barcode',
        'image',
        'description',
        'price',
        'cost',
        'stock_level',
        'is_active',
        'has_variants',
    ];

    /**
     * Get product initials for fallback display.
     */
    public function getInitialsAttribute(): string
    {
        return collect(explode(' ', $this->name))
            ->map(fn($n) => mb_substr($n, 0, 1))
            ->take(2)
            ->join('') ?? 'P';
    }

    /**
     * Get a consistent background color based on product name.
     */
    public function getFallbackColorAttribute(): string
    {
        $hash = md5($this->name);
        $h = hexdec(substr($hash, 0, 2)) % 360;
        return "hsl({$h}, 60%, 85%)";
    }

    /**
     * Get a consistent text color based on product name.
     */
    public function getFallbackTextColorAttribute(): string
    {
        $hash = md5($this->name);
        $h = hexdec(substr($hash, 0, 2)) % 360;
        return "hsl({$h}, 70%, 30%)";
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }



    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function prices()
    {
        return $this->hasMany(ProductOutletPrice::class);
    }

    public function modifiers()
    {
        return $this->belongsToMany(Modifier::class, 'product_modifiers');
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }
}
