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
        'email',
        'password',
        'role_id',
        'status',
        'mobile_number',
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
        ];
    }

    /**
     * Get the role that owns the user.
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Check if user has a specific permission.
     */
    public function hasPermission($permissionSlug)
    {
        // Load role if not already loaded
        if (!$this->relationLoaded('role')) {
            $this->load('role');
        }

        if (!$this->role) {
            return false;
        }

        // Super Admin has all permissions
        if ($this->role->slug === 'super_admin') {
            return true;
        }

        // Load permissions if not already loaded
        if (!$this->role->relationLoaded('permissions')) {
            $this->role->load('permissions');
        }

        // Check if permission exists in loaded permissions collection
        return $this->role->permissions->contains('slug', $permissionSlug);
    }

    /**
     * Check if user has any of the given permissions.
     */
    public function hasAnyPermission(array $permissionSlugs)
    {
        // Load role if not already loaded
        if (!$this->relationLoaded('role')) {
            $this->load('role');
        }

        if (!$this->role) {
            return false;
        }

        if ($this->role->slug === 'super_admin') {
            return true;
        }

        // Load permissions if not already loaded
        if (!$this->role->relationLoaded('permissions')) {
            $this->role->load('permissions');
        }

        // Check if any permission exists in loaded permissions collection
        return $this->role->permissions->whereIn('slug', $permissionSlugs)->isNotEmpty();
    }
}
