<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Usuario extends Model
{
    use HasFactory;

    // Nombre de la tabla asociada
    protected $table = 'Usuario';

    // Llave primaria
    protected $primaryKey = 'IdUsuario';

    // Campos rellenables (para permitir inserciones masivas)
    protected $fillable = ['Nombre', 'Correo'];

    // Deshabilitar timestamps si no tienes columnas `created_at` y `updated_at`
    public $timestamps = false;
}
