<?php

namespace App\Http\Controllers;

use App\Models\Habitacion;
use App\Models\Hotel;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class HabitacionController extends Controller
{
    public function index()
    {
        return response()->json(Habitacion::with('hotel')->get());
    }

    public function show($id)
    {
        $hab = Habitacion::with('hotel')->find($id);
        if (!$hab) return response()->json(['mensaje' => 'No encontrado'], 404);
        return response()->json($hab);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'IdHotel' => ['required','integer','exists:Hotel,IdHotel'],
            'TipoHabitacion' => ['required', Rule::in(['Sencilla','Doble','Suite'])],
            'Precio' => ['required','numeric','min:0'],
            'MaximoHuespedes' => ['required','integer','min:1'],
            'HabitacionesTotales' => ['required','integer','min:0'],
        ]);

        $hab = Habitacion::create($data);
        return response()->json($hab, 201);
    }

    public function update(Request $request, $id)
    {
        $hab = Habitacion::find($id);
        if (!$hab) return response()->json(['mensaje' => 'No encontrado'], 404);

        $data = $request->validate([
            'IdHotel' => ['sometimes','required','integer','exists:Hotel,IdHotel'],
            'TipoHabitacion' => ['sometimes','required', Rule::in(['Sencilla','Doble','Suite'])],
            'Precio' => ['sometimes','required','numeric','min:0'],
            'MaximoHuespedes' => ['sometimes','required','integer','min:1'],
            'HabitacionesTotales' => ['sometimes','required','integer','min:0'],
        ]);

        $hab->update($data);
        return response()->json($hab);
    }

    public function destroy($id)
    {
        $hab = Habitacion::find($id);
        if (!$hab) return response()->json(['mensaje' => 'No encontrado'], 404);
        $hab->delete();
        return response()->json(['mensaje' => 'Eliminado']);
    }

    // Opcional: disponibilidad filtrada
    public function disponibilidad(Request $request)
    {
        $data = $request->validate([
            'IdHotel' => ['required','integer','exists:Hotel,IdHotel'],
            'TipoHabitacion' => ['nullable', Rule::in(['Sencilla','Doble','Suite'])],
        ]);

        $query = Habitacion::where('IdHotel', $data['IdHotel']);
        if (isset($data['TipoHabitacion'])) {
            $query->where('TipoHabitacion', $data['TipoHabitacion']);
        }
        $habitaciones = $query->select('IdHabitacion','TipoHabitacion','HabitacionesTotales','Precio','MaximoHuespedes')->get();

        return response()->json($habitaciones);
    }
}
