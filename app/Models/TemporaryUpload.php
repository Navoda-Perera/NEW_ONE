<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TemporaryUpload extends Model
{
    use HasFactory;

    protected $fillable = [
        'category',
        'location_id',
        'user_id',
    ];

    protected $casts = [
        'category' => 'string',
        'location_id' => 'integer',
        'user_id' => 'integer',
    ];

    // Relationships following Laravel conventions
    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function associates()
    {
        return $this->hasMany(TemporaryUploadAssociate::class, 'temporary_id');
    }

    public function temporaryUploadAssociates()
    {
        return $this->hasMany(TemporaryUploadAssociate::class);
    }

    public function itemBulk()
    {
        return $this->hasOne(ItemBulk::class, 'created_by', 'user_id')
                    ->where('category', 'temporary_list');
    }

    // Scopes for role-based access control
    public function scopeSingleItem($query)
    {
        return $query->where('category', 'single_item');
    }

    public function scopeTemporaryList($query)
    {
        return $query->where('category', 'temporary_list');
    }

    public function scopeForLocation($query, $locationId)
    {
        return $query->where('location_id', $locationId);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Accessors
    public function getCategoryLabelAttribute()
    {
        $labels = [
            'single_item' => 'Single Item',
            'temporary_list' => 'Temporary List'
        ];

        return $labels[$this->category] ?? $this->category;
    }
}
