<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $table = 's_menu';
    protected $primaryKey = 'ID_MENU';

    const CREATED_AT = 'CREATED_AT';
    const UPDATED_AT = 'UPDATED_AT';

    protected $fillable = [
        'CREATED_BY',
        'HEADER_ID',
        'PARENT_ID',
        'NAMA',
        'SLUG',
        'ICON',
        'LINK',
        'TAMPIL',
        'URUT',
    ];

    protected function casts(): array
    {
        return [
            'TAMPIL' => 'boolean',
            'URUT' => 'integer',
            'CREATED_AT' => 'datetime',
            'UPDATED_AT' => 'datetime',
        ];
    }

    public function header()
    {
        return $this->belongsTo(Menu::class, 'HEADER_ID', 'ID_MENU');
    }

    public function parent()
    {
        return $this->belongsTo(Menu::class, 'PARENT_ID', 'ID_MENU');
    }

    public function subMenus()
    {
        return $this->hasMany(Menu::class, 'PARENT_ID', 'ID_MENU')->orderBy('URUT', 'asc');
    }

    public function permissions()
    {
        return $this->hasMany(Permission::class, 'ID_MENU', 'ID_MENU')->orderBy('URUT', 'asc');
    }

    public function groups()
    {
        return $this->belongsToMany(
            Group::class,
            's_group_menu',
            'ID_MENU',
            'ID_GROUP',
            'ID_MENU',
            'id'
        );
    }
}
