<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UpahTkbm extends Model
{
    use SoftDeletes;

    protected $table = 't_upah_tkbm';
    protected $primaryKey = 'ID_UPAH_TKBM';
    public $timestamps = true;

    const CREATED_AT = 'CREATED_AT';
    const UPDATED_AT = 'UPDATED_AT';
    const DELETED_AT = 'DELETED_AT';

    protected $guarded = ['ID_UPAH_TKBM'];

    protected $casts = [
        'TGL_KEGIATAN_START' => 'date',
        'TGL_KEGIATAN_END' => 'date',
        'TGL_BAYAR' => 'date',
        'TOTAL_TRUCK' => 'integer',
        'TOTAL_VOLUME' => 'float',
        'SUBTOTAL_UPAH' => 'float',
        'TOTAL_ADM' => 'float',
        'TOTAL_TOESLAG' => 'float',
        'TOTAL_LEMBUR' => 'float',
        'GRAND_TOTAL' => 'float',
        'ALOKASI_BURUH' => 'float',
        'ALOKASI_KOPERASI' => 'float',
        'CREATED_AT' => 'datetime',
        'UPDATED_AT' => 'datetime',
        'DELETED_AT' => 'datetime',
    ];

    public function workorder(): BelongsTo
    {
        return $this->belongsTo(Workorder::class, 'ID_WORK_ORDER', 'ID_WORK_ORDER');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'ID_CLIENT', 'ID_CLIENT');
    }

    public function kapal(): BelongsTo
    {
        return $this->belongsTo(Kapal::class, 'ID_KAPAL', 'ID_KAPAL');
    }

    public function dermaga(): BelongsTo
    {
        return $this->belongsTo(Dermaga::class, 'ID_DERMAGA', 'ID_DERMAGA');
    }

    public function details(): HasMany
    {
        return $this->hasMany(UpahTkbmDetail::class, 'ID_UPAH_TKBM', 'ID_UPAH_TKBM');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'CREATED_BY', 'id');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'UPDATED_BY', 'id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'REF_INVOICE_ID', 'ID_INVOICE');
    }
}

