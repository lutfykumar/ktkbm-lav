<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TarifUpah extends Model
{
    use SoftDeletes;

    protected $table = 'm_tarif_upah';
    protected $primaryKey = 'ID_TARIF';
    public $timestamps = true;

    const CREATED_AT = 'CREATED_AT';
    const UPDATED_AT = 'UPDATED_AT';
    const DELETED_AT = 'DELETED_AT';

    protected $guarded = ['ID_TARIF'];

    protected $casts = [
        'NILAI_TARIF' => 'float',
        'PERSEN_KAS_KOPERASI' => 'float',
        'IS_ACTIVE' => 'boolean',
        'CREATED_AT' => 'datetime',
        'UPDATED_AT' => 'datetime',
        'DELETED_AT' => 'datetime',
    ];

    public function aktifitas(): BelongsTo
    {
        return $this->belongsTo(Aktifitas::class, 'ID_AKTIFITAS', 'ID_AKTIFITAS');
    }

    public function krk(): BelongsTo
    {
        return $this->belongsTo(Krk::class, 'ID_KRK', 'ID_KRK');
    }
}

