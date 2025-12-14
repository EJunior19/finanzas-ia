<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiAlert extends Model
{
    protected $fillable = [
        'user_id',
        'financial_event_id',
        'type',
        'title',
        'message',
        'trigger_date',
        'sent_at',
    ];
}

