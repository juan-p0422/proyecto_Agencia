<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Habitacion extends Model
{
    protected $table = 'Habitacion';
    protected $primaryKey = 'IdHabitacion';
    public $timestamps = false;

    protected $fillable = ['IdHotel', 'TipoHabitacion', 'Precio', 'MaximoHuespedes'];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class, 'IdHotel', 'IdHotel');
    }
}
