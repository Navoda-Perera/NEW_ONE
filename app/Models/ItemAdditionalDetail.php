<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemAdditionalDetail extends Model
{
    use HasFactory;

    protected $table = 'item_additional_details';

    protected $fillable = [
        'type',
        'amount',
        'commission',
        'created_by',
        'location_id',
        'receiver_name',
        'receiver_address',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'commission' => 'decimal:2',
    ];

    // Define allowed types
    const TYPE_REMITTANCE = 'remittance';
    const TYPE_INSURED = 'insured';

    /**
     * Scope for remittance records
     */
    public function scopeRemittance($query)
    {
        return $query->where('type', self::TYPE_REMITTANCE);
    }

    /**
     * Scope for insured records
     */
    public function scopeInsured($query)
    {
        return $query->where('type', self::TYPE_INSURED);
    }

    /**
     * Get the user who created this record
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the location/post office for this record
     */
    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Calculate commission based on amount (2%)
     */
    public function calculateCommission()
    {
        return $this->amount * 0.02; // 2% commission
    }

    /**
     * Scope for pending records
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for processed records
     */
    public function scopeProcessed($query)
    {
        return $query->where('status', 'processed');
    }
}
