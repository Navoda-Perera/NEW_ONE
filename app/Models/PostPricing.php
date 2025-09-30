<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostPricing extends Model
{
    use HasFactory;

    protected $table = 'post_pricing';

    protected $fillable = [
        'service_type',
        'min_weight',
        'max_weight',
        'price'
    ];

    protected $casts = [
        'min_weight' => 'decimal:2',
        'max_weight' => 'decimal:2',
        'price' => 'decimal:2',
    ];

    // Service type constant
    const TYPE_REGISTER = 'register';

    /**
     * Calculate price based on weight and service type
     */
    public static function calculatePrice($weight, $serviceType)
    {
        $pricing = self::where('service_type', $serviceType)
            ->where('min_weight', '<=', $weight)
            ->where('max_weight', '>=', $weight)
            ->first();

        return $pricing ? $pricing->price : null;
    }

    /**
     * Get all pricing tiers for a service type
     */
    public static function getPricingTiers($serviceType = null)
    {
        $query = self::orderBy('min_weight');

        if ($serviceType) {
            $query->where('service_type', $serviceType);
        }

        return $query->get();
    }

    /**
     * Scope for register post pricing
     */
    public function scopeRegister($query)
    {
        return $query->where('service_type', self::TYPE_REGISTER);
    }

    /**
     * Scope for register post pricing (alternative method name)
     */
    public function scopeForRegisterPost($query)
    {
        return $query->where('service_type', self::TYPE_REGISTER);
    }
}
