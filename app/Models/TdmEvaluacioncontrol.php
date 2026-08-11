<?php
namespace App;
use Illuminate\Database\Eloquent\Model;

class TdmEvaluacioncontrol extends Model
{
    protected $table='tdmevaluacioncontrol';
    protected $primaryKey = 'idEvaluacion';
    protected $fillable = ['nombreEvaluacion','fechaEvaluacion','nivel','activo','created_at','updated_at'];
}
