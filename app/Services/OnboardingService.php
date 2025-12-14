<?php

namespace App\Services;

use App\Models\AiQuestion;
use App\Models\UserFinancialProfile;

class OnboardingService
{
    /**
     * Inicia el onboarding SOLO si:
     * - el usuario no completó onboarding
     * - y todavía no existen preguntas onboarding
     */
    public static function start(int $userId): void
    {
        // 1️⃣ Obtener o crear perfil financiero
        $profile = UserFinancialProfile::firstOrCreate(
            ['user_id' => $userId],
            ['onboarding_completed' => false]
        );

        // 2️⃣ Si ya terminó onboarding → NO preguntar
        if ($profile->onboarding_completed === true) {
            return;
        }

        // 3️⃣ Si ya existen preguntas onboarding → NO duplicar
        $alreadyAsked = AiQuestion::where('user_id', $userId)
            ->where('question_type', 'like', 'onboarding_%')
            ->exists();

        if ($alreadyAsked) {
            return;
        }

        // 4️⃣ Crear preguntas base
        self::createBaseQuestions($userId);
    }

    /**
     * Preguntas base obligatorias para conocer al usuario
     */
    protected static function createBaseQuestions(int $userId): void
    {
        $questions = [
            [
                'type' => 'onboarding_income',
                'text' => '¿Cuánto es tu ingreso mensual aproximado?',
            ],
            [
                'type' => 'onboarding_salary_day',
                'text' => '¿Qué día del mes cobrás normalmente?',
            ],
            [
                'type' => 'onboarding_fixed_expenses',
                'text' => '¿Tenés gastos fijos como alquiler, internet o servicios?',
            ],
            [
                'type' => 'onboarding_debts',
                'text' => '¿Tenés deudas activas actualmente?',
            ],
        ];

        foreach ($questions as $q) {
            AiQuestion::create([
                'user_id'            => $userId,
                'financial_event_id' => null,
                'question_type'      => $q['type'],
                'question_text'      => $q['text'],
                'priority'           => 'urgent',
            ]);
        }
    }
}
