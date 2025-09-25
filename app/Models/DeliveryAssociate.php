<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryAssociate extends Model
{
    protected $table = 'deliveries_associate';

    protected $fillable = [
        'item_id',
        'delivery_id',
        'status',
        'created_by',
        'updated_by',
        'location_id',
    ];

    // Relationships
    public function delivery(): BelongsTo
    {
        return $this->belongsTo(Delivery::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    // Scopes
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeAddedToBeat($query)
    {
        return $query->where('status', 'addbeat');
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
    }

    public function scopeUndelivered($query)
    {
        return $query->where('status', 'undelivered');
    }

    public function scopeReturned($query)
    {
        return $query->where('status', 'return');
    }
}
