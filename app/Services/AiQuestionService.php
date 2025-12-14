<?php

namespace App\Services;

use App\Models\AiQuestion;
use App\Models\FinancialEvent;
use App\Services\AiMemoryService;

class AiQuestionService
{
    public function __construct(
        protected AiMemoryService $memory
    ) {}

    public function generateForEvent(FinancialEvent $event): void
    {
        $userId = $event->user_id;

        /**
         * 1️⃣ Persona asociada
         * Intentamos usar memoria antes de preguntar
         */
        if (is_null($event->person_name)) {

            // 🔍 ¿Tenemos persona frecuente para esta categoría?
            if (
                $this->memory->hasReliableMemory(
                    $userId,
                    'person_map',
                    $event->category
                )
            ) {
                $memory = $this->memory->getMemory(
                    $userId,
                    'person_map',
                    $event->category
                );

                // Autocompletar
                $event->update([
                    'person_name' => $memory->value['name'] ?? null
                ]);

            } else {
                // Preguntar solo si no hay memoria
                $this->createQuestion(
                    $event,
                    'general',
                    '¿A quién realizaste este pago o gasto?'
                );
            }
        }

        /**
         * 2️⃣ Monto faltante
         */
        if (is_null($event->amount)) {
            $this->createQuestion(
                $event,
                'amount_check',
                '¿Cuál fue el monto exacto?'
            );
        }

        /**
         * 3️⃣ Deuda sin vencimiento
         */
        if ($event->event_type === 'debt' && is_null($event->due_date)) {
            $this->createQuestion(
                $event,
                'confirm_payment',
                '¿Cuándo vence esta deuda?'
            );
        }

        /**
         * 4️⃣ Gasto potencialmente recurrente
         * (solo pregunta si aún no se aprendió el hábito)
         */
        if (
            $event->event_type === 'expense' &&
            is_null($event->due_date) &&
            !$this->memory->hasReliableMemory(
                $userId,
                'habit',
                'monthly_expense'
            )
        ) {
            $this->createQuestion(
                $event,
                'general',
                '¿Este gasto se repite todos los meses?'
            );
        }
    }

    /**
     * Crear pregunta evitando duplicados
     */
    private function createQuestion(
        FinancialEvent $event,
        string $type,
        string $text
    ): void {
        AiQuestion::firstOrCreate([
            'user_id'             => $event->user_id,
            'financial_event_id'  => $event->id,
            'question_type'       => $type,
            'question_text'       => $text,
        ]);
    }
}
