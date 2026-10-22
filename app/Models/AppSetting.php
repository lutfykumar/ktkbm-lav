<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    protected $table = 'app_settings';

    protected $fillable = [
        'key',
        'value',
        'type',
        'input_type',
        'options',
        'validation_rules',
        'placeholder',
        'help_text',
        'is_visible',
        'sort_order',
        'is_encrypted',
        'default_value',
        'group',
        'description',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'is_visible' => 'boolean',
            'is_encrypted' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
