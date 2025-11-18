<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Descuento extends Model
{
    protected $table = 'Descuento';
    protected $primaryKey = 'IdDescuento';
    public $timestamps = false;

    protected $fillable = ['TipoHuesped', 'PorcentajeDescuento'];
}
