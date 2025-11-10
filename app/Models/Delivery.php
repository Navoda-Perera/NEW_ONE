<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Delivery extends Model
{
    protected $fillable = [
        'beat_number',
    ];

    // Relationships
    public function deliveryAssociates(): HasMany
    {
        return $this->hasMany(DeliveryAssociate::class);
    }

    // Scopes
    public function scopeByBeatNumber($query, $beatNumber)
    {
        return $query->where('beat_number', $beatNumber);
    }
}
