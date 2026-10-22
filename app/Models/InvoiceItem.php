<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    use SoftDeletes;

    protected $table = 't_invoice_item';
    protected $primaryKey = 'ID_INVOICE_ITEM';
    public $timestamps = false;

    const CREATED_AT = 'CREATED_AT';
    const DELETED_AT = 'DELETED_AT';

    protected $guarded = ['ID_INVOICE_ITEM'];

    protected $casts = [
        'JUMLAH' => 'float',
        'HARGA_SATUAN' => 'float',
        'SUBTOTAL' => 'float',
        'CREATED_AT' => 'datetime',
        'DELETED_AT' => 'datetime',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'ID_INVOICE', 'ID_INVOICE');
    }
}
