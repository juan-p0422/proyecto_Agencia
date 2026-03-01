<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;

class Usuario extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = 'Usuario';
    protected $primaryKey = 'IdUsuario';
    public $timestamps = false;

    protected $fillable = [
        'Nombre',
        'Correo',
        'Password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
    ];

    protected $hidden = [
        'Password',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    public function reservaciones()
    {
        return $this->belongsToMany(
            Reservacion::class,
            'Usuario_Reservacion',
            'IdUsuario',
            'IdReservacion'
        );
    }

    /**
     * Hashea automáticamente la contraseña si no viene ya hasheada.
     */
    public function setPasswordAttribute($value)
    {
        if (!$value) {
            $this->attributes['Password'] = $value;
            return;
        }

        $isBcrypt = is_string($value) && preg_match('/^\$2[ayb]\$.{56}$/', $value);

        $this->attributes['Password'] = $isBcrypt ? $value : Hash::make($value);
    }

    public function getAuthPassword()
    {
        return $this->Password;
    }

    public function getEmailForPasswordReset()
    {
        return $this->Correo;
    }
}
