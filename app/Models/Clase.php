<?php

namespace App;
#use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Clase extends Model
{
    #use HasFactory;
    public $table ="tdmclases";
    protected $primaryKey = 'id_clase';
    protected $fillable = ['titulo','descripcion','fecha','orden','duracion'];
}
