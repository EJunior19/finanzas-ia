<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // database/migrations/xxxx_add_priority_to_ai_questions_table.php
        Schema::table('ai_questions', function (Blueprint $table) {
            $table->string('priority')->default('normal')->index();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_questions', function (Blueprint $table) {
            //
        });
    }
};
