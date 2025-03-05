<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class Recorrido extends Eloquent
{
    use HasFactory;

    protected $connection = 'mongodb';
    // protected $collection = 'recorridos';

    
    protected $table = 'recorridos';
    protected $fillable =
    [
    'calorias',
     'tiempo',
     'velocidad_promedio',
     'velocidad_maxima',
      'distancia_recorrida',
     'usuario', 
     'bicicleta_id',
      'temperatura',
      'duracion_final', //en este campo se va a estar metiendo el valor del cronometro q se inicia en la aplicacion cada q se termina un recorrido 
      'acabado', //true => el recorrido ya termino, false => el recorrido sigue en curso 
    ];

    public function usuario(){
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function bicicleta(){
        return Bicicleta::where('id', $this->bicicleta_id)->first();
    }

    public function velocidades()
    {
        return $this->hasMany(Velocidad::class, 'recorrido_id');
    }

}
