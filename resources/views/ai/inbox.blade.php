@extends('layouts.app')

@section('title', 'Preguntas de Junior')

@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">
        ❓ Junior quiere saber
    </h1>
    <p class="text-gray-500 text-sm mt-1">
        Respondé para que pueda ayudarte mejor
    </p>
</div>

@if($questions->isEmpty())
    <div class="bg-green-50 border border-green-200 rounded-xl p-4">
        <p class="text-sm text-green-700">
            🎉 No hay preguntas pendientes
        </p>
    </div>
@else
    <div class="space-y-4">
        @foreach($questions as $question)
            <div class="bg-white rounded-xl shadow p-4">
                <p class="text-sm text-gray-800 mb-3">
                    {{ $question->question_text }}
                </p>

                <form method="POST"
                      action="{{ route('ai.questions.answer', $question) }}">
                    @csrf

                    <input
                        type="text"
                        name="answer"
                        required
                        class="w-full border rounded-lg px-3 py-2 text-sm mb-3"
                        placeholder="Escribí tu respuesta…">

                    <button
                        type="submit"
                        class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-semibold">
                        Enviar respuesta
                    </button>
                </form>
            </div>
        @endforeach
    </div>
@endif

@endsection
