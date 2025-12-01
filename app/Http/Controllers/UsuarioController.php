<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use App\Services\Builders\UsuarioBuilder;

class UsuarioController extends Controller
{
    public function index()
    {
        return response()->json(Usuario::all());
    }

    public function show($id)
    {
        $usuario = Usuario::find($id);
        if (!$usuario) {
            return response()->json(['mensaje' => 'No encontrado'], 404);
        }
        return response()->json($usuario);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'Nombre' => ['required', 'string', 'max:100'],
            'Correo' => ['required', 'email', 'max:150', 'unique:Usuario,Correo'],
            'Password' => ['required', 'string', 'min:8']
        ]);

        $usuario = (new UsuarioBuilder())
            ->setNombre($data['Nombre'])
            ->setCorreo($data['Correo'])
            ->setPassword($data['Password'])
            ->build();

        return response()->json($usuario, 201);
    }


    public function update(Request $request, $id)
    {
        $usuario = Usuario::find($id);
        if (!$usuario) {
            return response()->json(['mensaje' => 'No encontrado'], 404);
        }

        $data = $request->validate([
            'Nombre' => ['sometimes', 'required', 'string', 'max:100'],
            'Correo' => [
                'sometimes',
                'required',
                'email',
                'max:150',
                Rule::unique('Usuario', 'Correo')->ignore($usuario->IdUsuario, 'IdUsuario')
            ],
            'Password' => ['sometimes', 'required', 'string', 'min:8']
        ]);

        if (isset($data['Password'])) {
            $data['Password'] = Hash::make($data['Password']);
        }

        $usuario->update($data);

        return response()->json($usuario);
    }

    public function destroy($id)
    {
        $usuario = Usuario::find($id);
        if (!$usuario) {
            return response()->json(['mensaje' => 'No encontrado'], 404);
        }

        $usuario->delete();
        return response()->json(['mensaje' => 'Eliminado']);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'Correo' => ['required', 'email'],
            'Password' => ['required']
        ]);

        $usuario = Usuario::where('Correo', $data['Correo'])->first();
        if (!$usuario || !Hash::check($data['Password'], $usuario->Password)) {
            return response()->json(['mensaje' => 'Credenciales inválidas'], 401);
        }

        return response()->json($usuario);
    }
}
