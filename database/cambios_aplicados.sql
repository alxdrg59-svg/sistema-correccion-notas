-- ============================================================
-- SCRIPTS DE CAMBIOS APLICADOS A LA BASE DE DATOS
-- Sistema de Corrección de Notas — UTEC
--
-- Este archivo documenta todos los cambios SQL ejecutados
-- sobre la base de datos original (definida en la migración
-- de Laravel 2024_01_01_000003_create_custom_tables.php)
-- para llegar al esquema actual en producción.
--
-- Base de datos: sistema_correccion_db
-- Servidor: Google Cloud SQL (MySQL 8.0)
-- Instancia: sistema-correccion
-- ============================================================

-- ============================================================
-- SECCIÓN 1: CAMBIOS DE TIPO DE DATO EN TABLAS EXISTENTES
-- ============================================================

-- 1.1 usuarios — cambiar rol de VARCHAR a ENUM
ALTER TABLE `usuarios`
  MODIFY COLUMN `rol` ENUM('estudiante','docente','coordinador','admin')
  COLLATE utf8mb4_unicode_ci NOT NULL;

-- 1.2 usuarios — agregar columna estado (activo/inactivo)
ALTER TABLE `usuarios`
  ADD COLUMN `estado` TINYINT DEFAULT 1;

-- 1.3 usuarios — agregar columna fecha_creacion
ALTER TABLE `usuarios`
  ADD COLUMN `fecha_creacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

-- 1.4 usuarios — agregar índice único a carnet
ALTER TABLE `usuarios`
  ADD UNIQUE KEY `carnet` (`carnet`);

-- ============================================================

-- 1.5 solicitudes_correccion — cambiar evaluacion de VARCHAR a ENUM
ALTER TABLE `solicitudes_correccion`
  MODIFY COLUMN `evaluacion` ENUM('Evaluacion 1','Evaluacion 2','Evaluacion 3','Evaluacion 4','Evaluacion 5')
  COLLATE utf8mb4_unicode_ci NOT NULL;

-- 1.6 solicitudes_correccion — cambiar estado de VARCHAR a ENUM
ALTER TABLE `solicitudes_correccion`
  MODIFY COLUMN `estado` ENUM('pendiente_docente','rechazado_docente','pendiente_coordinador','rechazado_coordinador','pendiente_admin','finalizado')
  COLLATE utf8mb4_unicode_ci DEFAULT 'pendiente_docente';

-- 1.7 solicitudes_correccion — cambiar nota_actual de DECIMAL(4,1) a DECIMAL(4,2)
ALTER TABLE `solicitudes_correccion`
  MODIFY COLUMN `nota_actual` DECIMAL(4,2) NOT NULL;

-- 1.8 solicitudes_correccion — agregar columna es_excepcion
ALTER TABLE `solicitudes_correccion`
  ADD COLUMN `es_excepcion` TINYINT(1) DEFAULT 0;

-- ============================================================

-- 1.9 periodos_correccion — cambiar evaluacion de VARCHAR a ENUM
ALTER TABLE `periodos_correccion`
  MODIFY COLUMN `evaluacion` ENUM('Evaluacion 1','Evaluacion 2','Evaluacion 3','Evaluacion 4','Evaluacion 5')
  COLLATE utf8mb4_unicode_ci NOT NULL;

-- 1.10 periodos_correccion — agregar columna descripcion
ALTER TABLE `periodos_correccion`
  ADD COLUMN `descripcion` VARCHAR(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL;

-- ============================================================

-- 1.11 historial_notas — cambiar nota_anterior de DECIMAL(4,1) a DECIMAL(4,2)
ALTER TABLE `historial_notas`
  MODIFY COLUMN `nota_anterior` DECIMAL(4,2) NOT NULL;

-- 1.12 historial_notas — cambiar nota_nueva de DECIMAL(4,1) a DECIMAL(4,2)
ALTER TABLE `historial_notas`
  MODIFY COLUMN `nota_nueva` DECIMAL(4,2) NOT NULL;

-- ============================================================

-- 1.13 ciclos_academicos — agregar columnas fecha_inicio, fecha_fin, estado
ALTER TABLE `ciclos_academicos`
  ADD COLUMN `fecha_inicio` DATE NOT NULL,
  ADD COLUMN `fecha_fin` DATE NOT NULL,
  ADD COLUMN `estado` ENUM('activo','inactivo') COLLATE utf8mb4_unicode_ci DEFAULT 'inactivo';

-- 1.14 ciclos_academicos — agregar índice único a nombre
ALTER TABLE `ciclos_academicos`
  ADD UNIQUE KEY `nombre` (`nombre`);

-- ============================================================

-- 1.15 facultades — agregar columna descripcion
ALTER TABLE `facultades`
  ADD COLUMN `descripcion` TEXT COLLATE utf8mb4_unicode_ci;

-- ============================================================

-- 1.16 materias — agregar columna codigo
ALTER TABLE `materias`
  ADD COLUMN `codigo` VARCHAR(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  ADD UNIQUE KEY `codigo` (`codigo`);

-- ============================================================

-- 1.17 asignaciones_docente — agregar columnas ciclo_id, modalidad, ciclo, estado
ALTER TABLE `asignaciones_docente`
  ADD COLUMN `ciclo_id` INT NOT NULL,
  ADD COLUMN `modalidad` ENUM('presencial','virtual') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'presencial',
  ADD COLUMN `ciclo` VARCHAR(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  ADD COLUMN `estado` ENUM('activa','retirada') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'activa',
  ADD KEY `ciclo_id` (`ciclo_id`),
  ADD CONSTRAINT `asignaciones_docente_ibfk_3` FOREIGN KEY (`ciclo_id`) REFERENCES `ciclos_academicos` (`id`);

-- 1.18 asignaciones_docente — agregar restricciones únicas
ALTER TABLE `asignaciones_docente`
  ADD UNIQUE KEY `unique_docente_materia_seccion_ciclo` (`docente_id`, `materia_id`, `seccion`, `ciclo_id`),
  ADD UNIQUE KEY `unique_materia_seccion_ciclo` (`materia_id`, `seccion`, `ciclo_id`);

-- ============================================================

-- 1.19 asignaciones_estudiante — agregar columnas ciclo_id, ciclo, estado
ALTER TABLE `asignaciones_estudiante`
  ADD COLUMN `ciclo_id` INT NOT NULL,
  ADD COLUMN `ciclo` VARCHAR(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  ADD COLUMN `estado` ENUM('activa','retirada') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'activa',
  ADD KEY `ciclo_id` (`ciclo_id`),
  ADD CONSTRAINT `asignaciones_estudiante_ibfk_3` FOREIGN KEY (`ciclo_id`) REFERENCES `ciclos_academicos` (`id`);

-- 1.20 asignaciones_estudiante — agregar restricción única
ALTER TABLE `asignaciones_estudiante`
  ADD UNIQUE KEY `unique_estudiante_materia_seccion_ciclo` (`estudiante_id`, `materia_id`, `seccion`, `ciclo_id`);


-- ============================================================
-- SECCIÓN 2: TABLAS NUEVAS CREADAS
-- ============================================================

-- 2.1 bitacora — Auditoría de acciones del sistema
CREATE TABLE `bitacora` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `usuario_id` INT NOT NULL,
  `accion` VARCHAR(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `detalle` TEXT COLLATE utf8mb4_unicode_ci,
  `fecha` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `bitacora_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2.2 inscripciones_ciclo — Inscripción de estudiantes por ciclo
