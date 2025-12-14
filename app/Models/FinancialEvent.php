<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class FinancialEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'event_type',
        'status',
        'amount',
        'category',
        'person_name',
        'event_date',
        'due_date',
        'confidence',
        'source',
        'description',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'event_date' => 'date',
        'due_date' => 'date',
        'confidence' => 'integer',
    ];

    /* =====================
     |  RELACIONES
     ===================== */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function aiQuestions()
    {
        return $this->hasMany(AiQuestion::class);
    }

    /* =====================
     |  SCOPES (IA friendly)
     ===================== */
    public function scopePending(Builder $query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeOverdue(Builder $query)
    {
        return $query->where('status', 'overdue');
    }

    public function scopeExpected(Builder $query)
    {
        return $query->where('status', 'expected');
    }

    public function scopeCompleted(Builder $query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeType(Builder $query, string $type)
    {
        return $query->where('event_type', $type);
    }

    /* =====================
     |  HELPERS DE NEGOCIO
     ===================== */
    public function isDue(): bool
    {
        return $this->due_date && now()->greaterThan($this->due_date);
    }

    public function markCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'confidence' => 100,
        ]);
    }

    public function markOverdue(): void
    {
        if ($this->status !== 'completed') {
            $this->update(['status' => 'overdue']);
        }
    }
}
