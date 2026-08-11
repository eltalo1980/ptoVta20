<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Partido extends Model
{
    //protected $fillable = ['IDJugador1','IDJugador2','PtosJugador1Set1','PtosJugador2Set1','PtosJugador1Set2','PtosJugador2Set2','PtosJugador1Set3','PtosJugador2Set3'];
    protected $fillable = ['PtosJugador1Set1','PtosJugador2Set1','PtosJugador1Set2','PtosJugador2Set2','PtosJugador1Set3','PtosJugador2Set3'];
}
