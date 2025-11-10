<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Receipt extends Model
{
    protected $fillable = [
        'item_quantity',
        'item_bulk_id',
        'amount',
        'postage',
        'total_amount',
        'company_id',
        'passcode',
        'payment_type',
        'created_by',
        'location_id',
        'updated_by',
        'dlt_status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'postage' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'dlt_status' => 'boolean',
        'item_quantity' => 'integer',
    ];

    // Relationships
    public function itemBulk(): BelongsTo
    {
        return $this->belongsTo(ItemBulk::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
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
    public function scopeNotDeleted($query)
    {
        return $query->where('dlt_status', false);
    }

    public function scopeByPaymentType($query, $type)
    {
        return $query->where('payment_type', $type);
    }
}
