<?php

namespace App\Services\Builders;

use App\Models\Usuario;

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
        // ✅ NO hashear aquí. El modelo lo hashea con el mutator.
        $this->data['Password'] = $password;
        return $this;
    }

    public function build()
    {
        return Usuario::create($this->data);
    }
}
