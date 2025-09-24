<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'receiver_name',
        'address',
        'status',
        'weight',
        'amount',
        'updated_by',
        'service_type_id',
        'created_by',
        'tracking_number',
        'postage',
        'commission',
        'destination_post_office_id',
        'notes',
        'sender_name',
        'sender_address',
        'sender_mobile',
        'receiver_mobile',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
        'amount' => 'decimal:2',
        'postage' => 'decimal:2',
        'commission' => 'decimal:2',
    ];

    // Relationships
    public function serviceType()
    {
        return $this->belongsTo(ServiceType::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Scopes
    public function scopeAccepted($query)
    {
        return $query->where('status', 'accept');
    }

    public function scopeDispatched($query)
    {
        return $query->where('status', 'dispatched');
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
    }

    /**
     * Get the destination post office
     */
    public function destinationPostOffice()
    {
        return $this->belongsTo(Location::class, 'destination_post_office_id');
    }

    // Auto-generate tracking number
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($item) {
            if (empty($item->tracking_number)) {
                $item->tracking_number = 'SL' . strtoupper(Str::random(8)) . date('ymd');
            }
        });
    }
}
