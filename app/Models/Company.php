<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    protected $fillable = [
        'name',
        'mobile',
        'address',
        'email',
        'type',
        'status',
        'assign_postoffice',
        'balance',
        'created_by',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
    ];

    // Relationships
    public function assignedPostoffice(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'assign_postoffice');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(Receipt::class);
    }

    public function deposits(): HasMany
    {
        return $this->hasMany(Deposit::class);
    }

    public function withdraws(): HasMany
    {
        return $this->hasMany(Withdraw::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'ACTIVE');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }
}
