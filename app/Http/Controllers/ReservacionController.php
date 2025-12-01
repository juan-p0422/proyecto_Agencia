<?php

namespace App\Http\Controllers;

use App\Models\Reservacion;
use App\Models\Habitacion;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReservacionController extends Controller
{
    public function index()
    {
        return response()->json(
            Reservacion::with(['hotel', 'transporte', 'usuarios', 'habitacion'])->get()
        );
    }

    public function show($id)
    {
        $r = Reservacion::with(['hotel', 'transporte', 'usuarios', 'habitacion'])->find($id);
        if (!$r)
            return response()->json(['mensaje' => 'No encontrado'], 404);
        return response()->json($r);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'FechaInicio' => ['required', 'date'],
            'FechaFin' => ['required', 'date', 'after_or_equal:FechaInicio'],
            'PrecioTotal' => ['required', 'numeric', 'min:0'],
            'NumHuespedes' => ['required', 'integer', 'min:1'],
            'NumHabitaciones' => ['required', 'integer', 'min:1'],
            'IdHotel' => ['required', 'integer', 'exists:Hotel,IdHotel'],
            'IdTransporte' => ['required', 'integer', 'exists:Transporte,IdTransporte'],
            'IdHabitacion' => ['required', 'integer', 'exists:Habitacion,IdHabitacion'],
            'Estatus' => ['required', Rule::in(['Activa', 'Inactiva', 'Cancelada'])]
        ]);

        $habitacion = Habitacion::where('IdHabitacion', $data['IdHabitacion'])->first();

        if (!$habitacion) {
            return response()->json(['mensaje' => 'La habitación no existe.'], 404);
        }

        // Validar disponibilidad
        if ($habitacion->HabitacionesTotales < $data['NumHabitaciones']) {
            return response()->json([
                'mensaje' => 'No hay suficientes habitaciones disponibles.'
            ], 409);
        }

        // Restar disponibilidad
        $habitacion->HabitacionesTotales -= $data['NumHabitaciones'];
        $habitacion->save();

        // Crear reservación
        $r = Reservacion::create($data);

        return response()->json(
            $r->load(['hotel', 'transporte', 'usuarios', 'habitacion']),
            201
        );
    }

    public function update(Request $request, $id)
    {
        $r = Reservacion::find($id);
        if (!$r)
            return response()->json(['mensaje' => 'No encontrado'], 404);

        $data = $request->validate([
            'FechaInicio' => ['sometimes', 'required', 'date'],
            'FechaFin' => ['sometimes', 'required', 'date', 'after_or_equal:FechaInicio'],
            'PrecioTotal' => ['sometimes', 'required', 'numeric', 'min:0'],
            'NumHuespedes' => ['sometimes', 'required', 'integer', 'min:1'],
            'NumHabitaciones' => ['sometimes', 'required', 'integer', 'min:1'],
            'IdHotel' => ['sometimes', 'required', 'integer', 'exists:Hotel,IdHotel'],
            'IdTransporte' => ['sometimes', 'required', 'integer', 'exists:Transporte,IdTransporte'],
            'Estatus' => ['sometimes', 'required', Rule::in(['Activa', 'Inactiva', 'Cancelada'])],
            'Usuarios' => ['sometimes', 'array'],
            'Usuarios.*' => ['integer', 'exists:Usuario,IdUsuario'],
        ]);

        $r->update($data);

        if ($request->has('Usuarios')) {
            $r->usuarios()->sync($request->Usuarios ?? []);
        }

        return response()->json($r->load(['hotel', 'transporte', 'usuarios', 'habitacion']));
    }

    public function destroy($id)
    {
        $r = Reservacion::find($id);
        if (!$r)
            return response()->json(['mensaje' => 'No encontrado'], 404);

        $r->delete();
        return response()->json(['mensaje' => 'Eliminado']);
    }

    public function cancelar($id)
    {
        $r = Reservacion::find($id);

        if (!$r) {
            return response()->json(['mensaje' => 'No encontrado'], 404);
        }

        if ($r->Estatus === 'Cancelada') {
            return response()->json(['mensaje' => 'La reservación ya está cancelada'], 409);
        }

        $habitacion = Habitacion::find($r->IdHabitacion);
        $habitacion->HabitacionesTotales += $r->NumHabitaciones;
        $habitacion->save();

        $r->Estatus = 'Cancelada';
        $r->save();

        return response()->json(['mensaje' => 'Reservación cancelada'], 200);
    }
}
