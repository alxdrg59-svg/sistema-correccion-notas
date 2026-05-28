<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('1234');

        // Facultades
        DB::table('facultades')->insert([
            ['id' => 1, 'nombre' => 'Facultad de Ingeniería', 'coordinador_id' => null],
        ]);

        // Carreras
        DB::table('carreras')->insert([
            ['id' => 1, 'nombre' => 'Ingeniería en Sistemas', 'facultad_id' => 1],
            ['id' => 2, 'nombre' => 'Ingeniería Industrial', 'facultad_id' => 1],
        ]);

        // Usuarios (password: 1234 para todos)
        DB::table('usuarios')->insert([
            ['id' => 1, 'nombre' => 'Admin UTEC', 'correo' => 'admin@utec.com', 'password' => $password, 'rol' => 'admin', 'carnet' => null, 'carrera_id' => null],
            ['id' => 2, 'nombre' => 'Carlos Coordinador', 'correo' => 'coordinador@utec.com', 'password' => $password, 'rol' => 'coordinador', 'carnet' => null, 'carrera_id' => null],
            ['id' => 3, 'nombre' => 'Luis Martínez', 'correo' => 'luis.m@utec.com', 'password' => $password, 'rol' => 'docente', 'carnet' => null, 'carrera_id' => null],
            ['id' => 4, 'nombre' => 'María Docente', 'correo' => 'maria.d@utec.com', 'password' => $password, 'rol' => 'docente', 'carnet' => null, 'carrera_id' => null],
            ['id' => 5, 'nombre' => 'Juan Estudiante', 'correo' => 'juan.e@utec.com', 'password' => $password, 'rol' => 'estudiante', 'carnet' => 'EST-2024-001', 'carrera_id' => 1],
            ['id' => 6, 'nombre' => 'Ana Estudiante', 'correo' => 'ana.e@utec.com', 'password' => $password, 'rol' => 'estudiante', 'carnet' => 'EST-2024-002', 'carrera_id' => 1],
        ]);

        // Asignar coordinador a la facultad
        DB::table('facultades')->where('id', 1)->update(['coordinador_id' => 2]);

        // Materias
        DB::table('materias')->insert([
            ['id' => 1, 'nombre' => 'Programación I', 'carrera_id' => 1],
            ['id' => 2, 'nombre' => 'Base de Datos', 'carrera_id' => 1],
            ['id' => 3, 'nombre' => 'Matemática I', 'carrera_id' => 1],
        ]);

        // Ciclos académicos
        DB::table('ciclos_academicos')->insert([
            ['id' => 1, 'nombre' => 'Ciclo 01-2026'],
        ]);

        // Periodo de corrección activo
        DB::table('periodos_correccion')->insert([
            ['id' => 1, 'evaluacion' => 'Parcial 1', 'ciclo_id' => 1, 'fecha_inicio' => '2026-05-01', 'fecha_fin' => '2026-06-30', 'estado' => 1],
        ]);

        // Asignaciones docente
        DB::table('asignaciones_docente')->insert([
            ['docente_id' => 3, 'materia_id' => 1, 'seccion' => 'A'],
            ['docente_id' => 3, 'materia_id' => 2, 'seccion' => 'A'],
            ['docente_id' => 4, 'materia_id' => 3, 'seccion' => 'B'],
        ]);

        // Asignaciones estudiante
        DB::table('asignaciones_estudiante')->insert([
            ['estudiante_id' => 5, 'materia_id' => 1, 'seccion' => 'A', 'modalidad' => 'presencial'],
            ['estudiante_id' => 5, 'materia_id' => 2, 'seccion' => 'A', 'modalidad' => 'presencial'],
            ['estudiante_id' => 5, 'materia_id' => 3, 'seccion' => 'B', 'modalidad' => 'virtual'],
            ['estudiante_id' => 6, 'materia_id' => 1, 'seccion' => 'A', 'modalidad' => 'presencial'],
            ['estudiante_id' => 6, 'materia_id' => 3, 'seccion' => 'B', 'modalidad' => 'presencial'],
        ]);
    }
}
