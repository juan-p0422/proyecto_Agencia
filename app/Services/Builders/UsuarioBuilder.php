<?php

namespace App\Services\Builders;

use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;

class UsuarioBuilder
{
    private $data = [];

    public function setNombre($nombre)
    {
        $this->data['Nombre'] = $nombre;
        return $this;
    }

    public function setCorreo($correo)
    {
        $this->data['Correo'] = $correo;
        return $this;
    }

    public function setPassword($password)
    {
        $this->data['Password'] = Hash::make($password);
        return $this;
    }

    public function build()
    {
        return Usuario::create($this->data);
    }
}
