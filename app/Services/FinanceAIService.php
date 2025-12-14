<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class FinanceAIService
{
    public function suggestFromText(int $userId, string $text, array $context = []): array
    {
        $apiKey = config('services.openai.key');
        $model  = config('services.openai.model', env('OPENAI_MODEL', 'gpt-4o-mini'));

        if (!$apiKey) {
            throw new \RuntimeException('Falta OPENAI_API_KEY en .env');
        }

        // 🔑 NORMALIZAR UTF-8 (CLAVE EN WINDOWS)
        $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');

        $today = now()->toDateString();

        /*
        |--------------------------------------------------------------------------
        | JSON Schema (Structured Outputs)
        |--------------------------------------------------------------------------
        */
        $schema = [
            "type" => "object",
            "additionalProperties" => false,
            "properties" => [
                "kind" => [
                    "type" => "string",
                    "enum" => ["suggestion", "question", "reject"]
                ],
                "confidence" => [
                    "type" => "integer",
                    "minimum" => 0,
                    "maximum" => 100
                ],

                "event" => [
                    "type" => ["object", "null"],
                    "additionalProperties" => false,
                    "properties" => [
                        "event_type"   => [
                            "type" => "string",
                            "enum" => ["income", "expense", "debt", "payment"]
                        ],
                        "amount"       => ["type" => ["number", "null"]],
                        "category"     => ["type" => "string"],
                        "person_name"  => ["type" => ["string", "null"]],
                        "event_date"   => ["type" => ["string", "null"]],
                        "due_date"     => ["type" => ["string", "null"]],
                        "description"  => ["type" => ["string", "null"]],
                    ],
                    "required" => [
                        "event_type",
                        "amount",
                        "category",
                        "person_name",
                        "event_date",
                        "due_date",
                        "description"
                    ],
                ],

                "questions" => [
                    "type" => "array",
                    "items" => [
                        "type" => "object",
                        "additionalProperties" => false,
                        "properties" => [
                            "field" => [
                                "type" => "string",
                                "enum" => [
                                    "event_type",
                                    "amount",
                                    "category",
                                    "person_name",
                                    "event_date",
                                    "due_date"
                                ]
                            ],
                            "question" => ["type" => "string"],
                        ],
                        "required" => ["field", "question"],
                    ]
                ],

                "notes" => ["type" => ["string", "null"]],
            ],
            "required" => ["kind", "confidence", "event", "questions", "notes"],
        ];

        /*
        |--------------------------------------------------------------------------
        | ✅ PAYLOAD CORRECTO PARA /v1/responses (OPCIÓN A)
        |--------------------------------------------------------------------------
        */
        $payload = [
            "model" => $model,
            "store" => false,

            // 🔥 CLAVE: input como TEXTO PLANO
            "input" =>
                $this->systemPrompt($today)
                . "\n\n"
                . $this->userPrompt($userId, $text, $context),

            "text" => [
                "format" => [
                    "type" => "json_schema",
                    "name" => "finance_suggestion",
                    "strict" => true,
                    "schema" => $schema,
                ],
            ],
        ];

        $resp = Http::withToken($apiKey)
            ->acceptJson()
            ->contentType('application/json; charset=utf-8')
            ->post('https://api.openai.com/v1/responses', $payload);

        if (!$resp->successful()) {
            $body = $resp->json();
            $msg = $body['error']['message'] ?? $resp->body();
            throw new \RuntimeException("OpenAI error: " . $msg);
        }

        $data = $resp->json();

        // Extraer texto estructurado
        $jsonText = $this->extractAssistantText($data);

        // 🔑 LIMPIAR UTF-8 DE LA RESPUESTA
        $jsonText = mb_convert_encoding($jsonText, 'UTF-8', 'UTF-8');

        $parsed = json_decode($jsonText, true);

        if (!is_array($parsed)) {
            throw new \RuntimeException(
                "No pude parsear JSON de la IA. Texto: " . Str::limit($jsonText, 200)
            );
        }

        return $parsed;
    }

    private function systemPrompt(string $today): string
    {
        return <<<SYS
Eres un asistente financiero personal para registrar y controlar finanzas.
Tu trabajo: interpretar el texto del usuario y devolver una SUGERENCIA estructurada (NO confirmes ni ejecutes nada).

Reglas:
- Moneda principal: Guaraní (Gs).
- Si faltan datos, genera preguntas claras y cortas.
- NUNCA inventes montos, fechas ni personas.
- Si el usuario dice "hoy", "ayer", "mañana", conviértelo a YYYY-MM-DD usando HOY={$today}.
- Categorías sugeridas: sueldo, extra, alquiler, combustible, internet, comida, tarjeta, banco, cuota, deuda, pago.
- Si el texto no es financiero, responde kind="reject" y explica en notes.

Devuelve SOLO el JSON que pide el schema.
SYS;
    }

    private function userPrompt(int $userId, string $text, array $context): string
    {
        $ctx = empty($context)
            ? "{}"
            : json_encode($context, JSON_UNESCAPED_UNICODE);

        return <<<USR
Usuario={$userId}
Contexto={$ctx}

Texto:
{$text}

Interpreta y devuelve una sugerencia para registrar el evento.
USR;
    }

    /**
     * Extrae el texto JSON del response de OpenAI Responses API
     */
    private function extractAssistantText(array $response): string
    {
        $output = $response['output'] ?? [];

        foreach ($output as $item) {
            if (($item['type'] ?? null) === 'message') {
                foreach ($item['content'] ?? [] as $c) {
                    if (($c['type'] ?? null) === 'output_text' && isset($c['text'])) {
                        return $c['text'];
                    }
                    if (isset($c['text']) && is_string($c['text'])) {
                        return $c['text'];
                    }
                }
            }
        }

        if (isset($response['output_text']) && is_string($response['output_text'])) {
            return $response['output_text'];
        }

        throw new \RuntimeException('No encontré texto de salida en la respuesta de OpenAI.');
    }
}
