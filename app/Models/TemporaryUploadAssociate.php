<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TemporaryUploadAssociate extends Model
{
    use HasFactory;

    protected $fillable = [
        'temporary_id',
        'amount',
        'item_value',
        'sender_name',
        'receiver_address',
        'postage',
        'commission',
        'weight',
        'fix_amount',
        'receiver_name',
        'barcode',
        'status',
        'service_type', // Added for per-item service type
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'item_value' => 'decimal:2',
        'postage' => 'decimal:2',
        'commission' => 'decimal:2',
        'weight' => 'decimal:2',
        'fix_amount' => 'decimal:2',
    ];

    // Relationships
    public function temporaryUpload()
    {
        return $this->belongsTo(TemporaryUpload::class, 'temporary_id');
    }

    // Get service type from related ItemBulk record
    public function getServiceTypeAttribute()
    {
        // Find the ItemBulk record for this temporary upload
        $itemBulk = ItemBulk::where('created_by', $this->temporaryUpload->user_id)
                           ->where('location_id', $this->temporaryUpload->location_id)
                           ->where('category', 'single_item')
                           ->latest()
                           ->first();

        return $itemBulk ? $itemBulk->service_type : 'register_post';
    }

    // Scopes
    public function scopeAccepted($query)
    {
        return $query->where('status', 'accept');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'reject');
    }
}
