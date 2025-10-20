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
        'contact_number',
        'barcode',
        'service_type',
        'status',
        'notes',
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
