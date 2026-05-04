<?php

namespace App\Http\Controllers;

use App\Models\Reservacion;
use App\Models\Usuario;
use Illuminate\Http\Request;

class UsuarioReservacionController extends Controller
{
    public function indexByReservacion($idReservacion)
    {
        $r = Reservacion::with('usuarios')->find($idReservacion);
        if (!$r) return response()->json(['mensaje' => 'Reservación no encontrada'], 404);

        $usuarioActual = auth()->user();
        if (!$r->usuarios->contains('IdUsuario', $usuarioActual->IdUsuario)) {
            return response()->json(['mensaje' => 'Acceso denegado. Esta reservación no te pertenece.'], 403);
        }

        return response()->json($r->usuarios);
    }

    public function attach($idReservacion, Request $request)
    {
        $r = Reservacion::with('usuarios')->find($idReservacion);
        if (!$r) {
            return response()->json(['mensaje' => 'Reservación no encontrada'], 404);
        }

        $usuarioActual = auth()->user();
        if (!$r->usuarios->contains('IdUsuario', $usuarioActual->IdUsuario)) {
            return response()->json(['mensaje' => 'Acceso denegado. No puedes modificar esta reservación.'], 403);
        }

        if ($request->has('IdUsuario')) {
            $usuarioId = $request->validate([
                'IdUsuario' => ['required', 'integer', 'exists:Usuario,IdUsuario']
            ])['IdUsuario'];

            if (!$r->usuarios->contains('IdUsuario', $usuarioId)) {
                $r->usuarios()->attach($usuarioId);
            }

            return response()->json([
                'mensaje' => 'Usuario agregado',
                'reservacion' => $r->load('usuarios')
            ], 200);
        }

        $data = $request->validate([
            'Usuarios' => ['required', 'array', 'min:1'],
            'Usuarios.*' => ['integer', 'exists:Usuario,IdUsuario'],
        ]);

        $r->usuarios()->syncWithoutDetaching($data['Usuarios']);

        return response()->json([
            'mensaje' => 'Usuarios agregados',
            'reservacion' => $r->load('usuarios')
        ], 200);
    }
}
