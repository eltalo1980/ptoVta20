<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tdmparametros extends Model
{
    protected $table='tdmparametros';
    protected $primaryKey = 'idParametro';
    protected $fillable = ['grupo','idvalor', 'descripcion'];
}
