<?php

namespace App\Http\Controllers;

use App\Models\Transporte;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TransporteController extends Controller
{
    public function index()
    {
        return response()->json(Transporte::all());
    }

    public function show($id)
    {
        $t = Transporte::find($id);
        if (!$t) return response()->json(['mensaje' => 'No encontrado'], 404);
        return response()->json($t);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'TipoTransporte' => ['required', Rule::in(['Autobus','Avion'])],
            'PrecioPorPersona' => ['required','numeric','min:0'],
            'NombreEmpresa'    => ['required','string','max:120'],
        ]);

        $t = Transporte::create($data);
        return response()->json($t, 201);
    }

    public function update(Request $request, $id)
    {
        $t = Transporte::find($id);
        if (!$t) return response()->json(['mensaje' => 'No encontrado'], 404);

        $data = $request->validate([
            'TipoTransporte' => ['sometimes','required', Rule::in(['Autobus','Avion'])],
            'PrecioPorPersona' => ['sometimes','required','numeric','min:0'],
            'NombreEmpresa'    => ['sometimes','required','string','max:120'],
        ]);

        $t->update($data);
        return response()->json($t);
    }

    public function destroy($id)
    {
        $t = Transporte::find($id);
        if (!$t) return response()->json(['mensaje' => 'No encontrado'], 404);
        $t->delete();
        return response()->json(['mensaje' => 'Eliminado']);
    }
}
