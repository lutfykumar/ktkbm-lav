<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kapal extends Model
{
    use SoftDeletes;

    protected $table = 'm_kapal';
    protected $primaryKey = 'ID_KAPAL';

    const CREATED_AT = 'CREATED_AT';
    const UPDATED_AT = 'UPDATED_AT';
    const DELETED_AT = 'DELETED_AT';

    protected $fillable = [
        'NAMA',
        'TIPE',
        'STATUS',
        'CREATED_BY',
        'UPDATED_BY',
    ];

    protected function casts(): array
    {
        return [
            'STATUS' => 'integer',
            'CREATED_AT' => 'datetime',
            'UPDATED_AT' => 'datetime',
            'DELETED_AT' => 'datetime',
        ];
    }
}
