<?php

use Illuminate\Support\Facades\Route;

// Controllers
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AIController;
use App\Http\Controllers\AIQuestionController;
use App\Http\Controllers\FinancialEventController;
use App\Http\Controllers\DebtController;
use App\Http\Controllers\AIInboxController;

/*
|--------------------------------------------------------------------------
| HOME
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return redirect('/dashboard');
});

/*
|--------------------------------------------------------------------------
| DASHBOARD
|--------------------------------------------------------------------------
*/
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->name('dashboard');

/*
|--------------------------------------------------------------------------
| IA — JUNIOR
|--------------------------------------------------------------------------
| Interpretación, confirmación y bandeja de preguntas
*/
Route::post('/api/ai/suggest', [AIController::class, 'suggest']);
Route::post('/api/ai/confirm', [AIController::class, 'confirm']);

Route::get('/ai/inbox', [AIInboxController::class, 'index'])
    ->name('ai.inbox');


/*
|--------------------------------------------------------------------------
| IA — RESPUESTAS A PREGUNTAS
|--------------------------------------------------------------------------
*/
Route::post('/ai/questions/{question}/answer', [AIQuestionController::class, 'answer'])
    ->name('ai.questions.answer');

/*
|--------------------------------------------------------------------------
| EVENTOS FINANCIEROS
|--------------------------------------------------------------------------
| Gastos, pagos e ingresos (manuales + IA)
*/
Route::get('/events', [FinancialEventController::class, 'index'])
    ->name('events.index');

Route::get('/events/create', [FinancialEventController::class, 'create'])
    ->name('events.create');

Route::post('/events', [FinancialEventController::class, 'store'])
    ->name('events.store');

Route::get('/events/{event}/edit', [FinancialEventController::class, 'edit'])
    ->name('events.edit');

Route::put('/events/{event}', [FinancialEventController::class, 'update'])
    ->name('events.update');

Route::delete('/events/{event}', [FinancialEventController::class, 'destroy'])
    ->name('events.destroy');

/*
|--------------------------------------------------------------------------
| DEUDAS
|--------------------------------------------------------------------------
*/
Route::get('/debts', [DebtController::class, 'index'])
    ->name('debts.index');

Route::get('/debts/create', [DebtController::class, 'create'])
    ->name('debts.create');

Route::post('/debts', [DebtController::class, 'store'])
    ->name('debts.store');

Route::post('/debts/{event}/pay', [DebtController::class, 'markAsPaid'])
    ->name('debts.pay');

/*
|--------------------------------------------------------------------------
| FUTURO (AUTH)
|--------------------------------------------------------------------------
| Cuando actives login solo envuelves con middleware('auth')
*/
// Route::middleware(['auth'])->group(function () {
//     ...
// });
