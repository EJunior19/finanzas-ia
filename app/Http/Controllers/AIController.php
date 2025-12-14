<?php

namespace App\Http\Controllers;

use App\Services\FinanceAIService;
use App\Services\AiQuestionService;
use App\Services\AiExpectationBuilderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\FinancialEvent;
use App\Models\AiQuestion;

class AIController extends Controller
{
    /**
     * PASO 1
     * IA interpreta texto y devuelve sugerencia
     * 🔥 DEBUG ESTABLE
     */
    public function suggest(Request $request, FinanceAIService $ai)
    {
        Log::info('🧠 AIController@suggest — REQUEST RECIBIDO', [
            'headers' => $request->headers->all(),
            'raw'     => $request->getContent(),
        ]);

        try {
            // 🔹 Leer body crudo
            $raw = $request->getContent();

            // 🔑 Normalizar UTF-8 (Windows fix)
            $raw = mb_convert_encoding($raw, 'UTF-8', 'UTF-8');

            // 🔹 Intentar JSON
            $rawData = json_decode($raw, true);

            // 🔄 Fallback seguro
            $text = $rawData['text']
                ?? $request->input('text')
                ?? null;

            Log::info('✍️ Texto recibido', [
                'text' => $text,
                'type' => gettype($text),
            ]);

            if (!is_string($text) || trim($text) === '') {
                Log::warning('⚠️ Texto vacío o inválido');

                return response()->json([
                    'error' => 'Campo text es requerido',
                ], 422);
            }

            // 🔥 Usuario forzado (TEST)
            $userId = 1;

            Log::info('🤖 Llamando a FinanceAIService', [
                'user_id' => $userId,
                'text'    => $text,
            ]);

            $result = $ai->suggestFromText($userId, $text);

            Log::info('✅ Respuesta de FinanceAIService', [
                'result' => $result,
            ]);

            return response()->json(
                $result,
                200,
                [],
                JSON_UNESCAPED_UNICODE
            );

        } catch (\Throwable $e) {

            Log::error('❌ ERROR EN AIController@suggest', [
                'message' => $e->getMessage(),
                'type'    => get_class($e),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            return response()->json([
                'error'   => 'Exception en suggest',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * PASO 2
     * Confirmar evento + generar preguntas + aprendizaje
     */
    public function confirm(
        Request $request,
        AiQuestionService $questions,
        AiExpectationBuilderService $expectations
    ) {
        Log::info('📌 AIController@confirm — REQUEST', [
            'raw' => $request->getContent(),
        ]);

        $data = json_decode($request->getContent(), true) ?? [];

        if (
            !isset($data['event']) ||
            !isset($data['event']['event_type']) ||
            !isset($data['event']['category'])
        ) {
            Log::warning('⚠️ Estructura inválida', $data);

            return response()->json([
                'error' => 'Estructura de evento inválida',
            ], 422);
        }

        $userId = 1; // TEST

        $event = FinancialEvent::create([
            'user_id'     => $userId,
            'event_type'  => $data['event']['event_type'],
            'status'      => $this->resolveStatus($data),
            'amount'      => $data['event']['amount'] ?? null,
            'category'    => $data['event']['category'],
            'person_name' => $data['event']['person_name'] ?? null,
            'event_date'  => $data['event']['event_date'] ?? null,
            'due_date'    => $data['event']['due_date'] ?? null,
            'description' => $data['event']['description'] ?? null,
            'confidence'  => $data['confidence'] ?? 50,
            'source'      => 'ai',
        ]);

        Log::info('🧩 Evento creado', [
            'event_id' => $event->id,
        ]);

        // 🔹 PASO 2A: Generar preguntas automáticas
        $questions->generateForEvent($event);

        Log::info('❓ Preguntas generadas', [
            'event_id' => $event->id,
        ]);

        // 🔹 PASO 3: Aprendizaje → construir expectativas
        $expectations->analyzeEvent($event);

        Log::info('🧠 Expectativas analizadas', [
            'event_id' => $event->id,
        ]);

        return response()->json([
            'status'   => 'confirmed',
            'event_id' => $event->id,
        ]);
    }

    /**
     * PASO 3
     * Preguntas pendientes ORDENADAS POR PRIORIDAD
     */
    public function pendingQuestions()
    {
        $userId = 1; // TEST

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
            ->orderBy('asked_at')
            ->get();

        Log::info('📋 Preguntas pendientes', [
            'count' => $questions->count(),
        ]);

        return $questions;
    }

    /**
     * Resolver estado del evento
     */
    private function resolveStatus(array $data): string
    {
        $type = $data['event']['event_type'] ?? null;
        $due  = $data['event']['due_date'] ?? null;

        return match ($type) {
            'debt'    => 'pending',
            'payment' => 'completed',
            default   => $due && now()->toDateString() > $due
                ? 'overdue'
                : 'completed',
        };
    }
    public function inbox()
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
            ->orderBy('asked_at')
            ->get();

        return view('ai.inbox', [
            'questions' => $questions
        ]);
    }

}
