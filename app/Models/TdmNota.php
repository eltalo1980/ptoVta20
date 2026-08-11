<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TdmNota extends Model
{
    protected $table='tdmnotas';
    protected $primaryKey = 'idnotas';
    protected $fillable = ['idAlumno','idMensajero', 'nota','activo'];
}
