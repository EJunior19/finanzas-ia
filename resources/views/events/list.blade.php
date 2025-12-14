@extends('layouts.app')

@section('title', 'Movimientos')

@section('content')

<div class="max-w-5xl mx-auto">

    <!-- HEADER -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">
                📋 Movimientos
            </h1>
            <p class="text-sm text-gray-500">
                Historial de gastos, ingresos y pagos
            </p>
        </div>

        <a href="/events/create"
           class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-semibold">
            ➕ Nuevo
        </a>
    </div>

    <!-- LISTA -->
    <div class="bg-white shadow rounded-lg overflow-hidden">

        <table class="w-full text-sm">
            <thead class="bg-gray-100 text-gray-600">
                <tr>
                    <th class="px-4 py-3 text-left">Fecha</th>
                    <th class="px-4 py-3 text-left">Tipo</th>
                    <th class="px-4 py-3 text-left">Categoría</th>
                    <th class="px-4 py-3 text-left">Persona</th>
                    <th class="px-4 py-3 text-right">Monto</th>
                    <th class="px-4 py-3 text-center">Estado</th>
                </tr>
            </thead>

            <tbody>
                @forelse($events as $event)
                    <tr class="border-t hover:bg-gray-50">

                        <!-- FECHA -->
                        <td class="px-4 py-3">
                            {{ optional($event->event_date)->format('d/m/Y') }}
                        </td>

                        <!-- TIPO -->
                        <td class="px-4 py-3 capitalize">
                            @switch($event->event_type)
                                @case('expense') 💸 Gasto @break
                                @case('income') 💰 Ingreso @break
                                @case('payment') 💳 Pago @break
                                @case('debt') 📌 Deuda @break
                            @endswitch
                        </td>

                        <!-- CATEGORÍA -->
                        <td class="px-4 py-3 font-medium">
                            {{ $event->category }}
                        </td>

                        <!-- PERSONA -->
                        <td class="px-4 py-3 text-gray-600">
                            {{ $event->person_name ?? '—' }}
                        </td>

                        <!-- MONTO -->
                        <td class="px-4 py-3 text-right font-semibold">
                            {{ number_format($event->amount ?? 0, 0, ',', '.') }} Gs
                        </td>

                        <!-- ESTADO -->
                        <td class="px-4 py-3 text-center">
                            @switch($event->status)
                                @case('completed')
                                    <span class="text-green-600 font-semibold">
                                        ✔
                                    </span>
                                    @break

                                @case('pending')
                                    <span class="text-yellow-600 font-semibold">
                                        ⏳
                                    </span>
                                    @break

                                @case('overdue')
                                    <span class="text-red-600 font-semibold">
                                        ⚠
                                    </span>
                                    @break
                            @endswitch
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-gray-500">
                            No hay movimientos registrados todavía.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

    </div>

</div>

@endsection
