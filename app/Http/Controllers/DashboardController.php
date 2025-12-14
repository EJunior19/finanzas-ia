<?php

namespace App\Http\Controllers;

use App\Models\FinancialEvent;
use App\Models\AiQuestion;
use App\Services\OnboardingService;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    /**
     * Dashboard principal
     */
    public function index()
    {
        // 🔥 FORZADO PARA TEST (luego Auth::id())
        $userId = 1;

        // =========================
        // 🚀 INICIAR ONBOARDING IA
        // =========================
        // Si el usuario todavía no tiene perfil financiero,
        // Junior crea las preguntas básicas automáticamente
        OnboardingService::start($userId);

        // =========================
        // 📊 RESÚMENES PRINCIPALES
        // =========================

        $totalExpenses = FinancialEvent::where('user_id', $userId)
            ->where('event_type', 'expense')
            ->sum('amount');

        $totalIncome = FinancialEvent::where('user_id', $userId)
            ->where('event_type', 'income')
            ->sum('amount');

        $totalPayments = FinancialEvent::where('user_id', $userId)
            ->where('event_type', 'payment')
            ->sum('amount');

        $pendingDebts = FinancialEvent::where('user_id', $userId)
            ->whereIn('status', ['pending', 'overdue'])
            ->count();

        // =========================
        // 📅 MOVIMIENTOS RECIENTES
        // =========================

        $latestEvents = FinancialEvent::where('user_id', $userId)
            ->orderByDesc('event_date')
            ->limit(5)
            ->get();

        // =========================
        // ❓ PREGUNTAS DE JUNIOR
        // =========================
        // Incluye preguntas de onboarding + preguntas normales
        $pendingQuestions = AiQuestion::where('user_id', $userId)
            ->whereNull('answered_at')
            ->orderByRaw("
                CASE priority
                    WHEN 'urgent' THEN 1
                    WHEN 'normal' THEN 2
                    WHEN 'insight' THEN 3
                    ELSE 4
                END
            ")
            ->limit(3)
            ->get();

        // =========================
        // 📝 LOG DE CONTROL
        // =========================
        Log::info('📊 Dashboard cargado', [
            'user_id'            => $userId,
            'expenses'           => $totalExpenses,
            'income'             => $totalIncome,
            'payments'           => $totalPayments,
            'pending_debts'      => $pendingDebts,
            'pending_questions'  => $pendingQuestions->count(),
        ]);

        // =========================
        // 🖥️ RENDER DASHBOARD
        // =========================
        return view('dashboard.index', [
            'totalExpenses'     => $totalExpenses,
            'totalIncome'       => $totalIncome,
            'totalPayments'     => $totalPayments,
            'pendingDebts'      => $pendingDebts,
            'latestEvents'      => $latestEvents,
            'pendingQuestions'  => $pendingQuestions,
        ]);
    }
}
