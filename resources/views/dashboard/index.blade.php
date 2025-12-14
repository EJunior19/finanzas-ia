@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

<!-- TÍTULO -->
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">
        👋 Hola, Junior te ayuda con tus finanzas
    </h1>
    <p class="text-gray-500 text-sm mt-1">
        Resumen y preguntas inteligentes
    </p>
</div>

<!-- 🧠 ONBOARDING PROGRESO -->
@if(isset($onboardingProgress) && $onboardingProgress < 100)
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6">
        <p class="text-sm font-semibold text-blue-800">
            Junior te está conociendo ({{ $onboardingProgress }}%)
        </p>

        <div class="w-full bg-blue-200 rounded h-2 mt-2">
            <div class="bg-blue-600 h-2 rounded transition-all"
                 style="width: {{ $onboardingProgress }}%">
            </div>
        </div>

        <p class="text-xs text-blue-700 mt-2">
            Respondé estas preguntas para que Junior pueda ayudarte mejor.
        </p>
    </div>
@endif

<!-- RESUMEN RÁPIDO -->
<div class="grid grid-cols-2 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow p-4">
        <p class="text-sm text-gray-500">Ingresos</p>
        <p class="text-xl font-bold text-green-600">
            Gs {{ number_format($totalIncome ?? 0, 0, ',', '.') }}
        </p>
    </div>

    <div class="bg-white rounded-xl shadow p-4">
        <p class="text-sm text-gray-500">Gastos</p>
        <p class="text-xl font-bold text-red-500">
            Gs {{ number_format($totalExpenses ?? 0, 0, ',', '.') }}
        </p>
    </div>
</div>

<!-- ❓ PREGUNTA ACTUAL DE JUNIOR -->
<div class="bg-white rounded-xl shadow p-4 mb-6">
    <h2 class="font-semibold text-gray-800 mb-3">
        ❓ Junior necesita saber
    </h2>

    @if($pendingQuestions->isEmpty())
        <p class="text-sm text-gray-500">
            No hay preguntas pendientes 🎉
        </p>
    @else
        @php
            $question = $pendingQuestions->first();
        @endphp

        <div class="border rounded-lg p-4 bg-gray-50">
            <p class="text-sm text-gray-800 mb-3">
                {{ $question->question_text }}
            </p>

            <a href="{{ route('ai.inbox') }}"
               class="inline-block bg-indigo-600 text-white text-sm font-semibold px-4 py-2 rounded-lg shadow hover:bg-indigo-700 transition">
                Responder ahora →
            </a>
        </div>
    @endif
</div>

<!-- ÚLTIMOS MOVIMIENTOS -->
<div class="bg-white rounded-xl shadow p-4 mb-6">
    <h2 class="font-semibold text-gray-800 mb-3">
        📅 Últimos movimientos
    </h2>

    @if($latestEvents->isEmpty())
        <p class="text-sm text-gray-500">
            Aún no hay movimientos registrados
        </p>
    @else
        <ul class="space-y-2">
            @foreach($latestEvents as $event)
                <li class="text-sm flex justify-between">
                    <span>
                        {{ $event->category }}
                    </span>
                    <span class="font-semibold">
                        Gs {{ number_format($event->amount, 0, ',', '.') }}
                    </span>
                </li>
            @endforeach
        </ul>
    @endif
</div>

<!-- ACCIÓN RÁPIDA -->
<div class="fixed bottom-6 right-6">
    <a href="{{ route('events.create') }}"
       class="bg-indigo-600 text-white rounded-full px-5 py-3 shadow-lg text-sm font-semibold">
        + Registrar gasto
    </a>
</div>

@endsection
