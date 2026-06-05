<?php

use App\Models\User;

return [

    /*
    |--------------------------------------------------------------------------
    | Valores por Defecto de Autenticacion
    |--------------------------------------------------------------------------
    |
    | Esta opcion define el "guard" de autenticacion y el "broker" de
    | restablecimiento de contrasena por defecto. Puedes cambiar estos
    | valores segun lo necesites.
    |
    */

    'defaults' => [
        'guard' => env('AUTH_GUARD', 'web'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Guards de Autenticacion
    |--------------------------------------------------------------------------
    |
    | Aqui puedes definir los guards de autenticacion de tu aplicacion.
    | La configuracion por defecto usa almacenamiento de sesion junto
    | con el proveedor de usuarios Eloquent.
    |
    | Todos los guards tienen un proveedor de usuarios que define como
    | se obtienen los usuarios de la base de datos u otro sistema
    | de almacenamiento. Normalmente se usa Eloquent.
    |
    | Soportado: "session"
    |
    */

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Proveedores de Usuarios
    |--------------------------------------------------------------------------
    |
    | Todos los guards tienen un proveedor de usuarios que define como se
    | obtienen los usuarios de la base de datos. Normalmente se usa Eloquent.
    |
    | Si tienes multiples tablas o modelos de usuario puedes configurar
    | multiples proveedores. Estos proveedores pueden asignarse a
    | cualquier guard de autenticacion que hayas definido.
    |
    | Soportado: "database", "eloquent"
    |
    */

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => env('AUTH_MODEL', User::class),
        ],

        // 'users' => [
        //     'driver' => 'database',
        //     'table' => 'users',
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Restablecimiento de Contrasenas
    |--------------------------------------------------------------------------
    |
    | Estas opciones especifican el comportamiento del restablecimiento de
    | contrasenas de Laravel, incluyendo la tabla para almacenar tokens
    | y el proveedor de usuarios usado para obtener los usuarios.
    |
    | El tiempo de expiracion es la cantidad de minutos que cada token
    | sera considerado valido. Esto mantiene los tokens con vida corta
    | para que tengan menos tiempo de ser adivinados.
    |
    | El throttle es la cantidad de segundos que un usuario debe esperar
    | antes de generar mas tokens de restablecimiento.
    |
    */

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Tiempo de Confirmacion de Contrasena
    |--------------------------------------------------------------------------
    |
    | Aqui puedes definir la cantidad de segundos antes de que la ventana
    | de confirmacion de contrasena expire y se le pida al usuario que
    | reingrese su contrasena. Por defecto dura tres horas.
    |
    */

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];
