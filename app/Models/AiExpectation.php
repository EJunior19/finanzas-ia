<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class AiExpectation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'expectation_type',
        'category',
        'person_name',
        'expected_amount',
        'frequency',
        'expected_day',
        'last_fulfilled_at',
        'confidence',
    ];

    protected $casts = [
        'expected_amount' => 'decimal:2',
        'expected_day' => 'integer',
        'last_fulfilled_at' => 'date',
        'confidence' => 'integer',
    ];

    /* =====================
     |  RELACIONES
     ===================== */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /* =====================
     |  SCOPES
     ===================== */
    public function scopeMonthly(Builder $query)
    {
        return $query->where('frequency', 'monthly');
    }

    public function scopeDueThisMonth(Builder $query)
    {
        return $query->where(function ($q) {
            $q->whereNull('last_fulfilled_at')
              ->orWhereMonth('last_fulfilled_at', '!=', now()->month);
        });
    }

    /* =====================
     |  HELPERS
     ===================== */
    public function shouldTrigger(): bool
    {
        if (!$this->expected_day) {
            return false;
        }

        return now()->day > $this->expected_day &&
               ($this->last_fulfilled_at === null ||
                $this->last_fulfilled_at->month !== now()->month);
    }
}
