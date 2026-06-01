<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// =====================================================
// MODELO: SOLICITUD DE CORRECCION DE NOTA
//
// Representa una solicitud creada por un estudiante
// para corregir la nota de una evaluacion.
//
// Cada solicitud pasa por un flujo de aprobacion:
//   Estudiante → Docente → Coordinador → Admin
//
// Estados posibles (campo 'estado'):
//   - pendiente_docente: recien creada, esperando revision del docente
//   - rechazado_docente: el docente la rechazo
//   - pendiente_coordinador: el docente la aprobo, esperando coordinador
//   - rechazado_coordinador: el coordinador la rechazo
//   - pendiente_admin: el coordinador la aprobo, esperando admin
//   - finalizado: el admin aplico la correccion
//
// Campo 'es_excepcion':
//   - 0 = solicitud normal (dentro del periodo activo)
//   - 1 = solicitud por excepcion (fuera del periodo o para evaluacion anterior)
//
// Un estudiante puede tener una solicitud normal Y una de excepcion
// activas al mismo tiempo para la misma materia.
// =====================================================
class SolicitudCorreccion extends Model
{
    // Nombre real de la tabla en la base de datos
    protected $table = 'solicitudes_correccion';

    // La tabla no usa los campos created_at/updated_at de Laravel
    public $timestamps = false;

    // Campos que se pueden llenar de forma masiva (mass assignment)
    protected $fillable = [
        'estudiante_id',
        'materia_id',
        'seccion',
        'ciclo_id',
        'ciclo',
        'docente_id',
        'evaluacion',
        'nota_actual',
        'motivo',
        'estado',
        'es_excepcion',
        'fecha_solicitud'
    ];

    // Relacion con la tabla materias: cada solicitud pertenece a una materia
    public function materiaRelacion()
    {
        return $this->belongsTo(Materia::class, 'materia_id', 'id');
    }
}
