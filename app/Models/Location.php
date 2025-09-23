<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    protected $fillable = [
        'name',
        'code',
        'address',
        'city',
        'province',
        'postal_code',
        'phone',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the users assigned to this location
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get active locations
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
