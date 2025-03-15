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
     'velocidad',//velocidad actual (sera la q se muestra en la aplicacion)
     'velocidad_promedio',
     'velocidad_maxima',
      'distancia_recorrida',
     'usuario',
     'bicicleta_id',
     'temperatura',
     'suma_velocidad',//este campo sera un arreglo q tendra la suma de las velocidades de cada recorrido y la cantidad de velocidades calculadas para sacar el promedio
     //[
     //  'suma' => 0,
     //  'cantidad' => 0
     //]
    //  'peso_perdido' => 0,
      'duracion_final', //este campo se va a convertir a segundos el tiempo al acabar el recorrido
      'acabado', //true => el recorrido ya termino, false => el recorrido aun no termina 
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
