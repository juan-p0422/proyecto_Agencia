<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UsuarioController extends Controller
{
    public function index()
    {
        return response()->json(Usuario::all());
    }

    public function show($id)
    {
        $usuario = Usuario::find($id);
        if (!$usuario) return response()->json(['mensaje' => 'No encontrado'], 404);
        return response()->json($usuario);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'Nombre' => ['required','string','max:100'],
            'Correo' => ['required','email','max:150','unique:Usuario,Correo'],
        ]);

        $usuario = Usuario::create($data);
        return response()->json($usuario, 201);
    }

    public function update(Request $request, $id)
    {
        $usuario = Usuario::find($id);
        if (!$usuario) return response()->json(['mensaje' => 'No encontrado'], 404);

        $data = $request->validate([
            'Nombre' => ['sometimes','required','string','max:100'],
            'Correo' => [
                'sometimes','required','email','max:150',
                Rule::unique('Usuario','Correo')->ignore($usuario->IdUsuario, 'IdUsuario')
            ],
        ]);

        $usuario->update($data);
        return response()->json($usuario);
    }

    public function destroy($id)
    {
        $usuario = Usuario::find($id);
        if (!$usuario) return response()->json(['mensaje' => 'No encontrado'], 404);
        $usuario->delete();
        return response()->json(['mensaje' => 'Eliminado']);
    }
}

