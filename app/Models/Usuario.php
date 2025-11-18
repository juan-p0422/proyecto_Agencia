<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Usuario extends Model
{
    protected $table = 'Usuario';
    protected $primaryKey = 'IdUsuario';
    public $timestamps = false;

    protected $fillable = ['Nombre', 'Correo'];

    // Relaciones
    public function reservaciones()
    {
        return $this->belongsToMany(Reservacion::class, 'Usuario_Reservacion', 'IdUsuario', 'IdReservacion');
    }
}

