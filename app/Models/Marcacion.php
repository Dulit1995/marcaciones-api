<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Marcacion extends Model
{
    use HasFactory;

    protected $fillable = [
        'empleado_id',
        'tipo_marcacion',
        'timestamp',
    ];

    public function empleado()
    {
        return $this->belongsTo(Empleado::class);
    }
}
