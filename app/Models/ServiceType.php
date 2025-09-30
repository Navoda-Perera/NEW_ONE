<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'is_active',
        'has_weight_pricing',
        'base_price',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'has_weight_pricing' => 'boolean',
        'base_price' => 'decimal:2',
    ];

    // Relationships
    public function items()
    {
        return $this->hasMany(Item::class);
    }

    public function itemBulks()
    {
        return $this->hasMany(ItemBulk::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Constants for service types
    const REGISTER_POST = 'REG_POST';
    const SLP_COURIER = 'SLP_COURIER';
    const COD = 'COD';
    const REMITTANCE = 'REMITTANCE';
    const COD_POSTAGE = 'COD_POSTAGE';
    const POST_CARD = 'POST_CARD';
    const POSTAL_ID_ONE_DAY = 'POSTAL_ID_ONE_DAY';
    const POSTAL_ID_NORMAL = 'POSTAL_ID_NORMAL';
    const REGISTER_FEE = 'REGISTER_FEE';
    const AR_CARD = 'AR_CARD';
    const DUPLICATE_GR = 'DUPLICATE_GR';
}
