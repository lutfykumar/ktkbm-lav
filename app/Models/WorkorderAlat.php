<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkorderAlat extends Model
{
    use SoftDeletes;

    protected $table = 't_work_order_alat';
    protected $primaryKey = 'ID_WORK_ORDER_ALAT';
    public $timestamps = true;

    const CREATED_AT = 'CREATED_AT';
    const UPDATED_AT = 'UPDATED_AT';
    const DELETED_AT = 'DELETED_AT';

    protected $guarded = ['ID_WORK_ORDER_ALAT'];

    protected $casts = [
        'CREATED_AT' => 'datetime',
        'UPDATED_AT' => 'datetime',
        'DELETED_AT' => 'datetime',
    ];

    public function workorder(): BelongsTo
    {
        return $this->belongsTo(Workorder::class, 'ID_WORK_ORDER', 'ID_WORK_ORDER');
    }

    public function alat(): BelongsTo
    {
        return $this->belongsTo(Alat::class, 'ID_ALAT', 'ID_ALAT');
    }
}
