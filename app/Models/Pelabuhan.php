<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pelabuhan extends Model
{
    use SoftDeletes;

    protected $table = 'm_pelabuhan';
    protected $primaryKey = 'ID_PELABUHAN';

    const CREATED_AT = 'CREATED_AT';
    const UPDATED_AT = 'UPDATED_AT';
    const DELETED_AT = 'DELETED_AT';

    protected $fillable = [
        'NAMA',
        'ALAMAT',
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

    public function dermagas()
    {
        return $this->hasMany(Dermaga::class, 'ID_PELABUHAN', 'ID_PELABUHAN');
    }
}
