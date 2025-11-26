<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transporte extends Model
{
    protected $table = 'Transporte';
    protected $primaryKey = 'IdTransporte';
    public $timestamps = false;

    protected $fillable = ['TipoTransporte', 'PrecioPorPersona', 'NombreEmpresa'];

    public function reservaciones()
    {
        return $this->hasMany(Reservacion::class, 'IdTransporte', 'IdTransporte');
    }
}
