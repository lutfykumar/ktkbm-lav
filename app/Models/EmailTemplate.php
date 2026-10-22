<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    protected $table = 's_email_templates';
    protected $primaryKey = 'ID_TEMPLATE';

    const CREATED_AT = 'CREATED_AT';
    const UPDATED_AT = 'UPDATED_AT';

    protected $fillable = [
        'TEMPLATE_NAME',
        'TEMPLATE_SLUG',
        'SUBJECT',
        'EMAIL_BODY',
        'VARIABLES',
        'DESCRIPTION',
        'CATEGORY',
        'IS_ACTIVE',
        'CREATED_BY',
        'UPDATED_BY',
    ];

    protected $casts = [
        'IS_ACTIVE' => 'integer',
        'VARIABLES' => 'array',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'CREATED_BY', 'id');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'UPDATED_BY', 'id');
    }
}
