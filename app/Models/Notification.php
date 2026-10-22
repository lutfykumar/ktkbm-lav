<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 't_notifications';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'title',
        'body',
        'type',
        'ref_id',
        'is_read',
        'created_at',
        'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
