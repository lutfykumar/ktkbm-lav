<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppConfig extends Model
{
    protected $table = 's_appconfig';
    protected $primaryKey = 'ID_CONFIG';
    public $timestamps = false;

    protected $fillable = [
        'GROUP',
        'LABEL',
        'TYPE',
        'INDIKATOR',
        'VALUE',
    ];
}
