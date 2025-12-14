<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ai_expectations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Qué se espera
            $table->enum('expectation_type', ['income', 'expense', 'debt']);

            $table->string('category');
            $table->string('person_name')->nullable();

            // Opcional (la IA aprende)
            $table->decimal('expected_amount', 15, 2)->nullable();

            // Frecuencia
            $table->enum('frequency', ['daily', 'weekly', 'monthly', 'custom'])
                  ->default('monthly');

            // Día esperado (ej: sueldo día 5)
            $table->unsignedTinyInteger('expected_day')->nullable();

            // Última vez cumplido
            $table->date('last_fulfilled_at')->nullable();

            // Confianza del patrón
            $table->unsignedTinyInteger('confidence')->default(50);

            $table->timestamps();

            $table->index(['user_id', 'expectation_type']);
            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_expectations');
    }
};
