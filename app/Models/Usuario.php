<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject; // 1. Importamos la interfaz de JWT

// 2. Le decimos a la clase que implemente JWTSubject
class Usuario extends Authenticatable implements JWTSubject 
{
    // Asegúrate de que el nombre de tu tabla sea el correcto
    protected $table = 'Usuario'; 
    protected $primaryKey = 'IdUsuario';
    public $timestamps = false; // Cambia a true si usas created_at y updated_at

    protected $fillable = [
        'Nombre',
        'Correo',
        'Password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at'
    ];

    // Ocultamos datos sensibles cuando el usuario se devuelve en JSON
    protected $hidden = [
        'Password',
        'two_factor_secret',
        'two_factor_recovery_codes'
    ];

    /* ==========================================================
       🔹 MÉTODOS OBLIGATORIOS PARA QUE JWT FUNCIONE 🔹
       ========================================================== */

    /**
     * Obtiene el identificador que se guardará en el Token (tu IdUsuario).
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Permite agregar datos extra (claims) dentro del Token.
     * Si no necesitas datos extra, simplemente retornamos un array vacío.
     */
    public function getJWTCustomClaims()
    {
        return [
            'correo' => $this->Correo,
            'nombre' => $this->Nombre
        ];
    }
}
