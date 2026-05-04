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
        $usuarioActual = auth()->user();
        
        $reservaciones = Reservacion::whereHas('usuarios', function($query) use ($usuarioActual) {
            $query->where('Usuario_Reservacion.IdUsuario', $usuarioActual->IdUsuario);
        })->with(['hotel', 'transporte', 'usuarios', 'habitacion'])->get();

        return response()->json($reservaciones);
    }

    public function show($id)
    {
        $r = Reservacion::with(['hotel', 'transporte', 'usuarios', 'habitacion'])->find($id);
        if (!$r) return response()->json(['mensaje' => 'No encontrado'], 404);

        $usuarioActual = auth()->user();
        if (!$r->usuarios->contains('IdUsuario', $usuarioActual->IdUsuario)) {
            return response()->json(['mensaje' => 'Acceso denegado. No es tu reservación.'], 403);
        }

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

        if ($habitacion->HabitacionesTotales < $data['NumHabitaciones']) {
            return response()->json(['mensaje' => 'No hay suficientes habitaciones disponibles.'], 409);
        }

        $habitacion->HabitacionesTotales -= $data['NumHabitaciones'];
        $habitacion->save();

        $r = Reservacion::create($data);

        $usuarioActual = auth()->user();
        $r->usuarios()->attach($usuarioActual->IdUsuario);

        return response()->json($r->load(['hotel', 'transporte', 'usuarios', 'habitacion']), 201);
    }

    public function cancelar($id)
    {
        $r = Reservacion::with('usuarios')->find($id);

        if (!$r) {
            return response()->json(['mensaje' => 'No encontrado'], 404);
        }

        $usuarioActual = auth()->user();
        if (!$r->usuarios->contains('IdUsuario', $usuarioActual->IdUsuario)) {
            return response()->json(['mensaje' => 'Acceso denegado. Esta reservación no te pertenece.'], 403);
        }

        if ($r->Estatus === 'Cancelada') {
            return response()->json(['mensaje' => 'La reservación ya está cancelada'], 409);
        }

        $habitacion = Habitacion::find($r->IdHabitacion);
        if ($habitacion) {
            $habitacion->HabitacionesTotales += $r->NumHabitaciones;
            $habitacion->save();
        }

        $r->Estatus = 'Cancelada';
        $r->save();

        return response()->json(['mensaje' => 'Reservación cancelada'], 200);
    }
}
