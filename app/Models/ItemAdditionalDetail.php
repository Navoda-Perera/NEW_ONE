<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemAdditionalDetail extends Model
{
    use HasFactory;

    protected $table = 'item_additional_details';

    protected $fillable = [
        'item_id',
        'type',
        'amount',
        'commission',
        'created_by',
        'location_id',
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
     * Get the item this additional detail belongs to
     */
    public function item()
    {
        return $this->belongsTo(Item::class);
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
}
