<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SmsSent extends Model
{
    protected $table = 'sms_sents';

    protected $fillable = [
        'item_id',
        'sender_mobile',
        'receiver_mobile',
        'status',
    ];

    // Relationships
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    // Scopes for status enum values
    public function scopeAccept($query)
    {
        return $query->where('status', 'accept');
    }

    public function scopeAddbeat($query)
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

    public function scopeReturn($query)
    {
        return $query->where('status', 'return');
    }

    public function scopeDelete($query)
    {
        return $query->where('status', 'delete');
    }
}
