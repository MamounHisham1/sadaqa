<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StreamLink extends Model
{
    protected $fillable = [
        'token',
        'recipient_name',
        'dedication_type',
        'sender_name',
        'message',
        'rotation',
        'password',
    ];

    protected function casts(): array
    {
        return [
            'last_played_at' => 'datetime',
            'ayahs_played' => 'integer',
            'khatmas' => 'integer',
            'views' => 'integer',
            'rotation' => 'array',
        ];
    }
}
