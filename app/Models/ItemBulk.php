<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemBulk extends Model
{
    use HasFactory;

    protected $table = 'item_bulk';

    protected $fillable = [
        'sender_name',
        'service_type',
        'location_id',
        'created_by',
        'category',
        'item_quantity',
        'notes',
    ];

    protected $casts = [
        'item_quantity' => 'integer',
    ];

    // Relationships
    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(Item::class, 'item_bulk_id');
    }

    public function receipts()
    {
        return $this->hasMany(Receipt::class, 'item_bulk_id');
    }

    // Scopes for category enum
    public function scopeSingleItem($query)
    {
        return $query->where('category', 'single_item');
    }

    public function scopeBulkList($query)
    {
        return $query->where('category', 'bulk_list');
    }

    public function scopeTemporaryList($query)
    {
        return $query->where('category', 'temporary_list');
    }

    // Scopes for service_type using direct column comparison
    public function scopeRegisterPost($query)
    {
        return $query->where('service_type', 'register_post');
    }

    public function scopeSlpCourier($query)
    {
        return $query->where('service_type', 'slp_courier');
    }

    public function scopeCod($query)
    {
        return $query->where('service_type', 'cod');
    }

    public function scopeRemittance($query)
    {
        return $query->where('service_type', 'remittance');
    }
}
