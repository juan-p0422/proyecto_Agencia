<?php

namespace App\Http\Controllers;

use App\Models\Descuento;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DescuentoController extends Controller
{
    public function index()
    {
        return response()->json(Descuento::all());
    }

    public function show($id)
    {
        $d = Descuento::find($id);
        if (!$d) return response()->json(['mensaje' => 'No encontrado'], 404);
        return response()->json($d);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'TipoHuesped' => ['required', Rule::in(['Adulto','Niño'])],
            'PorcentajeDescuento' => ['required','numeric','min:0','max:100'],
        ]);

        $d = Descuento::create($data);
        return response()->json($d, 201);
    }

    public function update(Request $request, $id)
    {
        $d = Descuento::find($id);
        if (!$d) return response()->json(['mensaje' => 'No encontrado'], 404);

        $data = $request->validate([
            'TipoHuesped' => ['sometimes','required', Rule::in(['Adulto','Niño'])],
            'PorcentajeDescuento' => ['sometimes','required','numeric','min:0','max:100'],
        ]);

        $d->update($data);
        return response()->json($d);
    }

    public function destroy($id)
    {
        $d = Descuento::find($id);
        if (!$d) return response()->json(['mensaje' => 'No encontrado'], 404);
        $d->delete();
        return response()->json(['mensaje' => 'Eliminado']);
    }
}
