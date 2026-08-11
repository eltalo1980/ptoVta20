<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Asistencias extends Model
{
    //


    public $table ="tdm_asistencias";
    protected $primaryKey = 'id_asistencia';
    protected $fillable = ['fecha_asistencia','id_usuario'];
}
