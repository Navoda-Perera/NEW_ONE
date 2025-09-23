<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TemporaryUpload extends Model
{
    use HasFactory;

    protected $fillable = [
        'location_id',
        'user_id',
        'filename',
        'original_filename',
        'total_items',
        'status',
        'notes',
    ];

    protected $casts = [
        'total_items' => 'integer',
    ];

    // Relationships
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

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}
