<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class AiQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'question_type',
        'financial_event_id',
        'question_text',
        'answer_type',
        'answer_payload',
        'asked_at',
        'answered_at',
    ];

    protected $casts = [
        'answer_payload' => 'array',
        'asked_at' => 'datetime',
        'answered_at' => 'datetime',
    ];

    /* =====================
     |  RELACIONES
     ===================== */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function financialEvent()
    {
        return $this->belongsTo(FinancialEvent::class);
    }

    /* =====================
     |  SCOPES
     ===================== */
    public function scopePending(Builder $query)
    {
        return $query->whereNull('answered_at');
    }

    /* =====================
     |  HELPERS
     ===================== */
    public function answer(string $type, array $payload = []): void
    {
        $this->update([
            'answer_type' => $type,
            'answer_payload' => $payload,
            'answered_at' => now(),
        ]);
    }
}
