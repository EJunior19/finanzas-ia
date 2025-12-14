<?php

namespace App\Http\Controllers;

use App\Models\AiQuestion;

class AIInboxController extends Controller
{
    /**
     * Vista HTML de preguntas pendientes
     */
    public function index()
    {
        $userId = 1; // luego Auth::id()

        $questions = AiQuestion::where('user_id', $userId)
            ->whereNull('answered_at')
            ->orderByRaw("
                CASE priority
                    WHEN 'urgent' THEN 1
                    WHEN 'normal' THEN 2
                    WHEN 'insight' THEN 3
                    ELSE 4
                END
            ")
            ->orderBy('id')
            ->get();

        return view('ai.inbox', [
            'questions' => $questions
        ]);
    }
}
