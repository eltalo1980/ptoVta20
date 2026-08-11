<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ContabilidadProfesores extends Model
{
    protected $table='tdmcontabilidadprofesores';
    protected $primaryKey = 'idContabilidadprofesores';
    protected $fillable = ['idContabilidadprofesores','idContabilidad','idAlumno','periodo', 'desde', 'hasta'];
}
