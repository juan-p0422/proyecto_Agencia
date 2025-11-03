<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;

class UsuarioController extends Controller
{
   
   public function index()
    {
        // Obtener todos los usuarios
        $usuarios = \App\Models\Usuario::all();

        // Retornar los usuarios como respuesta JSON
        return response()->json($usuarios);
    }

    
    public function store(Request $request)
    {
        // Validar los datos del usuario
        $validatedData = $request->validate([
            'Nombre' => 'required|string|max:100',
            'Correo' => 'required|email|max:150|unique:Usuario,Correo',
        ]);

        // Crear un nuevo usuario usando Eloquent
        $usuario = \App\Models\Usuario::create($validatedData);

        // Retornar el usuario creado como respuesta JSON
        return response()->json($usuario, 201);
    }



}
