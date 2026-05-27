<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;
    // Especificamos el nombre de la tabla en la base de datos
    protected $table = 'usuarios'; // ''
    //
    public $timestamps = false;
    // Definimos los campos que se pueden asignar masivamente (para crear o actualizar registros) 
    protected $fillable = [
        'nombre',
        'correo',
        'password',
        'rol',
    ];

    protected $hidden = [
        'password',
    ];

    // Esto le dice a Laravel que busque por "correo" al autenticar
    public function getEmailAttribute()
    {
        return $this->correo;
    }
}