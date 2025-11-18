<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hotel extends Model
{
    protected $table = 'Hotel';
    protected $primaryKey = 'IdHotel';
    public $timestamps = false;

    protected $fillable = ['NombreHotel', 'Ubicacion'];

    public function habitaciones()
    {
        return $this->hasMany(Habitacion::class, 'IdHotel', 'IdHotel');
    }

    public function reservaciones()
    {
        return $this->hasMany(Reservacion::class, 'IdHotel', 'IdHotel');
    }
}

