<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Workorder extends Model
{
    use SoftDeletes;

    protected $table = 't_work_order';
    protected $primaryKey = 'ID_WORK_ORDER';
    public $timestamps = true;

    const CREATED_AT = 'CREATED_AT';
    const UPDATED_AT = 'UPDATED_AT';
    const DELETED_AT = 'DELETED_AT';

    protected $guarded = ['ID_WORK_ORDER'];

    protected $casts = [
        'CREATED_AT' => 'datetime',
        'UPDATED_AT' => 'datetime',
        'DELETED_AT' => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'ID_CLIENT', 'ID_CLIENT');
    }

    public function pelabuhan(): BelongsTo
    {
        return $this->belongsTo(Pelabuhan::class, 'ID_PELABUHAN', 'ID_PELABUHAN');
    }

    public function kapal(): BelongsTo
    {
        return $this->belongsTo(Kapal::class, 'ID_KAPAL', 'ID_KAPAL');
    }

    public function dermaga(): BelongsTo
    {
        return $this->belongsTo(Dermaga::class, 'ID_DERMAGA', 'ID_DERMAGA');
    }

    public function aktifitas(): BelongsTo
    {
        return $this->belongsTo(Aktifitas::class, 'ID_AKTIFITAS', 'ID_AKTIFITAS');
    }

    public function satuan(): BelongsTo
    {
        return $this->belongsTo(Satuan::class, 'ID_SATUAN', 'ID_SATUAN');
    }

    public function pengawas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ID_PENGAWAS', 'id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'CREATED_BY', 'id');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'UPDATED_BY', 'id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(WorkorderDetail::class, 'ID_WORK_ORDER', 'ID_WORK_ORDER');
    }

    public function alats(): HasMany
    {
        return $this->hasMany(WorkorderAlat::class, 'ID_WORK_ORDER', 'ID_WORK_ORDER');
    }
}
