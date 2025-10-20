<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SlpPricing extends Model
{
    use HasFactory;

    protected $table = 'slp_pricing';

    protected $fillable = [
        'weight_from',
        'weight_to',
        'price',
        'is_active',
    ];

    protected $casts = [
        'weight_from' => 'decimal:2',
        'weight_to' => 'decimal:2',
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Static method to calculate price based on weight
    public static function calculatePrice($weight)
    {
        $pricing = self::active()
            ->where('weight_from', '<=', $weight)
            ->where('weight_to', '>=', $weight)
            ->first();

        if ($pricing) {
            return (float) $pricing->price;
        }

        // If no exact match, find the closest higher tier
        $pricing = self::active()
            ->where('weight_from', '>', $weight)
            ->orderBy('weight_from', 'asc')
            ->first();

        return $pricing ? (float) $pricing->price : null;
    }

    // Static method to get all pricing tiers
    public static function getPricingTiers()
    {
        return self::active()->orderBy('weight_from')->get();
    }
}
