<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use User;

class Rol extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'roles';
    protected $fillable = ['nombre'];

    public function users()
    {
        return $this->hasMany(Usuario::class);
    }
}
