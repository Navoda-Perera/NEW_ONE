<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_bulk_id',
        'barcode',
        'receiver_name',
        'receiver_address',
        'status',
        'weight',
        'amount',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
        'amount' => 'decimal:2',
    ];

    // Relationships
    public function itemBulk()
    {
        return $this->belongsTo(ItemBulk::class, 'item_bulk_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function trackings()
    {
        return $this->hasMany(Tracking::class);
    }

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function smsSents()
    {
        return $this->hasMany(SmsSent::class);
    }

    public function deliveryAssociates()
    {
        return $this->hasMany(DeliveryAssociate::class);
    }

    public function dispatchAssociates()
    {
        return $this->hasMany(DispatchAssociate::class);
    }

    public function withdraws()
    {
        return $this->hasMany(Withdraw::class);
    }

    public function temporaryUploadAssociate()
    {
        return $this->hasOne(TemporaryUploadAssociate::class, 'barcode', 'barcode');
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

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // Barcode must be provided from PM/customer - no auto-generation
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($item) {
            if (empty($item->barcode)) {
                $item->barcode = 'SL' . strtoupper(Str::random(8)) . date('ymd');
            }
        });
    }
}
