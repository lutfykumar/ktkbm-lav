<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

/**
 * User Model — Compatible with Ion Auth s_users schema
 *
 * Key differences from default Laravel User:
 * - Table: s_users
 * - No standard timestamps (uses CREATED_AT, UPDATED_AT with datetime + created_on/last_login as Unix int)
 * - Identity: email (unique)
 * - Password: bcrypt (compatible with Laravel Hash::check)
 * - Soft delete: DELETED_AT column
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The table associated with the model.
     */
    protected $table = 's_users';

    /**
     * Laravel timestamp column names (Ion Auth uses uppercase)
     */
    const CREATED_AT = 'CREATED_AT';
    const UPDATED_AT = 'UPDATED_AT';

    /**
     * Soft delete column
     */
    const DELETED_AT = 'DELETED_AT';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'ip_address',
        'username',
        'password',
        'email',
        'avatar',
        'phone',
        'active',
        'created_on',
        'last_login',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
        'activation_selector',
        'activation_code',
        'forgotten_password_selector',
        'forgotten_password_code',
    ];

    /**
     * Attribute casting
     */
    protected function casts(): array
    {
        return [
            'password'      => 'hashed',
            'active'        => 'boolean',
            'created_on'    => 'integer',   // Unix timestamp
            'last_login'    => 'integer',   // Unix timestamp
            'CREATED_AT'    => 'datetime',
            'UPDATED_AT'    => 'datetime',
            'DELETED_AT'    => 'datetime',
        ];
    }

    /**
     * Get created_on as Carbon instance
     */
    public function getCreatedOnDateAttribute(): ?Carbon
    {
        return $this->created_on ? Carbon::createFromTimestamp($this->created_on) : null;
    }

    /**
     * Get last_login as Carbon instance
     */
    public function getLastLoginDateAttribute(): ?Carbon
    {
        return $this->last_login ? Carbon::createFromTimestamp($this->last_login) : null;
    }

    /**
     * Get avatar URL
     */
    public function getAvatarUrlAttribute(): string
    {
        return $this->avatar
            ? asset('filemanager/avatar/' . $this->avatar)
            : asset('filemanager/avatar/default.jpg');
    }

    /**
     * User belongs to many groups (roles)
     */
    public function groups()
    {
        return $this->belongsToMany(
            Group::class,
            's_users_groups',
            'user_id',
            'group_id'
        )->withTimestamps('CREATED_AT', 'UPDATED_AT');
    }

    /**
     * Get the primary group (role)
     */
    public function getGroupAttribute()
    {
        return $this->groups->first();
    }

    /**
     * Get group ID (role ID)
     */
    public function getGroupIdAttribute(): ?int
    {
        return $this->groups()->first()?->id;
    }

    /**
     * Get group name (role name)
     */
    public function getGroupNameAttribute(): ?string
    {
        return $this->groups()->first()?->name;
    }

    /**
     * Check if user has a specific permission slug
     */
    public function hasPermission(string $slug): bool
    {
        $permissions = $this->getCachedPermissions();
        return in_array($slug, $permissions);
    }

    /**
     * Get all permission slugs for this user (cached in session)
     */
    public function getCachedPermissions(): array
    {
        $cacheKey = 'user_permissions_' . $this->id;
        return cache()->remember($cacheKey, 300, function () {
            $groupId = $this->getGroupIdAttribute();
            if (!$groupId) {
                return [];
            }

            return \DB::table('s_group_permissions as gp')
                ->join('s_permission as p', 'p.ID_PERMISSION', '=', 'gp.ID_PERMISSION')
                ->where('gp.ID_GROUP', $groupId)
                ->pluck('p.SLUG')
                ->toArray();
        });
    }

    /**
     * Clear permissions cache
     */
    public function clearPermissionsCache(): void
    {
        cache()->forget('user_permissions_' . $this->id);
    }

    /**
     * Check if user has dashboard access (role_id IN 1, 2, 3)
     */
    public function hasDashboardAccess(): bool
    {
        $groupId = $this->getGroupIdAttribute();
        return in_array($groupId, [1, 2, 3]);
    }

    /**
     * Scope active users
     */
    public function scopeActive($query)
    {
        return $query->where('active', 1);
    }
}
