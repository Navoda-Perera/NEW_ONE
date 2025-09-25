<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'item_id',
        'fixed_amount',
        'commission',
        'item_value',
        'status',
        'delivered_by',
        'delivered_location',
        'settlement_by',
        'settlement_location',
        'settlement_user_nic',
    ];

    protected $casts = [
        'fixed_amount' => 'decimal:2',
        'commission' => 'decimal:2',
        'item_value' => 'decimal:2',
    ];

    // Relationships
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function deliveredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delivered_by');
    }

    public function deliveredLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'delivered_location');
    }

    public function settlementBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'settlement_by');
    }

    public function settlementLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'settlement_location');
    }

    // Scopes
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeAccepted($query)
    {
        return $query->where('status', 'accept');
    }

    public function scopePayable($query)
    {
        return $query->where('status', 'payable');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }
}
