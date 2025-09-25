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

    // Scopes for service_type enum
    public function scopeNormalPost($query)
    {
        return $query->where('service_type', 'NORMAL_POST');
    }

    public function scopeRegPost($query)
    {
        return $query->where('service_type', 'REG_POST');
    }

    public function scopeSlpCourier($query)
    {
        return $query->where('service_type', 'SLP_COURIER');
    }

    public function scopeCod($query)
    {
        return $query->where('service_type', 'COD');
    }

    public function scopeRemittance($query)
    {
        return $query->where('service_type', 'REMITTANCE');
    }
}
