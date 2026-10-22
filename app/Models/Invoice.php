<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use SoftDeletes;

    protected $table = 't_invoice';
    protected $primaryKey = 'ID_INVOICE';
    public $timestamps = true;

    const CREATED_AT = 'CREATED_AT';
    const UPDATED_AT = 'UPDATED_AT';
    const DELETED_AT = 'DELETED_AT';

    protected $guarded = ['ID_INVOICE'];

    protected $casts = [
        'TANGGAL_INVOICE' => 'date',
        'TANGGAL_JATUH_TEMPO' => 'date',
        'TANGGAL_BAYAR' => 'date',
        'SUBTOTAL' => 'float',
        'PAJAK_PERSEN' => 'float',
        'PAJAK_NOMINAL' => 'float',
        'TOTAL' => 'float',
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

    public function workorder(): BelongsTo
    {
        return $this->belongsTo(Workorder::class, 'ID_WORK_ORDER', 'ID_WORK_ORDER');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class, 'ID_INVOICE', 'ID_INVOICE');
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
