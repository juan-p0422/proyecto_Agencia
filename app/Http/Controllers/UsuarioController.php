<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use App\Services\Builders\UsuarioBuilder;

class UsuarioController extends Controller
{
    public function index()
    {
        return response()->json(Usuario::query()->get()->makeHidden(['Password']));
    }

    public function show($id)
    {
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

        // ✅ NO hashear aquí. El modelo (mutator) lo hace una sola vez.
        $usuario = (new UsuarioBuilder())
            ->setNombre($data['Nombre'])
            ->setCorreo($data['Correo'])
            ->setPassword($data['Password']) // texto plano
            ->build();

        return response()->json($usuario->makeHidden(['Password']), 201);
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
                Rule::unique('Usuario', 'Correo')->ignore($usuario->IdUsuario, 'IdUsuario'),
            ],
            'Password' => ['sometimes', 'required', 'string', 'min:8'],
        ]);

        // ✅ NO hashear aquí tampoco. El mutator del modelo lo hará.
        $usuario->update($data);

        return response()->json($usuario->fresh()->makeHidden(['Password']));
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
            'Password' => ['required'],
        ]);

        $usuario = Usuario::where('Correo', $data['Correo'])->first();

        if (!$usuario || !Hash::check($data['Password'], $usuario->Password)) {
            return response()->json(['mensaje' => 'Credenciales inválidas'], 401);
        }

        // ✅ Corregido: tus columnas reales están en snake_case
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

        $token = $usuario->createToken('api')->plainTextToken;

        return response()->json([
            'token' => $token,
            'usuario' => $usuario->makeHidden(['Password']),
        ]);
    }
}
