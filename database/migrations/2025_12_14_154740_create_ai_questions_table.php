<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ai_questions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Tipo de pregunta
            $table->enum('question_type', [
                'confirm_payment',
                'missing_income',
                'categorize',
                'amount_check',
                'general'
            ]);

            $table->foreignId('financial_event_id')
                  ->nullable()
                  ->constrained('financial_events')
                  ->nullOnDelete();

            $table->text('question_text');

            // Respuesta del usuario
            $table->enum('answer_type', ['yes', 'no', 'edit', 'skip'])->nullable();
            $table->json('answer_payload')->nullable();

            $table->timestamp('asked_at')->useCurrent();
            $table->timestamp('answered_at')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'answered_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_questions');
    }
};
