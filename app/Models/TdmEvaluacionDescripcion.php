<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TdmEvaluacionDescripcion extends Model
{
    protected $table='tdmevaluaciondescripcion';
    protected $primaryKey = 'idevaluacionDescripcion';
    protected $fillable = ['idEvaluacion','idEvaluacion','idPregunta','Pregunta','TipoPregunta','valoresRespuesta','activo','created_at','updated_at'];
}
