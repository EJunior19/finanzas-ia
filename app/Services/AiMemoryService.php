<?php

namespace App\Services;

use App\Models\AiMemoryItem;

class AiMemoryService
{
    /**
     * 🔍 Verifica si existe una memoria confiable
     */
    public function hasReliableMemory(
        int $userId,
        string $memoryType,
        string $key,
        int $minConfidence = 70
    ): bool {
        return AiMemoryItem::where('user_id', $userId)
            ->where('memory_type', $memoryType)
            ->where('key', strtolower($key))
            ->where('confidence', '>=', $minConfidence)
            ->exists();
    }

    /**
     * 📦 Obtener memoria puntual
     */
    public function getMemory(
        int $userId,
        string $memoryType,
        string $key
    ): ?AiMemoryItem {
        return AiMemoryItem::where('user_id', $userId)
            ->where('memory_type', $memoryType)
            ->where('key', strtolower($key))
            ->first();
    }

    /**
     * 📚 Obtener todas las memorias de un tipo
     * (útil para contexto futuro)
     */
    public function getAllByType(
        int $userId,
        string $memoryType
    ) {
        return AiMemoryItem::where('user_id', $userId)
            ->where('memory_type', $memoryType)
            ->orderByDesc('confidence')
            ->get();
    }

    /**
     * 🔁 Marcar memoria como usada
     * (sube confianza levemente)
     */
    public function markUsed(AiMemoryItem $memory): void
    {
        $memory->update([
            'last_used_at' => now(),
            'confidence'   => min($memory->confidence + 1, 100),
        ]);
    }

    /**
     * 📉 Penalizar memoria incorrecta
     * (cuando el usuario corrige algo)
     */
    public function penalize(
        int $userId,
        string $memoryType,
        string $key,
        int $penalty = 10
    ): void {
        $memory = $this->getMemory($userId, $memoryType, $key);

        if (!$memory) {
            return;
        }

        $memory->update([
            'confidence' => max($memory->confidence - $penalty, 0),
        ]);
    }

    /**
     * 🧠 Crear o reforzar memoria
     * (usado por AiLearningService)
     */
    public function reinforce(
        int $userId,
        string $memoryType,
        string $key,
        array $value,
        int $confidence = 70
    ): void {
        AiMemoryItem::updateOrCreate(
            [
                'user_id'     => $userId,
                'memory_type' => $memoryType,
                'key'         => strtolower($key),
            ],
            [
                'value'        => $value,
                'confidence'   => $confidence,
                'last_used_at' => now(),
            ]
        );
    }
}
