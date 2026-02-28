<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatLog extends Model
{
    protected $fillable = [
        'prompt',
        'response',
        'model',
        'prompt_tokens',
        'completion_tokens',
        'total_tokens',
        'error',
    ];
}
