<?php
/**
 * Vacía las tablas dependientes del flujo de solicitudes para poder
 * probar de nuevo desde cero. NO toca usuarios, facultades, carreras,
 * materias, asignaciones ni periodos.
 *
 * Uso: php reset_solicitudes.php
 */
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

// Orden importante: primero hijas (con FK), después la padre.
$tablas = ['historial_notas', 'aprobaciones', 'evidencias', 'solicitudes_correccion'];

DB::statement('SET FOREIGN_KEY_CHECKS=0;');
foreach ($tablas as $t) {
    DB::table($t)->truncate();
    echo "  [OK] $t  → vaciada y AUTO_INCREMENT reseteado" . PHP_EOL;
}
DB::statement('SET FOREIGN_KEY_CHECKS=1;');

echo PHP_EOL . "Listo. Ya puedes enviar una nueva solicitud de prueba." . PHP_EOL;
echo "Entra como estudiante (luis.m@utec.com / 1234) → \"Nueva Solicitud\"." . PHP_EOL;
