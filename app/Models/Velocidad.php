<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Velocidad extends Model
{
    use HasFactory;

    protected $table = 'velocidades';
    protected $fillable = ['recorrido_id', 'valor'];

    public function recorrido()
    {
        return Recorrido::where('_id', $this->recorrido_id)->first();
    }
}
