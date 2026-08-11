<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Configuracion extends Model
{
    protected $table = 'tbl_local_marco_cofiguracion';
    protected $primaryKey = 'idConfiguracion';
    public $timestamps = false;

    protected $fillable = [
        'idLocal',
        'categoria',
        'valor',
        'descripcion',
        'tipoValores',
        'nivel'
    ];
}
