<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkorderDetail extends Model
{
    use SoftDeletes;

    protected $table = 't_work_order_detail';
    protected $primaryKey = 'ID_WORK_ORDER_DETAIL';
    public $timestamps = true;

    const CREATED_AT = 'CREATED_AT';
    const UPDATED_AT = 'UPDATED_AT';
    const DELETED_AT = 'DELETED_AT';

    protected $guarded = ['ID_WORK_ORDER_DETAIL'];

    protected $casts = [
        'HASIL' => 'float',
        'JUMLAH_TRUCK' => 'integer',
        'RANSUM' => 'integer',
        'CREATED_AT' => 'datetime',
        'UPDATED_AT' => 'datetime',
        'DELETED_AT' => 'datetime',
    ];

    public function workorder(): BelongsTo
    {
        return $this->belongsTo(Workorder::class, 'ID_WORK_ORDER', 'ID_WORK_ORDER');
    }

    public function krk(): BelongsTo
    {
        return $this->belongsTo(Krk::class, 'ID_KRK', 'ID_KRK');
    }

    public function satuan(): BelongsTo
    {
        return $this->belongsTo(Satuan::class, 'ID_SATUAN', 'ID_SATUAN');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'CREATED_BY', 'id');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'UPDATED_BY', 'id');
    }
}
