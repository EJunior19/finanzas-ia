@extends('layouts.app')

@section('title', 'Nuevo movimiento')

@section('content')

<div class="max-w-xl mx-auto">

    <!-- HEADER -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">
            ➕ Nuevo movimiento
        </h1>
        <p class="text-sm text-gray-500 mt-1">
            Registrá un gasto, ingreso, pago o deuda
        </p>
    </div>

    <!-- FORM -->
    <form method="POST" action="{{ route('events.store') }}">
        @csrf

        <!-- TIPO -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Tipo
            </label>
            <select name="event_type"
                    class="w-full border rounded-lg px-3 py-2 text-sm"
                    required>
                <option value="">Seleccionar</option>
                <option value="expense">Gasto</option>
                <option value="income">Ingreso</option>
                <option value="payment">Pago</option>
                <option value="debt">Deuda</option>
            </select>
        </div>

        <!-- MONTO -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Monto (Gs)
            </label>

            <!-- Visible -->
            <input type="text"
                   id="amount_display"
                   class="w-full border rounded-lg px-3 py-2 text-sm"
                   placeholder="Ej: 150.000"
                   inputmode="numeric"
                   autocomplete="off"
                   required>

            <!-- Real (backend) -->
            <input type="hidden" name="amount" id="amount">
        </div>

        <!-- CATEGORÍA -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Categoría
            </label>
            <input type="text"
                   name="category"
                   class="w-full border rounded-lg px-3 py-2 text-sm"
                   placeholder="internet, comida, combustible…"
                   required>
        </div>

        <!-- PERSONA -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Persona / Entidad (opcional)
            </label>
            <input type="text"
                   name="person_name"
                   class="w-full border rounded-lg px-3 py-2 text-sm"
                   placeholder="Personal, Banco, Juan…">
        </div>

        <!-- FECHA EVENTO -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Fecha del evento
            </label>

            <!-- Visible -->
            <input type="text"
                   id="event_date_display"
                   class="w-full border rounded-lg px-3 py-2 text-sm"
                   placeholder="dd/mm/aaaa"
                   value="{{ now()->format('d/m/Y') }}">

            <!-- Real -->
            <input type="hidden"
                   name="event_date"
                   id="event_date"
                   value="{{ now()->toDateString() }}">
        </div>

        <!-- FECHA VENCIMIENTO -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Fecha de vencimiento (opcional)
            </label>

            <input type="text"
                   id="due_date_display"
                   class="w-full border rounded-lg px-3 py-2 text-sm"
                   placeholder="dd/mm/aaaa">

            <input type="hidden"
                   name="due_date"
                   id="due_date">
        </div>

        <!-- DESCRIPCIÓN -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Descripción (opcional)
            </label>
            <textarea name="description"
                      rows="3"
                      class="w-full border rounded-lg px-3 py-2 text-sm"
                      placeholder="Detalle adicional…"></textarea>
        </div>

        <!-- BOTONES -->
        <div class="flex gap-3">
            <button type="submit"
                    class="flex-1 bg-indigo-600 text-white py-2 rounded-lg font-semibold">
                Guardar
            </button>

            <a href="{{ route('dashboard') }}"
               class="flex-1 text-center bg-gray-200 text-gray-700 py-2 rounded-lg font-semibold">
                Cancelar
            </a>
        </div>
    </form>
</div>

<!-- =========================
     JS FORMATEO LOCAL
========================= -->
<script>
/**
 * 💰 Formatear monto: 150.000.000
 */
const amountDisplay = document.getElementById('amount_display');
const amountInput   = document.getElementById('amount');

amountDisplay.addEventListener('input', () => {
    let value = amountDisplay.value.replace(/\D/g, '');
    if (!value) {
        amountInput.value = '';
        return;
    }

    amountInput.value = value;
    amountDisplay.value = Number(value).toLocaleString('es-PY');
});

/**
 * 📅 dd/mm/yyyy → yyyy-mm-dd
 */
function bindDate(displayId, hiddenId) {
    const display = document.getElementById(displayId);
    const hidden  = document.getElementById(hiddenId);

    display.addEventListener('blur', () => {
        const parts = display.value.split('/');
        if (parts.length === 3) {
            const [d, m, y] = parts;
            if (d && m && y) {
                hidden.value = `${y}-${m.padStart(2,'0')}-${d.padStart(2,'0')}`;
            }
        }
    });
}

bindDate('event_date_display', 'event_date');
bindDate('due_date_display', 'due_date');
</script>

@endsection
