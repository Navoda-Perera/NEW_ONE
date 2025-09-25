<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dispatch extends Model
{
    protected $fillable = [
        'necklabel',
        'manifest_id',
        'destination_office',
        'created_by',
        'location_id',
    ];

    // Relationships
    public function destinationOffice(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'destination_office');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function dispatchAssociates(): HasMany
    {
        return $this->hasMany(DispatchAssociate::class);
    }

    // Scopes
    public function scopeByDestination($query, $destinationId)
    {
        return $query->where('destination_office', $destinationId);
    }

    public function scopeByLocation($query, $locationId)
    {
        return $query->where('location_id', $locationId);
    }
}
