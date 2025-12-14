<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ai_memory_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Tipo de memoria
            $table->enum('memory_type', [
                'person_map',
                'category_rule',
                'habit',
                'alias'
            ]);

            // Clave aprendida (ej: "Juan", "sueldo")
            $table->string('key');

            // Datos aprendidos
            $table->json('value');

            // Nivel de certeza
            $table->unsignedTinyInteger('confidence')->default(50);

            $table->timestamp('last_used_at')->nullable();

            $table->timestamps();

            $table->unique(['user_id', 'memory_type', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_memory_items');
    }
};
