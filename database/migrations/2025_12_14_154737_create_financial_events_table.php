<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('financial_events', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Tipo de evento financiero
            $table->enum('event_type', ['income', 'expense', 'debt', 'payment']);

            // Estado del evento
            $table->enum('status', ['expected', 'pending', 'completed', 'overdue'])
                  ->default('pending');

            // Monto siempre positivo
            $table->decimal('amount', 15, 2)->nullable();

            // Clasificación
            $table->string('category');
            $table->string('person_name')->nullable();

            // Fechas
            $table->date('event_date')->nullable();
            $table->date('due_date')->nullable();

            // Confianza de la IA (0–100)
            $table->unsignedTinyInteger('confidence')->default(50);

            // Origen del evento
            $table->enum('source', ['ai', 'user', 'system'])->default('ai');

            $table->text('description')->nullable();

            $table->timestamps();

            // Índices importantes para IA
            $table->index(['user_id', 'event_type']);
            $table->index(['status', 'due_date']);
            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_events');
    }
};
