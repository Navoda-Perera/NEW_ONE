<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'nic',
        'email',
        'mobile',
        'password',
        'user_type',
        'role',
        'is_active',
        'company_name',
        'company_br',
        'location_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is PM
     */
    public function isPM(): bool
    {
        return $this->role === 'pm';
    }

    /**
     * Check if user is customer
     */
    public function isCustomer(): bool
    {
        return $this->role === 'customer';
    }

    /**
     * Check if user is external customer (external user type with customer role)
     */
    public function isExternalCustomer(): bool
    {
        return $this->user_type === 'external' && $this->role === 'customer';
    }

    /**
     * Check if user is internal (internal user type)
     */
    public function isInternal(): bool
    {
        return $this->user_type === 'internal';
    }

    /**
     * Check if user is external (external user type)
     */
    public function isExternal(): bool
    {
        return $this->user_type === 'external';
    }

    /**
     * Get the username field for authentication (NIC)
     */
    public function getAuthIdentifierName()
    {
        return 'nic';
    }

    /**
     * Get the location assigned to this user
     */
    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}
