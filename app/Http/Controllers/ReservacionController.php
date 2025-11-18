<?php

namespace App\Http\Controllers;

use App\Models\Reservacion;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReservacionController extends Controller
{
    public function index()
    {
        return response()->json(
            Reservacion::with(['hotel','transporte','usuarios'])->get()
        );
    }

    public function show($id)
    {
        $r = Reservacion::with(['hotel','transporte','usuarios'])->find($id);
        if (!$r) return response()->json(['mensaje' => 'No encontrado'], 404);
        return response()->json($r);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'FechaInicio' => ['required','date'],
            'FechaFin' => ['required','date','after_or_equal:FechaInicio'],
            'PrecioTotal' => ['required','numeric','min:0'],
            'NumHuespedes' => ['required','integer','min:1'],
            'NumHabitaciones' => ['required','integer','min:1'],
            'IdHotel' => ['required','integer','exists:Hotel,IdHotel'],
            'IdTransporte' => ['required','integer','exists:Transporte,IdTransporte'],
            'Estatus' => ['required', Rule::in(['Activa','Inactiva','Cancelada'])],
            // opcional: asociar usuarios
            'Usuarios' => ['sometimes','array'],
            'Usuarios.*' => ['integer','exists:Usuario,IdUsuario'],
        ]);

        $r = Reservacion::create($data);

        if ($request->filled('Usuarios')) {
            $r->usuarios()->sync($request->Usuarios);
        }

        return response()->json($r->load(['hotel','transporte','usuarios']), 201);
    }

    public function update(Request $request, $id)
    {
        $r = Reservacion::find($id);
        if (!$r) return response()->json(['mensaje' => 'No encontrado'], 404);

        $data = $request->validate([
            'FechaInicio' => ['sometimes','required','date'],
            'FechaFin' => ['sometimes','required','date','after_or_equal:FechaInicio'],
            'PrecioTotal' => ['sometimes','required','numeric','min:0'],
            'NumHuespedes' => ['sometimes','required','integer','min:1'],
            'NumHabitaciones' => ['sometimes','required','integer','min:1'],
            'IdHotel' => ['sometimes','required','integer','exists:Hotel,IdHotel'],
            'IdTransporte' => ['sometimes','required','integer','exists:Transporte,IdTransporte'],
            'Estatus' => ['sometimes','required', Rule::in(['Activa','Inactiva','Cancelada'])],
            'Usuarios' => ['sometimes','array'],
            'Usuarios.*' => ['integer','exists:Usuario,IdUsuario'],
        ]);

        $r->update($data);

        if ($request->has('Usuarios')) {
            $r->usuarios()->sync($request->Usuarios ?? []);
        }

        return response()->json($r->load(['hotel','transporte','usuarios']));
    }

    public function destroy($id)
    {
        $r = Reservacion::find($id);
        if (!$r) return response()->json(['mensaje' => 'No encontrado'], 404);
        $r->delete();
        return response()->json(['mensaje' => 'Eliminado']);
    }
}
