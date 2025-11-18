<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservacion extends Model
{
    protected $table = 'Reservacion';
    protected $primaryKey = 'IdReservacion';
    public $timestamps = false;

    protected $fillable = [
        'FechaInicio', 'FechaFin', 'PrecioTotal', 'NumHuespedes', 'NumHabitaciones',
        'IdHotel', 'IdTransporte', 'Estatus'
    ];

    protected $casts = [
        'FechaInicio' => 'date',
        'FechaFin' => 'date',
        'PrecioTotal' => 'float',
        'NumHuespedes' => 'integer',
        'NumHabitaciones' => 'integer',
    ];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class, 'IdHotel', 'IdHotel');
    }

    public function transporte()
    {
        return $this->belongsTo(Transporte::class, 'IdTransporte', 'IdTransporte');
    }

    public function usuarios()
    {
        return $this->belongsToMany(Usuario::class, 'Usuario_Reservacion', 'IdReservacion', 'IdUsuario');
    }
}

