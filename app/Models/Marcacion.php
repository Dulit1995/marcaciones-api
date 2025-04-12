<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Marcacion extends Model
{
    protected $table = 'marcacions'; // Asegúrate de que coincida con el nombre de tu tabla
    protected $fillable = ['empleado_id', 'tipo_marcacion', 'timestamp'];
}
