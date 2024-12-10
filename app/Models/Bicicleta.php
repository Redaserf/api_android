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
        return $this->hasMany(Recorrido::class, 'bicicleta_id');
    }

    public function usuario(){
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function getImagenUrlAttribute()
    {
        return $this->imagen ? config('app.url') . Storage::url($this->imagen) : null;
    }

}
