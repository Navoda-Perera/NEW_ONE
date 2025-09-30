<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TemporaryUpload extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_type',
        'location_id',
        'user_id',
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


}
