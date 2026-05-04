<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use App\Services\Builders\UsuarioBuilder;

class UsuarioController extends Controller
{
    public function show($id)
    {
        $usuarioActual = auth('api')->user();

        if ($usuarioActual->IdUsuario != $id) {
            return response()->json(['mensaje' => 'Acceso denegado. Solo puedes ver tu propio perfil.'], 403);
        }

        $usuario = Usuario::find($id);
        if (!$usuario) {
            return response()->json(['mensaje' => 'No encontrado'], 404);
        }

        return response()->json($usuario->makeHidden(['Password']));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'Nombre' => ['required', 'string', 'max:100'],
            'Correo' => ['required', 'email', 'max:150', 'unique:Usuario,Correo'],
            'Password' => ['required', 'string', 'min:8'],
        ]);

        $usuario = (new UsuarioBuilder())
            ->setNombre($data['Nombre'])
            ->setCorreo($data['Correo'])
            ->setPassword(Hash::make($data['Password'])) 
            ->build();

        return response()->json($usuario->makeHidden(['Password']), 201);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'Correo' => ['required', 'email'],
            'Password' => ['required'],
        ]);

        $usuario = Usuario::where('Correo', $data['Correo'])->first();

        if (!$usuario || !Hash::check($data['Password'], $usuario->Password)) {
            return response()->json(['mensaje' => 'Credenciales inválidas'], 401);
        }

        $twoFactorEnabled =
            !empty($usuario->two_factor_secret)
            && !empty($usuario->two_factor_confirmed_at);

        if ($twoFactorEnabled) {
            $pending = (string) Str::uuid();
            Cache::put("2fa:pending:{$pending}", $usuario->IdUsuario, now()->addMinutes(5));

            return response()->json([
                'two_factor_required' => true,
                'pending_token' => $pending,
            ]);
        }

        $token = auth('api')->login($usuario);

        return response()->json([
            'token' => $token,
            'usuario' => $usuario->makeHidden(['Password']),
        ]);
    }
}