CREATE TABLE `inscripciones_ciclo` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `estudiante_id` INT NOT NULL,
  `ciclo_id` INT NOT NULL,
  `estado` ENUM('inscrito','retirado','finalizado') COLLATE utf8mb4_unicode_ci DEFAULT 'inscrito',
  `fecha_inscripcion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_inscripcion_ciclo` (`estudiante_id`, `ciclo_id`),
  KEY `ciclo_id` (`ciclo_id`),
  CONSTRAINT `inscripciones_ciclo_ibfk_1` FOREIGN KEY (`estudiante_id`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `inscripciones_ciclo_ibfk_2` FOREIGN KEY (`ciclo_id`) REFERENCES `ciclos_academicos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2.3 notas — Calificaciones reales de estudiantes
CREATE TABLE `notas` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `estudiante_id` INT NOT NULL,
  `materia_id` INT NOT NULL,
  `evaluacion` VARCHAR(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nota` DECIMAL(4,2) NOT NULL,
  `ciclo_id` INT NOT NULL,
  `ciclo` VARCHAR(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_nota_estudiante_materia_evaluacion_ciclo` (`estudiante_id`, `materia_id`, `evaluacion`, `ciclo_id`),
  KEY `materia_id` (`materia_id`),
  KEY `ciclo_id` (`ciclo_id`),
  CONSTRAINT `notas_ibfk_1` FOREIGN KEY (`estudiante_id`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `notas_ibfk_2` FOREIGN KEY (`materia_id`) REFERENCES `materias` (`id`),
  CONSTRAINT `notas_ibfk_3` FOREIGN KEY (`ciclo_id`) REFERENCES `ciclos_academicos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2.4 sessions — Sesiones de usuario (estándar Laravel)
CREATE TABLE `sessions` (
  `id` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` BIGINT UNSIGNED DEFAULT NULL,
  `ip_address` VARCHAR(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` TEXT COLLATE utf8mb4_unicode_ci,
  `payload` LONGTEXT COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` INT NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
