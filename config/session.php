<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Driver de Sesion por Defecto
    |--------------------------------------------------------------------------
    |
    | Esta opcion determina el driver de sesion que se usara para las
    | peticiones entrantes. Laravel soporta varias opciones de
    | almacenamiento. La base de datos es una buena opcion por defecto.
    |
    | Soportado: "file", "cookie", "database", "memcached",
    |            "redis", "dynamodb", "array"
    |
    */

    'driver' => env('SESSION_DRIVER', 'database'),

    /*
    |--------------------------------------------------------------------------
    | Tiempo de Vida de la Sesion
    |--------------------------------------------------------------------------
    |
    | Aqui puedes especificar la cantidad de minutos que la sesion puede
    | permanecer inactiva antes de expirar. Si quieres que expire
    | inmediatamente al cerrar el navegador, usa la opcion expire_on_close.
    |
    */

    'lifetime' => (int) env('SESSION_LIFETIME', 120),

    'expire_on_close' => env('SESSION_EXPIRE_ON_CLOSE', false),

    /*
    |--------------------------------------------------------------------------
    | Encriptacion de Sesion
    |--------------------------------------------------------------------------
    |
    | Esta opcion permite especificar que todos los datos de sesion deben
    | encriptarse antes de almacenarse. La encriptacion es automatica
    | y puedes usar la sesion de forma normal.
    |
    */

    'encrypt' => env('SESSION_ENCRYPT', false),

    /*
    |--------------------------------------------------------------------------
    | Ubicacion de Archivos de Sesion
    |--------------------------------------------------------------------------
    |
    | Cuando se usa el driver "file", los archivos de sesion se guardan
    | en disco. La ubicacion por defecto se define aqui, pero puedes
    | cambiarla a otra ubicacion.
    |
    */

    'files' => storage_path('framework/sessions'),

    /*
    |--------------------------------------------------------------------------
    | Conexion de Base de Datos para Sesiones
    |--------------------------------------------------------------------------
    |
    | Cuando se usan los drivers "database" o "redis", puedes especificar
    | la conexion que se usara para gestionar las sesiones. Debe
    | corresponder a una conexion en tu configuracion de base de datos.
    |
    */

    'connection' => env('SESSION_CONNECTION'),

    /*
    |--------------------------------------------------------------------------
    | Tabla de Base de Datos para Sesiones
    |--------------------------------------------------------------------------
    |
    | Cuando se usa el driver "database", puedes especificar la tabla
    | que se usara para almacenar las sesiones. Ya viene definida
    | una tabla por defecto, pero puedes cambiarla.
    |
    */

    'table' => env('SESSION_TABLE', 'sessions'),

    /*
    |--------------------------------------------------------------------------
    | Almacen de Cache para Sesiones
    |--------------------------------------------------------------------------
    |
    | Cuando se usa un backend de sesion basado en cache, puedes definir
    | el almacen de cache que se usara para guardar los datos de sesion
    | entre peticiones. Debe coincidir con un almacen de cache definido.
    |
    | Afecta a: "dynamodb", "memcached", "redis"
    |
    */

    'store' => env('SESSION_STORE'),

    /*
    |--------------------------------------------------------------------------
    | Loteria de Limpieza de Sesiones
    |--------------------------------------------------------------------------
    |
    | Algunos drivers de sesion deben limpiar manualmente su almacenamiento
    | para eliminar sesiones viejas. Aqui se definen las probabilidades
    | de que esto ocurra en cada peticion. Por defecto es 2 de cada 100.
    |
    */

    'lottery' => [2, 100],

    /*
    |--------------------------------------------------------------------------
    | Nombre de la Cookie de Sesion
    |--------------------------------------------------------------------------
    |
    | Aqui puedes cambiar el nombre de la cookie de sesion que crea el
    | framework. Normalmente no es necesario cambiarlo ya que hacerlo
    | no aporta una mejora de seguridad significativa.
    |
    */

    'cookie' => env(
        'SESSION_COOKIE',
        Str::slug((string) env('APP_NAME', 'laravel')).'-session'
    ),

    /*
    |--------------------------------------------------------------------------
    | Ruta de la Cookie de Sesion
    |--------------------------------------------------------------------------
    |
    | La ruta de la cookie determina para que ruta estara disponible.
    | Normalmente sera la ruta raiz de tu aplicacion, pero puedes
    | cambiarla si es necesario.
    |
    */

    'path' => env('SESSION_PATH', '/'),

    /*
    |--------------------------------------------------------------------------
    | Dominio de la Cookie de Sesion
    |--------------------------------------------------------------------------
    |
    | Este valor determina el dominio y subdominios donde la cookie de
    | sesion estara disponible. Por defecto estara disponible en el
    | dominio raiz sin subdominios. Normalmente no debe cambiarse.
    |
    */

    'domain' => env('SESSION_DOMAIN'),

    /*
    |--------------------------------------------------------------------------
    | Cookies Solo por HTTPS
    |--------------------------------------------------------------------------
    |
    | Si activas esta opcion, las cookies de sesion solo se enviaran al
    | servidor si el navegador tiene una conexion HTTPS. Esto evita
    | que la cookie se envie cuando no se puede hacer de forma segura.
    |
    */

    'secure' => env('SESSION_SECURE_COOKIE'),

    /*
    |--------------------------------------------------------------------------
    | Solo Acceso HTTP
    |--------------------------------------------------------------------------
    |
    | Si activas esta opcion, JavaScript no podra acceder al valor de la
    | cookie y solo sera accesible a traves del protocolo HTTP. Es poco
    | probable que debas desactivar esta opcion.
    |
    */

    'http_only' => env('SESSION_HTTP_ONLY', true),

    /*
    |--------------------------------------------------------------------------
    | Cookies Same-Site
    |--------------------------------------------------------------------------
    |
    | Esta opcion determina como se comportan las cookies cuando se hacen
    | peticiones entre sitios, y puede usarse para mitigar ataques CSRF.
    | Por defecto se establece en "lax" para permitir peticiones seguras.
    |
    | Soportado: "lax", "strict", "none", null
    |
    */

    'same_site' => env('SESSION_SAME_SITE', 'lax'),

    /*
    |--------------------------------------------------------------------------
    | Cookies Particionadas
    |--------------------------------------------------------------------------
    |
    | Si activas esta opcion, la cookie se vinculara al sitio de nivel
    | superior en un contexto entre sitios. Las cookies particionadas
    | son aceptadas cuando estan marcadas como "secure" y Same-Site
    | esta establecido en "none".
    |
    */

    'partitioned' => env('SESSION_PARTITIONED_COOKIE', false),

];
