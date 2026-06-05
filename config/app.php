<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Nombre de la Aplicacion
    |--------------------------------------------------------------------------
    |
    | Este valor es el nombre de tu aplicacion, que se usara cuando el
    | framework necesite mostrar el nombre en notificaciones u otros
    | elementos de la interfaz de usuario.
    |
    */

    'name' => env('APP_NAME', 'Laravel'),

    /*
    |--------------------------------------------------------------------------
    | Entorno de la Aplicacion
    |--------------------------------------------------------------------------
    |
    | Este valor determina el "entorno" en el que tu aplicacion se esta
    | ejecutando. Puede determinar como se configuran los diferentes
    | servicios que utiliza. Se establece en tu archivo ".env".
    |
    */

    'env' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Modo de Depuracion
    |--------------------------------------------------------------------------
    |
    | Cuando tu aplicacion esta en modo de depuracion, se mostraran mensajes
    | de error detallados con trazas de pila en cada error que ocurra.
    | Si esta desactivado, se muestra una pagina de error generica.
    |
    */

    'debug' => (bool) env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | URL de la Aplicacion
    |--------------------------------------------------------------------------
    |
    | Esta URL es usada por la consola para generar URLs correctamente
    | al usar la herramienta de linea de comandos Artisan. Debes
    | establecerla a la raiz de tu aplicacion.
    |
    */

    'url' => env('APP_URL', 'http://localhost'),

    /*
    |--------------------------------------------------------------------------
    | Zona Horaria de la Aplicacion
    |--------------------------------------------------------------------------
    |
    | Aqui puedes especificar la zona horaria por defecto para tu aplicacion,
    | que sera usada por las funciones de fecha y hora de PHP. Por defecto
    | es "UTC", pero aqui esta configurada para El Salvador.
    |
    */

    'timezone' => 'America/El_Salvador',

    /*
    |--------------------------------------------------------------------------
    | Configuracion de Idioma
    |--------------------------------------------------------------------------
    |
    | El idioma de la aplicacion determina el idioma por defecto que usaran
    | los metodos de traduccion/localizacion de Laravel. Se puede
    | establecer a cualquier idioma del que tengas traducciones.
    |
    */

    'locale' => env('APP_LOCALE', 'en'),

    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),

    'faker_locale' => env('APP_FAKER_LOCALE', 'en_US'),

    /*
    |--------------------------------------------------------------------------
    | Clave de Encriptacion
    |--------------------------------------------------------------------------
    |
    | Esta clave es utilizada por los servicios de encriptacion de Laravel
    | y debe ser una cadena aleatoria de 32 caracteres para garantizar
    | que todos los valores encriptados sean seguros. Debes configurarla
    | antes de desplegar la aplicacion.
    |
    */

    'cipher' => 'AES-256-CBC',

    'key' => env('APP_KEY'),

    'previous_keys' => [
        ...array_filter(
            explode(',', (string) env('APP_PREVIOUS_KEYS', ''))
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | Driver de Modo Mantenimiento
    |--------------------------------------------------------------------------
    |
    | Estas opciones determinan el driver usado para gestionar el estado
    | de "modo mantenimiento" de Laravel. El driver "cache" permite
    | controlar el modo mantenimiento en multiples maquinas.
    |
    | Drivers soportados: "file", "cache"
    |
    */

    'maintenance' => [
        'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),
        'store' => env('APP_MAINTENANCE_STORE', 'database'),
    ],

];
