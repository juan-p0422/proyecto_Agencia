<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class Usuario extends Model
{
    protected $table = 'Usuario';
    protected $primaryKey = 'IdUsuario';
    public $timestamps = false;

    protected $fillable = ['Nombre', 'Correo', 'Password'];

    protected $hidden = ['Password'];

    public function reservaciones()
    {
        return $this->belongsToMany(Reservacion::class, 'Usuario_Reservacion', 'IdUsuario', 'IdReservacion');
    }

    public function setPasswordAttribute($value)
    {
        if ($value && !preg_match('/^\$2[ayb]\$.{56}$/', $value)) {
            $this->attributes['Password'] = Hash::make($value);
        } else {
            $this->attributes['Password'] = $value;
        }
    }
}
