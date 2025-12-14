<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiMemoryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'memory_type',
        'key',
        'value',
        'confidence',
        'last_used_at',
    ];

    protected $casts = [
        'value' => 'array',
        'confidence' => 'integer',
        'last_used_at' => 'datetime',
    ];

    /* =====================
     |  RELACIONES
     ===================== */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /* =====================
     |  HELPERS
     ===================== */
    public function reinforce(): void
    {
        $this->update([
            'confidence' => min(100, $this->confidence + 5),
            'last_used_at' => now(),
        ]);
    }

    public function weaken(): void
    {
        $this->update([
            'confidence' => max(0, $this->confidence - 5),
        ]);
    }
}
