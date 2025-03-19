<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Bicicleta extends Model
{
    use HasFactory;
    protected $table = 'bicicletas';
    protected $fillable = ['nombre', 'usuario_id'];

    public function recorridos(){
        return Recorrido::raw(function($collection){
            return $collection->find(['bicicleta_id' => $this->id]);//retorna los recorridos de un usuario
        });
    }

    public function usuario(){
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function getImagenUrlAttribute()
    {
        return $this->imagen ? config('app.url') . Storage::url($this->imagen) : null;
    }

}
