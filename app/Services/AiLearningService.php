<?php

namespace App\Services;

use App\Models\AiMemoryItem;
use App\Models\AiQuestion;

class AiLearningService
{
    public function learnFromAnswer(AiQuestion $question, mixed $answer): void
    {
        $userId = $question->user_id;

        // Aprender personas
        if ($question->question_type === 'general' && is_string($answer)) {
            AiMemoryItem::updateOrCreate(
                [
                    'user_id'     => $userId,
                    'memory_type' => 'person_map',
                    'key'         => strtolower($answer),
                ],
                [
                    'value'      => ['name' => $answer],
                    'confidence' => 70,
                    'last_used_at' => now(),
                ]
            );
        }

        // Aprender recurrencia
        if (
            $question->question_text === '¿Este gasto se repite todos los meses?' &&
            $answer === true
        ) {
            AiMemoryItem::updateOrCreate(
                [
                    'user_id'     => $userId,
                    'memory_type' => 'habit',
                    'key'         => 'monthly_expense',
                ],
                [
                    'value'      => ['confirmed' => true],
                    'confidence' => 80,
                    'last_used_at' => now(),
                ]
            );
        }
    }
}
