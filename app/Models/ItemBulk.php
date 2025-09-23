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
        'service_type_id',
        'location_id',
        'created_by',
        'category',
        'total_items',
        'total_amount',
        'total_postage',
        'total_commission',
        'status',
        'notes',
    ];

    protected $casts = [
        'total_items' => 'integer',
        'total_amount' => 'decimal:2',
        'total_postage' => 'decimal:2',
        'total_commission' => 'decimal:2',
    ];

    // Relationships
    public function serviceType()
    {
        return $this->belongsTo(ServiceType::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

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
}
