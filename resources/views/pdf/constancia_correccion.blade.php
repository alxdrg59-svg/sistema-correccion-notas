{{--
    ===================================================
    PLANTILLA PDF: Constancia de Corrección de Nota
    ===================================================
    Renderizada por dompdf (sin Tailwind, sin JS).
    Solo se usa CSS estático embebido porque dompdf no
    soporta CSS moderno (grid, flex avanzado, custom props).
    Recibe del controlador:
      $solicitud           → datos básicos de la solicitud + estudiante + materia
      $historialNota       → registro de nota_anterior / nota_nueva / fecha
      $decisionDocente     → última acción del docente (acción, comentario, fecha)
      $decisionCoordinador → última acción del coordinador
      $decisionAdmin       → acción de cierre del admin
      $fechaEmision        → Carbon::now() para timbrar la constancia
--}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Constancia de Corrección de Nota</title>
    <style>
        @page { margin: 30px 40px; }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 11px;
            color: #1f2937;
            line-height: 1.5;
        }

        /* ───────── Encabezado institucional ───────── */
        .header {
            border-bottom: 4px solid #5D0A28;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header table { width: 100%; border-collapse: collapse; }
        .header .marca {
            color: #5D0A28;
            font-size: 22px;
            font-weight: bold;
            letter-spacing: 1px;
        }
        .header .subtitulo {
            color: #555;
            font-size: 11px;
            font-style: italic;
            margin-top: 2px;
        }
        .header .folio {
            text-align: right;
            font-size: 10px;
            color: #555;
        }
        .header .folio strong { color: #5D0A28; font-size: 12px; }

        /* ───────── Título del documento ───────── */
        .titulo-doc {
            text-align: center;
            margin: 18px 0 20px 0;
        }
        .titulo-doc h1 {
            font-size: 16px;
            color: #5D0A28;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .titulo-doc .linea-bajo-titulo {
            width: 120px;
            height: 2px;
            background-color: #5D0A28;
            margin: 6px auto 0 auto;
        }

        /* ───────── Cuerpo principal ───────── */
        .seccion {
            margin-bottom: 16px;
        }
        .seccion h2 {
            font-size: 12px;
            color: #5D0A28;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 4px;
            margin: 0 0 8px 0;
        }

        /* Tabla de datos clave en dos columnas */
        .datos {
            width: 100%;
            border-collapse: collapse;
        }
        .datos td {
            padding: 4px 6px;
            vertical-align: top;
        }
        .datos .label {
            width: 28%;
            color: #555;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9px;
        }
        .datos .valor {
            color: #1f2937;
            font-weight: 600;
        }

        /* Caja destacada con la corrección de nota */
        .resultado {
            background-color: #f9fafb;
            border: 2px solid #5D0A28;
            border-radius: 6px;
            padding: 12px;
            margin: 14px 0;
        }
        .resultado table {
            width: 100%;
            border-collapse: collapse;
            text-align: center;
        }
        .resultado .col-nota {
            width: 40%;
        }
        .resultado .col-flecha {
            width: 20%;
            color: #9ca3af;
            font-size: 22px;
        }
        .resultado .label-nota {
            font-size: 9px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 4px;
        }
        .resultado .valor-anterior {
            font-size: 28px;
            font-weight: bold;
            color: #dc2626;
        }
        .resultado .valor-nuevo {
            font-size: 28px;
            font-weight: bold;
            color: #16a34a;
        }

        /* Bloque de trazabilidad de aprobaciones */
        .trazabilidad table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }
        .trazabilidad th {
            background-color: #5D0A28;
            color: #fff;
            text-align: left;
            padding: 6px 8px;
            font-size: 9px;
            text-transform: uppercase;
        }
        .trazabilidad td {
            padding: 6px 8px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 10px;
            vertical-align: top;
        }
        .trazabilidad .comentario {
            color: #4b5563;
            font-style: italic;
            font-size: 9px;
            margin-top: 2px;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 8px;
            font-weight: bold;
            border-radius: 8px;
            border: 1px solid;
        }
        .badge-ok   { background-color: #ecfdf5; color: #047857; border-color: #a7f3d0; }
        .badge-no   { background-color: #fef2f2; color: #b91c1c; border-color: #fecaca; }

        /* Motivo del reclamo */
        .motivo {
            background-color: #f9fafb;
            border-left: 3px solid #5D0A28;
            padding: 8px 10px;
            font-size: 10px;
            color: #374151;
        }

        /* Pie de página: firma del admin */
        .firma {
            margin-top: 50px;
            text-align: center;
        }
        .firma .linea {
            width: 60%;
            border-top: 1px solid #1f2937;
            margin: 0 auto;
        }
        .firma .nombre {
            font-weight: bold;
            font-size: 11px;
            margin-top: 4px;
        }
        .firma .cargo {
            font-size: 9px;
            color: #555;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Aviso al pie del documento */
        .nota-legal {
            margin-top: 25px;
            text-align: center;
            font-size: 8px;
            color: #9ca3af;
            font-style: italic;
            border-top: 1px dashed #e5e7eb;
            padding-top: 6px;
        }
    </style>
</head>
<body>

    {{-- ══════════════════════════════════════════════ --}}
    {{-- ENCABEZADO institucional con folio único       --}}
    {{-- ══════════════════════════════════════════════ --}}
    <div class="header">
        <table>
            <tr>
                <td>
                    <img src="{{ public_path('images/utec-logo.jpeg') }}" style="height: 50px; margin-bottom: 4px;"><br>
                    <div class="marca">UTEC</div>
                    <div class="subtitulo">Universidad Tecnológica · Sistema de Corrección de Notas</div>
                </td>
                <td class="folio">
                    Folio: <strong>SCN-{{ str_pad($solicitud->id, 6, '0', STR_PAD_LEFT) }}</strong><br>
                    Emisión: {{ $fechaEmision->format('d/m/Y H:i') }}
                </td>
            </tr>
        </table>
    </div>

    {{-- Título del documento --}}
    <div class="titulo-doc">
        <h1>Constancia de Corrección de Nota</h1>
        <div class="linea-bajo-titulo"></div>
    </div>

    {{-- ══════════════════════════════════════════════ --}}
    {{-- DATOS DEL ESTUDIANTE Y LA ASIGNATURA            --}}
    {{-- ══════════════════════════════════════════════ --}}
    <div class="seccion">
        <h2>Información del Estudiante</h2>
        <table class="datos">
            <tr>
                <td class="label">Nombre</td>
                <td class="valor">{{ $solicitud->estudiante_nombre }}</td>
                <td class="label">Carnet</td>
                <td class="valor">{{ $solicitud->estudiante_carnet ?? '—' }}</td>
            </tr>
            <tr>
                <td class="label">Facultad</td>
                <td class="valor">{{ $solicitud->facultad_nombre ?? '—' }}</td>
                <td class="label">Carrera</td>
                <td class="valor">{{ $solicitud->carrera_nombre ?? '—' }}</td>
            </tr>
        </table>
    </div>

    <div class="seccion">
        <h2>Detalle de la Asignatura</h2>
        <table class="datos">
            <tr>
                <td class="label">Materia</td>
                <td class="valor">{{ $solicitud->materia_nombre }}</td>
                <td class="label">Sección</td>
                <td class="valor">{{ $solicitud->seccion }}</td>
            </tr>
            <tr>
                <td class="label">Docente</td>
                <td class="valor">{{ $solicitud->docente_nombre }}</td>
                <td class="label">Ciclo</td>
                <td class="valor">{{ $solicitud->ciclo }}</td>
            </tr>
            <tr>
                <td class="label">Evaluación</td>
                <td class="valor" colspan="3">{{ $solicitud->evaluacion }}</td>
            </tr>
        </table>
    </div>

    {{-- ══════════════════════════════════════════════ --}}
    {{-- RESULTADO DE LA CORRECCIÓN (lo más importante) --}}
    {{-- ══════════════════════════════════════════════ --}}
    <div class="resultado">
        <table>
            <tr>
                <td class="col-nota">
                    <div class="label-nota">Nota Propuesta</div>
                    <div class="valor-anterior">{{ number_format($historialNota->nota_anterior, 2) }}</div>
                </td>
                <td class="col-flecha">&#10140;</td>
                <td class="col-nota">
                    <div class="label-nota">Nota Corregida</div>
                    <div class="valor-nuevo">{{ number_format($historialNota->nota_nueva, 2) }}</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- ══════════════════════════════════════════════ --}}
    {{-- MOTIVO DEL RECLAMO (texto que escribió el      --}}
    {{-- estudiante al crear la solicitud)              --}}
    {{-- ══════════════════════════════════════════════ --}}
    <div class="seccion">
        <h2>Motivo del Reclamo</h2>
        <div class="motivo">
            {{ $solicitud->motivo }}
        </div>
    </div>

    {{-- ══════════════════════════════════════════════ --}}
    {{-- TRAZABILIDAD del flujo de aprobación            --}}
    {{-- Una fila por cada actor (docente, coordinador, --}}
    {{-- admin) con su fecha, comentario y resultado.   --}}
    {{-- ══════════════════════════════════════════════ --}}
    <div class="seccion trazabilidad">
        <h2>Trazabilidad de la Aprobación</h2>
        <table>
            <thead>
                <tr>
                    <th style="width: 22%;">Etapa</th>
                    <th style="width: 28%;">Responsable</th>
                    <th style="width: 18%;">Fecha</th>
                    <th style="width: 32%;">Decisión</th>
                </tr>
            </thead>
            <tbody>
                {{-- Fila 1: Docente --}}
                <tr>
                    <td><strong>Revisión Docente</strong></td>
                    <td>
                        {{ $decisionDocente->actor_nombre ?? '—' }}
                    </td>
                    <td>
                        @if($decisionDocente)
                            {{ \Carbon\Carbon::parse($decisionDocente->fecha)->format('d/m/Y H:i') }}
                        @else — @endif
                    </td>
                    <td>
                        @if($decisionDocente)
                            <span class="badge badge-ok">{{ $decisionDocente->accion }}</span>
                            @if($decisionDocente->comentario)
                                <div class="comentario">"{{ $decisionDocente->comentario }}"</div>
                            @endif
                        @else —
                        @endif
                    </td>
                </tr>

                {{-- Fila 2: Coordinador --}}
                <tr>
                    <td><strong>Revisión Coordinador</strong></td>
                    <td>
                        {{ $decisionCoordinador->actor_nombre ?? '—' }}
                    </td>
                    <td>
                        @if($decisionCoordinador)
                            {{ \Carbon\Carbon::parse($decisionCoordinador->fecha)->format('d/m/Y H:i') }}
                        @else — @endif
                    </td>
                    <td>
                        @if($decisionCoordinador)
                            <span class="badge badge-ok">{{ $decisionCoordinador->accion }}</span>
                            @if($decisionCoordinador->comentario)
                                <div class="comentario">"{{ $decisionCoordinador->comentario }}"</div>
                            @endif
                        @else —
                        @endif
                    </td>
                </tr>

                {{-- Fila 3: Admin (cierre) --}}
                <tr>
                    <td><strong>Cierre Administrativo</strong></td>
                    <td>
                        {{ $decisionAdmin->actor_nombre ?? '—' }}
                    </td>
                    <td>
                        @if($decisionAdmin)
                            {{ \Carbon\Carbon::parse($decisionAdmin->fecha)->format('d/m/Y H:i') }}
                        @else — @endif
                    </td>
                    <td>
                        @if($decisionAdmin)
                            <span class="badge badge-ok">{{ $decisionAdmin->accion }}</span>
                            @if($decisionAdmin->comentario)
                                <div class="comentario">"{{ $decisionAdmin->comentario }}"</div>
                            @endif
                        @else —
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- ══════════════════════════════════════════════ --}}
    {{-- FIRMA del administrador académico               --}}
    {{-- ══════════════════════════════════════════════ --}}
    <div class="firma">
        <div class="linea"></div>
        <div class="nombre">{{ $decisionAdmin->actor_nombre ?? 'Administración Académica' }}</div>
        <div class="cargo">Administrador Académico — UTEC</div>
    </div>

    {{-- Nota legal al pie --}}
    <div class="nota-legal">
        Este documento es generado automáticamente por el Sistema de Corrección de Notas de UTEC.<br>
        Folio único de verificación: SCN-{{ str_pad($solicitud->id, 6, '0', STR_PAD_LEFT) }}
    </div>

</body>
</html>
