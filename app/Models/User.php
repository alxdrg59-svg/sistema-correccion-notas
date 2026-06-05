<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'usuarios';

    public $timestamps = false;

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