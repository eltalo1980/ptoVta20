<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Contabilidad extends Model
{
    protected $table='tdmcontabilidad';
    protected $primaryKey = 'idContabilidad';
    protected $fillable = ['idContabilidad','periodo', 'desde', 'hasta'];
}
