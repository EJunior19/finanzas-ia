<?php

namespace App\Services;

use App\Models\AiQuestion;
use App\Models\FinancialEvent;
use App\Models\AiExpectation;
use Carbon\Carbon;

class AiAlertService
{
    public function runForUser(int $userId): void
    {
        $this->checkDueEvents($userId);
        $this->checkMissingIncomes($userId);
    }

    /**
     * 🔔 Eventos vencidos o por vencer
     */
    private function checkDueEvents(int $userId): void
    {
        $events = FinancialEvent::where('user_id', $userId)
            ->whereIn('status', ['pending', 'overdue'])
            ->whereNotNull('due_date')
            ->get();

        foreach ($events as $event) {
            if ($this->alreadyAsked($event->id, 'confirm_payment')) {
                continue;
            }

            if (Carbon::now()->greaterThanOrEqualTo($event->due_date)) {
                AiQuestion::create([
                    'user_id'            => $userId,
                    'financial_event_id' => $event->id,
                    'question_type'      => 'confirm_payment',
                    'question_text'      => "¿Ya pagaste {$event->category}?",
                ]);
            }
        }
    }

    /**
     * 💰 Ingresos esperados no registrados
     */
    private function checkMissingIncomes(int $userId): void
    {
        $expectations = AiExpectation::where('user_id', $userId)
            ->where('expectation_type', 'income')
            ->get();

        foreach ($expectations as $exp) {
            if ($this->alreadyAsked(null, 'missing_income', $exp->category)) {
                continue;
            }

            if (
                $exp->expected_day &&
                now()->day >= $exp->expected_day &&
                (!$exp->last_fulfilled_at ||
                 now()->diffInDays($exp->last_fulfilled_at) > 25)
            ) {
                AiQuestion::create([
                    'user_id'       => $userId,
                    'question_type' => 'missing_income',
                    'question_text'=> "¿Ya cobraste {$exp->category} este mes?",
                ]);
            }
        }
    }

    /**
     * 🛑 Evitar spam
     */
    private function alreadyAsked(
        ?int $eventId,
        string $type,
        ?string $category = null
    ): bool {
        return AiQuestion::where('question_type', $type)
            ->whereNull('answered_at')
            ->when($eventId, fn ($q) =>
                $q->where('financial_event_id', $eventId)
            )
            ->when($category, fn ($q) =>
                $q->where('question_text', 'like', "%{$category}%")
            )
            ->exists();
    }
}
