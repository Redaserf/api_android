<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recorrido extends Model
{
    use HasFactory;

    
    protected $table = 'recorridos';
    protected $fillable =
    ['calorias', 'tiempo', 'velocidad_promedio',
     'velocidad_maxima', 'distancia_recorrida',
     'usuario_id', 'bicicleta_id', 'temperatura'];

    public function usuario(){
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function bicicleta(){
        return $this->belongsTo(Bicicleta::class, 'bicicleta_id');
    }

    public function velocidades()
    {
        return $this->hasMany(Velocidad::class, 'recorrido_id');
    }

}
