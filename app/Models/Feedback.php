<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    protected $fillable = ['type', 'message', 'contact', 'url', 'resolved'];

    protected function casts(): array
    {
        return ['resolved' => 'boolean'];
    }
}
