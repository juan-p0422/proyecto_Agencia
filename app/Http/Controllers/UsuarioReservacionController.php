<?php

namespace App\Http\Controllers;

use App\Models\Reservacion;
use App\Models\Usuario;
use Illuminate\Http\Request;

class UsuarioReservacionController extends Controller
{
    // Listar usuarios de una reservación
    public function indexByReservacion($idReservacion)
    {
        $r = Reservacion::with('usuarios')->find($idReservacion);
        if (!$r) return response()->json(['mensaje' => 'Reservación no encontrada'], 404);
        return response()->json($r->usuarios);
    }

    // Agregar usuarios a una reservación
    public function attach($idReservacion, Request $request)
{
    $r = Reservacion::find($idReservacion);
    if (!$r) {
        return response()->json(['mensaje' => 'Reservación no encontrada'], 404);
    }

    // Soportar ambos formatos: Usuarios[] o IdUsuario suelto
    if ($request->has('IdUsuario')) {
        $usuarioId = $request->validate([
            'IdUsuario' => ['required', 'integer', 'exists:Usuario,IdUsuario']
        ])['IdUsuario'];

        $r->usuarios()->attach($usuarioId);

        return response()->json([
            'mensaje' => 'Usuario agregado',
            'reservacion' => $r->load('usuarios')
        ], 200);
    }

    // Formato original
    $data = $request->validate([
        'Usuarios' => ['required', 'array', 'min:1'],
        'Usuarios.*' => ['integer', 'exists:Usuario,IdUsuario'],
    ]);

    $r->usuarios()->attach($data['Usuarios']);

    return response()->json([
        'mensaje' => 'Usuarios agregados',
        'reservacion' => $r->load('usuarios')
    ], 200);
}

    // Quitar usuarios de una reservación
    public function detach($idReservacion, Request $request)
    {
        $r = Reservacion::find($idReservacion);
        if (!$r) return response()->json(['mensaje' => 'Reservación no encontrada'], 404);

        $data = $request->validate([
            'Usuarios' => ['required','array','min:1'],
            'Usuarios.*' => ['integer','exists:Usuario,IdUsuario'],
        ]);

        $r->usuarios()->detach($data['Usuarios']);
        return response()->json(['mensaje' => 'Usuarios removidos']);
    }
}
