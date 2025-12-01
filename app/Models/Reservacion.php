<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservacion extends Model
{
    protected $table = 'Reservacion';
    protected $primaryKey = 'IdReservacion';
    public $timestamps = false;

    protected $fillable = [
        'FechaInicio',
        'FechaFin',
        'PrecioTotal',
        'NumHuespedes',
        'NumHabitaciones',
        'IdHotel',
        'IdTransporte',
        'IdHabitacion', // 👈 IMPORTANTE
        'Estatus'
    ];

    // 🔹 RELACIÓN CON HABITACION
    public function habitacion()
    {
        return $this->belongsTo(Habitacion::class, 'IdHabitacion', 'IdHabitacion');
    }

    // 🔹 RELACIÓN CON HOTEL
    public function hotel()
    {
        return $this->belongsTo(Hotel::class, 'IdHotel', 'IdHotel');
    }

    // 🔹 RELACIÓN CON TRANSPORTE
    public function transporte()
    {
        return $this->belongsTo(Transporte::class, 'IdTransporte', 'IdTransporte');
    }

    // 🔹 RELACIÓN muchos-a-muchos con usuarios
    public function usuarios()
    {
        return $this->belongsToMany(Usuario::class, 'Usuario_Reservacion', 'IdReservacion', 'IdUsuario');
    }
}
