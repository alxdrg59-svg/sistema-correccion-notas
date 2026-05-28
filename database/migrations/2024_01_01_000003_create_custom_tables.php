<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facultades', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->unsignedBigInteger('coordinador_id')->nullable();
        });

        Schema::create('carreras', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->unsignedBigInteger('facultad_id');
            $table->foreign('facultad_id')->references('id')->on('facultades');
        });

        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('correo')->unique();
            $table->string('password');
            $table->string('rol');
            $table->string('carnet')->nullable();
            $table->unsignedBigInteger('carrera_id')->nullable();
            $table->foreign('carrera_id')->references('id')->on('carreras');
        });

        Schema::table('facultades', function (Blueprint $table) {
            $table->foreign('coordinador_id')->references('id')->on('usuarios');
        });

        Schema::create('materias', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->unsignedBigInteger('carrera_id');
            $table->foreign('carrera_id')->references('id')->on('carreras');
        });

        Schema::create('ciclos_academicos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
        });

        Schema::create('periodos_correccion', function (Blueprint $table) {
            $table->id();
            $table->string('evaluacion');
            $table->unsignedBigInteger('ciclo_id');
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->tinyInteger('estado')->default(0);
            $table->foreign('ciclo_id')->references('id')->on('ciclos_academicos');
        });

        Schema::create('asignaciones_estudiante', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('estudiante_id');
            $table->unsignedBigInteger('materia_id');
            $table->string('seccion');
            $table->string('modalidad')->default('presencial');
            $table->foreign('estudiante_id')->references('id')->on('usuarios');
            $table->foreign('materia_id')->references('id')->on('materias');
        });

        Schema::create('asignaciones_docente', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('docente_id');
            $table->unsignedBigInteger('materia_id');
            $table->string('seccion');
            $table->foreign('docente_id')->references('id')->on('usuarios');
            $table->foreign('materia_id')->references('id')->on('materias');
        });

        Schema::create('solicitudes_correccion', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('estudiante_id');
            $table->unsignedBigInteger('materia_id');
            $table->string('seccion');
            $table->unsignedBigInteger('ciclo_id');
            $table->string('ciclo');
            $table->unsignedBigInteger('docente_id');
            $table->string('evaluacion');
            $table->decimal('nota_actual', 4, 1);
            $table->text('motivo');
            $table->string('estado')->default('pendiente_docente');
            $table->timestamp('fecha_solicitud')->useCurrent();
            $table->foreign('estudiante_id')->references('id')->on('usuarios');
            $table->foreign('materia_id')->references('id')->on('materias');
            $table->foreign('docente_id')->references('id')->on('usuarios');
            $table->foreign('ciclo_id')->references('id')->on('ciclos_academicos');
        });

        Schema::create('aprobaciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('solicitud_id');
            $table->unsignedBigInteger('usuario_id');
            $table->string('accion');
            $table->text('comentario')->nullable();
            $table->string('nota_sugerida_admin')->nullable();
            $table->timestamp('fecha')->useCurrent();
            $table->foreign('solicitud_id')->references('id')->on('solicitudes_correccion');
            $table->foreign('usuario_id')->references('id')->on('usuarios');
        });

        Schema::create('evidencias', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('solicitud_id');
            $table->unsignedBigInteger('usuario_id');
            $table->string('archivo');
            $table->string('descripcion')->nullable();
            $table->timestamp('fecha')->useCurrent();
            $table->foreign('solicitud_id')->references('id')->on('solicitudes_correccion');
            $table->foreign('usuario_id')->references('id')->on('usuarios');
        });

        Schema::create('historial_notas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('solicitud_id');
            $table->decimal('nota_anterior', 4, 1);
            $table->decimal('nota_nueva', 4, 1);
            $table->unsignedBigInteger('usuario_id');
            $table->timestamp('fecha')->useCurrent();
            $table->foreign('solicitud_id')->references('id')->on('solicitudes_correccion');
            $table->foreign('usuario_id')->references('id')->on('usuarios');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historial_notas');
        Schema::dropIfExists('evidencias');
        Schema::dropIfExists('aprobaciones');
        Schema::dropIfExists('solicitudes_correccion');
        Schema::dropIfExists('asignaciones_docente');
        Schema::dropIfExists('asignaciones_estudiante');
        Schema::dropIfExists('periodos_correccion');
        Schema::dropIfExists('ciclos_academicos');
        Schema::dropIfExists('materias');
        Schema::table('facultades', function (Blueprint $table) {
            $table->dropForeign(['coordinador_id']);
        });
        Schema::dropIfExists('usuarios');
        Schema::dropIfExists('carreras');
        Schema::dropIfExists('facultades');
    }
};
