<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tdmconfiguracion extends Model
{
    protected $table='tdmconfiguracion';
    protected $primaryKey = 'idConfiguracion';
    protected $fillable = ['tipoValores','concepto','categoria','idvalor','valor','descripcion'];
}
