<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InformacionAdicional extends Model
{
    protected $fillable = ['id','Item', 'Tipo', 'Orden'];
}
