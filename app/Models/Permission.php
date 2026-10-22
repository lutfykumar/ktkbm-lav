<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $table = 's_permission';
    protected $primaryKey = 'ID_PERMISSION';
    public $timestamps = false;

    protected $fillable = [
        'ID_MENU',
        'NAMA',
        'SLUG',
        'URUT',
    ];

    public function menu()
    {
        return $this->belongsTo(Menu::class, 'ID_MENU', 'ID_MENU');
    }

    public function groups()
    {
        return $this->belongsToMany(
            Group::class,
            's_group_permissions',
            'ID_PERMISSION',
            'ID_GROUP',
            'ID_PERMISSION',
            'id'
        );
    }
}
