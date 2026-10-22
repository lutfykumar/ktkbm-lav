<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Group Model — Ion Auth s_groups (roles/groups)
 */
class Group extends Model
{
    protected $table = 's_groups';

    const CREATED_AT = 'CREATED_AT';
    const UPDATED_AT = 'UPDATED_AT';
    const DELETED_AT = 'DELETED_AT';

    use SoftDeletes;

    protected $fillable = ['name', 'description'];

    protected function casts(): array
    {
        return [
            'CREATED_AT' => 'datetime',
            'UPDATED_AT' => 'datetime',
            'DELETED_AT' => 'datetime',
        ];
    }

    /**
     * Group has many users
     */
    public function users()
    {
        return $this->belongsToMany(
            User::class,
            's_users_groups',
            'group_id',
            'user_id'
        )->withTimestamps('CREATED_AT', 'UPDATED_AT');
    }

    /**
     * Group has many permissions
     */
    public function permissions()
    {
        return $this->belongsToMany(
            Permission::class,
            's_group_permissions',
            'ID_GROUP',
            'ID_PERMISSION',
            'id',
            'ID_PERMISSION'
        );
    }

    /**
     * Group has many menus
     */
    public function menus()
    {
        return $this->belongsToMany(
            Menu::class,
            's_group_menu',
            'ID_GROUP',
            'ID_MENU',
            'id',
            'ID_MENU'
        );
    }

    /**
     * Get all permission slugs for this group
     */
    public function getPermissionSlugs(): array
    {
        return $this->permissions()->pluck('SLUG')->toArray();
    }
}
