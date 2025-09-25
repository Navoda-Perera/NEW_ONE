<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Delivery extends Model
{
    protected $fillable = [
        'postman_id',
        'beat_number',
    ];

    // Relationships
    public function postman(): BelongsTo
    {
        return $this->belongsTo(Postman::class);
    }

    public function deliveryAssociates(): HasMany
    {
        return $this->hasMany(DeliveryAssociate::class);
    }

    // Scopes
    public function scopeByPostman($query, $postmanId)
    {
        return $query->where('postman_id', $postmanId);
    }

    public function scopeByBeatNumber($query, $beatNumber)
    {
        return $query->where('beat_number', $beatNumber);
    }
}
