<?php

namespace App\Http\Controllers;

use App\Models\AiQuestion;
use App\Models\FinancialEvent;
use App\Models\UserFinancialProfile;
use App\Services\AiLearningService;
use App\Services\AiMemoryService;
use App\Services\AiConfidenceService;
use Illuminate\Http\Request;

class AIQuestionController extends Controller
{
    public function answer(
        Request $request,
        AiQuestion $question,
        AiLearningService $learning,
        AiMemoryService $memoryService,
        AiConfidenceService $confidenceService
    ) {
        // ❗ Evitar responder dos veces
        if ($question->answered_at) {
            return response()->json([
                'error' => 'Esta pregunta ya fue respondida'
            ], 422);
        }

        $answer = $request->input('answer');

        if ($answer === null) {
            return response()->json([
                'error' => 'La respuesta es obligatoria'
            ], 422);
        }

        /**
         * 1️⃣ Guardar respuesta
         */
        $question->update([
            'answer_type'    => $this->resolveAnswerType($answer),
            'answer_payload' => is_array($answer) ? $answer : ['value' => $answer],
            'answered_at'    => now(),
        ]);

        /**
         * 2️⃣ ONBOARDING — respuestas globales (no ligadas a eventos)
         */
        if (str_starts_with($question->question_type, 'onboarding_')) {
            $this->applyOnboardingAnswer($question, $answer);
        }

        /**
         * 3️⃣ Aplicar impacto al evento (si corresponde)
         */
        if ($question->financial_event_id) {
            $this->applyToEvent($question, $answer);
        }

        /**
         * 4️⃣ Aprendizaje IA (crear / actualizar memorias)
         */
        $learning->learnFromAnswer($question, $answer);

        /**
         * 5️⃣ C3 — Ajuste de confianza dinámica
         */
        $this->adjustMemoryConfidence(
            $question,
            $answer,
            $memoryService,
            $confidenceService
        );

        return response()->json([
            'status'      => 'answered',
            'question_id' => $question->id
        ]);
    }

    /**
     * Determinar tipo de respuesta
     */
    private function resolveAnswerType(mixed $answer): string
    {
        if (is_bool($answer)) {
            return $answer ? 'yes' : 'no';
        }

        return 'edit';
    }

    /**
     * ==============================
     * 🧠 ONBOARDING — PERFIL FINANCIERO
     * ==============================
     */
    private function applyOnboardingAnswer(AiQuestion $question, mixed $answer): void
    {
        $profile = UserFinancialProfile::firstOrCreate([
            'user_id' => $question->user_id
        ]);

        match ($question->question_type) {
            'onboarding_income' =>
                $profile->update(['monthly_income' => (float) $answer]),

            'onboarding_salary_day' =>
                $profile->update(['salary_day' => (int) $answer]),

            'onboarding_fixed_expenses' =>
                $profile->update([
                    'fixed_expenses' => is_array($answer) ? $answer : ['raw' => $answer]
                ]),

            'onboarding_debts' =>
                $profile->update([
                    'debts' => is_array($answer) ? $answer : ['raw' => $answer]
                ]),

            default => null
        };

        // ✅ Marcar onboarding completo si lo esencial ya está
        if (
            $profile->monthly_income &&
            $profile->salary_day
        ) {
            $profile->update([
                'onboarding_completed' => true,
                'confidence' => min(100, $profile->confidence + 20)
            ]);
        }
    }

    /**
     * ==============================
     * 📌 Aplicar respuesta al evento
     * ==============================
     */
    private function applyToEvent(AiQuestion $question, mixed $answer): void
    {
        $event = $question->financialEvent;

        match ($question->question_type) {
            'general' => $event->update([
                'person_name' => is_string($answer) ? $answer : null
            ]),

            'amount_check' => $event->update([
                'amount' => (float) $answer
            ]),

            default => null
        };

        // 🔼 Subimos confianza del evento
        $event->increment('confidence', 5);
    }

    /**
     * ==============================
     * 🧠 C3 — Ajustar confianza de memorias
     * ==============================
     */
    private function adjustMemoryConfidence(
        AiQuestion $question,
        mixed $answer,
        AiMemoryService $memoryService,
        AiConfidenceService $confidenceService
    ): void {
        $userId = $question->user_id;

        /**
         * ✅ Caso 1: Confirmación explícita
         */
        if ($answer === true) {
            $memory = $memoryService->getMemory(
                $userId,
                'habit',
                'monthly_expense'
            );

            if ($memory) {
                $confidenceService->reinforce($memory, 5);
            }
        }

        /**
         * ❌ Caso 2: Corrección de persona
         */
        if (
            $question->question_type === 'general' &&
            is_string($answer) &&
            $question->financialEvent
        ) {
            $event = $question->financialEvent;

            if ($event->person_name && $event->person_name !== $answer) {
                $oldMemory = $memoryService->getMemory(
                    $userId,
                    'person_map',
                    $event->person_name
                );

                if ($oldMemory) {
                    $confidenceService->penalize($oldMemory, 10);
                }
            }
        }

        /**
         * 🔁 Caso 3: Uso correcto sin corrección
         */
        if (
            $question->answered_at &&
            $question->answer_type !== 'edit'
        ) {
            $memory = $memoryService->getMemory(
                $userId,
                'person_map',
                is_string($answer) ? $answer : ''
            );

            if ($memory) {
                $confidenceService->reinforce($memory, 1);
            }
        }
    }
}
