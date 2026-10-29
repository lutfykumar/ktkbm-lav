<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UpahTkbmDetail extends Model
{
    use SoftDeletes;

    protected $table = 't_upah_tkbm_detail';
    protected $primaryKey = 'ID_UPAH_DETAIL';
    public $timestamps = true;

    const CREATED_AT = 'CREATED_AT';
    const UPDATED_AT = 'UPDATED_AT';
    const DELETED_AT = 'DELETED_AT';

    protected $guarded = ['ID_UPAH_DETAIL'];

    protected $casts = [
        'TGL_KERJA' => 'date',
        'JUMLAH_GANG' => 'integer',
        'JUMLAH_HARIAN' => 'integer',
        'JUMLAH_TRUCK' => 'integer',
        'VOLUME_BORONGAN' => 'float',
        'UPAH_NOMINAL' => 'float',
        'ADM_NOMINAL' => 'float',
        'TOESLAG_NOMINAL' => 'float',
        'LEMBUR_NOMINAL' => 'float',
        'JUMLAH_UPAH' => 'float',
        'CREATED_AT' => 'datetime',
        'UPDATED_AT' => 'datetime',
        'DELETED_AT' => 'datetime',
    ];

    public function upahTkbm(): BelongsTo
    {
        return $this->belongsTo(UpahTkbm::class, 'ID_UPAH_TKBM', 'ID_UPAH_TKBM');
    }
}

