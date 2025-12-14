<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AIController;
use App\Http\Controllers\AIQuestionController;
use App\Http\Controllers\AIAlertController;

/*
|--------------------------------------------------------------------------
| AI – Finanzas Inteligentes
|--------------------------------------------------------------------------
| Rutas de interacción con el asistente financiero
| Todas son stateless (API)
*/

Route::prefix('ai')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | IA – Interpretación de texto
    |--------------------------------------------------------------------------
    */
    Route::post('suggest', [AIController::class, 'suggest']);

    /*
    |--------------------------------------------------------------------------
    | IA – Confirmación de eventos
    |--------------------------------------------------------------------------
    */
    Route::post('confirm', [AIController::class, 'confirm']);

    /*
    |--------------------------------------------------------------------------
    | IA – Preguntas y alertas
    |--------------------------------------------------------------------------
    */
    Route::get('questions', [AIController::class, 'pendingQuestions']);

    Route::post('questions/{question}/answer', [
        AIQuestionController::class,
        'answer'
    ]);

    /*
    |--------------------------------------------------------------------------
    | IA – Motor de alertas
    |--------------------------------------------------------------------------
    */
    Route::post('alerts/run', [AIAlertController::class, 'run']);

});
