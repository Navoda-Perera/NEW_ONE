<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DispatchAssociate extends Model
{
    protected $table = 'dispatches_associate';

    protected $fillable = [
        'item_id',
        'dispatch_id',
        'status',
        'redirect_office',
        'updated_by',
    ];

    // Relationships
    public function dispatch(): BelongsTo
    {
        return $this->belongsTo(Dispatch::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function redirectOffice(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'redirect_office');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Scopes
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeDispatched($query)
    {
        return $query->where('status', 'dispatch');
    }

    public function scopeReceived($query)
    {
        return $query->where('status', 'received');
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
    }
}
