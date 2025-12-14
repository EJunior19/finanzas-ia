<?php

namespace App\Http\Controllers;

use App\Models\FinancialEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FinancialEventController extends Controller
{
    /**
     * 📋 Listar eventos financieros
     * (gastos, pagos, ingresos)
     */
    public function index()
    {
        $userId = 1; // 🔥 TEST (luego Auth::id())

        $events = FinancialEvent::where('user_id', $userId)
            ->whereIn('event_type', ['expense', 'payment', 'income'])
            ->orderByDesc('event_date')
            ->orderByDesc('created_at')
            ->get();

        Log::info('📋 Eventos financieros cargados', [
            'user_id' => $userId,
            'count'   => $events->count(),
        ]);

        return view('events.list', [
            'events' => $events,
            'title'  => 'Movimientos financieros',
        ]);
    }

    /**
     * ➕ Formulario nuevo evento
     */
    public function create()
    {
        return view('events.create', [
            'title' => 'Registrar movimiento',
            'type'  => 'generic',
        ]);
    }

    /**
     * 💾 Guardar evento financiero
     */
    public function store(Request $request)
    {
        $userId = 1; // 🔥 TEST

        $data = $request->validate([
            'event_type'  => 'required|in:expense,payment,income',
            'category'    => 'required|string|max:255',
            'amount'      => 'nullable|numeric|min:0',
            'person_name' => 'nullable|string|max:255',
            'event_date'  => 'nullable|date',
            'description' => 'nullable|string|max:500',
        ]);

        $status = match ($data['event_type']) {
            'income'  => 'completed',
            'payment' => 'completed',
            'expense' => 'completed',
        };

        $event = FinancialEvent::create([
            'user_id'     => $userId,
            'event_type'  => $data['event_type'],
            'status'      => $status,
            'amount'      => $data['amount'] ?? null,
            'category'    => $data['category'],
            'person_name' => $data['person_name'] ?? null,
            'event_date'  => $data['event_date'] ?? now()->toDateString(),
            'description' => $data['description'] ?? null,
            'confidence'  => 80,
            'source'      => 'manual',
        ]);

        Log::info('💾 Evento financiero creado', [
            'event_id' => $event->id,
            'type'     => $event->event_type,
        ]);

        return redirect('/events')
            ->with('success', 'Movimiento registrado correctamente');
    }

    /**
     * ✏️ Editar evento
     */
    public function edit(FinancialEvent $event)
    {
        return view('events.create', [
            'event' => $event,
            'title' => 'Editar movimiento',
            'type'  => 'generic',
        ]);
    }

    /**
     * 🔄 Actualizar evento
     */
    public function update(Request $request, FinancialEvent $event)
    {
        $data = $request->validate([
            'category'    => 'required|string|max:255',
            'amount'      => 'nullable|numeric|min:0',
            'person_name' => 'nullable|string|max:255',
            'event_date'  => 'nullable|date',
            'description' => 'nullable|string|max:500',
        ]);

        $event->update($data);

        Log::info('✏️ Evento actualizado', [
            'event_id' => $event->id,
        ]);

        return redirect('/events')
            ->with('success', 'Movimiento actualizado');
    }

    /**
     * 🗑️ Eliminar evento
     */
    public function destroy(FinancialEvent $event)
    {
        $event->delete();

        Log::info('🗑️ Evento eliminado', [
            'event_id' => $event->id,
        ]);

        return redirect('/events')
            ->with('success', 'Movimiento eliminado');
    }
}
