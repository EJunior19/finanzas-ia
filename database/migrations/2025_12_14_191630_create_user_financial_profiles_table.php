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
        Schema::create('user_financial_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->boolean('onboarding_completed')->default(false);

            $table->decimal('monthly_income', 15, 2)->nullable();
            $table->integer('salary_day')->nullable();

            $table->json('fixed_expenses')->nullable(); // internet, alquiler, etc
            $table->json('debts')->nullable();

            $table->smallInteger('confidence')->default(0);
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_financial_profiles');
    }
};
