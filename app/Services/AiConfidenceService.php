<?php

namespace App\Services;

use App\Models\AiMemoryItem;
use Carbon\Carbon;

class AiConfidenceService
{
    /**
     * 🔼 Reforzar confianza por uso correcto
     */
    public function reinforce(AiMemoryItem $memory, int $points = 1): void
    {
        $memory->update([
            'confidence'   => min($memory->confidence + $points, 100),
            'last_used_at' => now(),
        ]);
    }

    /**
     * 🔽 Penalizar por corrección del usuario
     */
    public function penalize(AiMemoryItem $memory, int $points = 10): void
    {
        $memory->update([
            'confidence' => max($memory->confidence - $points, 0),
        ]);
    }

    /**
     * ⏳ Penalizar memorias viejas no usadas
     */
    public function decayOldMemories(int $days = 30): void
    {
        AiMemoryItem::where('last_used_at', '<', now()->subDays($days))
            ->where('confidence', '>', 0)
            ->chunkById(100, function ($items) {
                foreach ($items as $memory) {
                    $memory->update([
                        'confidence' => max($memory->confidence - 1, 0),
                    ]);
                }
            });
    }
}
