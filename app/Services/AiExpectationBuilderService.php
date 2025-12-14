<?php

namespace App\Services;

use App\Models\AiExpectation;
use App\Models\FinancialEvent;
use Illuminate\Support\Facades\Log;

class AiExpectationBuilderService
{
    /**
     * Analiza un evento recién confirmado
     * y decide si debe crear/actualizar una expectativa
     */
    public function analyzeEvent(FinancialEvent $event): void
    {
        // Solo eventos con mínima confianza
        if ($event->confidence < 60) {
            return;
        }

        // Solo tipos que tienen sentido predecir
        if (!in_array($event->event_type, ['income', 'expense', 'debt'])) {
            return;
        }

        // Buscar eventos similares anteriores
        $similarEvents = FinancialEvent::where('user_id', $event->user_id)
            ->where('event_type', $event->event_type)
            ->where('category', $event->category)
            ->when(
                $event->person_name,
                fn ($q) => $q->where('person_name', $event->person_name)
            )
            ->where('id', '!=', $event->id)
            ->orderBy('event_date', 'desc')
            ->limit(3)
            ->get();

        // Necesitamos al menos 1 previo (2 en total)
        if ($similarEvents->count() < 1) {
            return;
        }

        // Calcular día promedio
        $days = collect([$event])
            ->merge($similarEvents)
            ->pluck('event_date')
            ->filter()
            ->map(fn ($d) => (int) date('d', strtotime($d)));

        $expectedDay = $days->count() ? round($days->avg()) : null;

        // Buscar expectativa existente
        $expectation = AiExpectation::where('user_id', $event->user_id)
            ->where('expectation_type', $event->event_type)
            ->where('category', $event->category)
            ->when(
                $event->person_name,
                fn ($q) => $q->where('person_name', $event->person_name)
            )
            ->first();

        if ($expectation) {
            // 🔁 Ajustar expectativa existente
            $expectation->update([
                'expected_amount' => $event->amount ?? $expectation->expected_amount,
                'expected_day'    => $expectedDay ?? $expectation->expected_day,
                'confidence'      => min($expectation->confidence + 5, 100),
            ]);

            Log::info('🔁 Expectativa actualizada', [
                'expectation_id' => $expectation->id,
            ]);
        } else {
            // ✨ Crear nueva expectativa
            $expectation = AiExpectation::create([
                'user_id'           => $event->user_id,
                'expectation_type'  => $event->event_type,
                'category'          => $event->category,
                'person_name'       => $event->person_name,
                'expected_amount'   => $event->amount,
                'frequency'         => 'monthly',
                'expected_day'      => $expectedDay,
                'confidence'        => 60,
            ]);

            Log::info('✨ Nueva expectativa creada', [
                'expectation_id' => $expectation->id,
            ]);
        }
    }
}
