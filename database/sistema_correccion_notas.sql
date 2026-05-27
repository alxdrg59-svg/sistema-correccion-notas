-- =====================================================
-- Script de creación de la base de datos
-- Sistema de Corrección de Notas — UTEC
-- Ejecutar en phpMyAdmin: pestaña "Importar" o "SQL"
-- =====================================================
-- IMPORTANTE — si se importa desde línea de comandos:
--   mysql -u root -p --default-character-set=utf8mb4 < sistema_correccion_notas.sql
-- En PowerShell, NO uses "Get-Content ... | mysql ..." porque PowerShell
-- recodifica el archivo y los acentos se pierden. Usa redirección con cmd:
--   cmd /c "mysql -u root --default-character-set=utf8mb4 < sistema_correccion_notas.sql"
-- =====================================================

-- Fuerza la sesión actual a utf8mb4 para que los INSERT con acentos
-- se guarden con la codificación correcta sin importar la config global.
SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

CREATE DATABASE IF NOT EXISTS `sistema_correccion_notas`
    DEFAULT CHARACTER SET utf8mb4
    DEFAULT COLLATE utf8mb4_unicode_ci;

USE `sistema_correccion_notas`;

-- ----------------------------------------------------- 
--  FACULTADES 
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `facultades` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nombre` VARCHAR(150) NOT NULL,
    `coordinador_id` INT NULL,
    INDEX (`coordinador_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------- 
--  CARRERAS
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `carreras` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nombre` VARCHAR(150) NOT NULL,
    `facultad_id` INT NOT NULL,
    FOREIGN KEY (`facultad_id`) REFERENCES `facultades`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------- 
--  USUARIOS (estudiantes, docentes, coordinadores, admin)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `usuarios` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nombre` VARCHAR(150) NOT NULL,
    `correo` VARCHAR(150) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `rol` ENUM('estudiante','docente','coordinador','admin') NOT NULL,
    `carnet` VARCHAR(20) NULL,
    `carrera_id` INT NULL,
    `remember_token` VARCHAR(100) NULL,
    FOREIGN KEY (`carrera_id`) REFERENCES `carreras`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- FK de coordinador en facultades (se agrega después porque depende de usuarios)
ALTER TABLE `facultades`
    ADD CONSTRAINT `fk_facultad_coordinador`
    FOREIGN KEY (`coordinador_id`) REFERENCES `usuarios`(`id`) ON DELETE SET NULL;

-- ----------------------------------------------------- 
--  CICLOS ACADEMICOS 
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `ciclos_academicos` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nombre` VARCHAR(50) NOT NULL,
    `fecha_inicio` DATE NOT NULL,
    `fecha_fin` DATE NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------- 
--  MATERIAS 
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `materias` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nombre` VARCHAR(150) NOT NULL,
    `carrera_id` INT NOT NULL,
    FOREIGN KEY (`carrera_id`) REFERENCES `carreras`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------- 
--  PERIODOS DE CORRECCION (ventana en la que se aceptan solicitudes)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `periodos_correccion` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `evaluacion` VARCHAR(80) NOT NULL,
    `ciclo_id` INT NOT NULL,
    `fecha_inicio` DATE NOT NULL,
    `fecha_fin` DATE NOT NULL,
    `estado` TINYINT(1) NOT NULL DEFAULT 0,
    FOREIGN KEY (`ciclo_id`) REFERENCES `ciclos_academicos`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------- 
--  ASIGNACIONES DOCENTE (qué docente imparte qué materia/sección)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `asignaciones_docente` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `docente_id` INT NOT NULL,
    `materia_id` INT NOT NULL,
    `seccion` VARCHAR(20) NOT NULL,
    FOREIGN KEY (`docente_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`materia_id`) REFERENCES `materias`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------- 
--  ASIGNACIONES ESTUDIANTE (qué estudiante cursa qué materia/sección)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `asignaciones_estudiante` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `estudiante_id` INT NOT NULL,
    `materia_id` INT NOT NULL,
    `seccion` VARCHAR(20) NOT NULL,
    `modalidad` ENUM('presencial','virtual') NOT NULL DEFAULT 'presencial',
    FOREIGN KEY (`estudiante_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`materia_id`) REFERENCES `materias`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------- 
--  SOLICITUDES DE CORRECCION 
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `solicitudes_correccion` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `estudiante_id` INT NOT NULL,
    `materia_id` INT NOT NULL,
    `docente_id` INT NOT NULL,
    `seccion` VARCHAR(20) NOT NULL,
    `evaluacion` VARCHAR(80) NOT NULL,
    `ciclo_id` INT NOT NULL,
    `ciclo` VARCHAR(50) NOT NULL,
    `nota_actual` DECIMAL(4,2) NOT NULL,
    `motivo` TEXT NOT NULL,
    `estado` ENUM(
        'pendiente_docente',
        'rechazado_docente',
        'pendiente_coordinador',
        'rechazado_coordinador',
        'pendiente_admin',
        'finalizado'
    ) NOT NULL DEFAULT 'pendiente_docente',
    `fecha_solicitud` DATETIME NOT NULL,
    FOREIGN KEY (`estudiante_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`materia_id`)    REFERENCES `materias`(`id`)  ON DELETE CASCADE,
    FOREIGN KEY (`docente_id`)    REFERENCES `usuarios`(`id`)  ON DELETE CASCADE,
    FOREIGN KEY (`ciclo_id`)      REFERENCES `ciclos_academicos`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------- 
--  APROBACIONES (historial de acciones de cada actor)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `aprobaciones` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `solicitud_id` INT NOT NULL,
    `usuario_id` INT NOT NULL,
    `accion` VARCHAR(150) NOT NULL,
    `comentario` TEXT NULL,
    `nota_sugerida_admin` VARCHAR(255) NULL,
    `fecha` DATETIME NOT NULL,
    FOREIGN KEY (`solicitud_id`) REFERENCES `solicitudes_correccion`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`usuario_id`)   REFERENCES `usuarios`(`id`)               ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------- 
--  EVIDENCIAS (archivos adjuntos del docente)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `evidencias` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `solicitud_id` INT NOT NULL,
    `usuario_id` INT NOT NULL,
    `archivo` VARCHAR(255) NOT NULL,
    `descripcion` VARCHAR(255) NULL,
    `fecha` DATETIME NOT NULL,
    FOREIGN KEY (`solicitud_id`) REFERENCES `solicitudes_correccion`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`usuario_id`)   REFERENCES `usuarios`(`id`)               ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------- 
--  HISTORIAL DE NOTAS (queda registro tras finalizar)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `historial_notas` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `solicitud_id` INT NOT NULL,
    `nota_anterior` DECIMAL(4,2) NOT NULL,
    `nota_nueva` DECIMAL(4,2) NOT NULL,
    `usuario_id` INT NOT NULL,
    `fecha` DATETIME NOT NULL,
    FOREIGN KEY (`solicitud_id`) REFERENCES `solicitudes_correccion`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`usuario_id`)   REFERENCES `usuarios`(`id`)               ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- DATOS DE PRUEBA (semilla mínima para poder hacer login)
-- Las contraseñas estan en texto plano "1234" — encriptar
-- visitando /encriptar-mi-clave o usando seeders después
-- =====================================================

INSERT INTO `facultades` (`nombre`) VALUES
    ('Facultad de Informática y Ciencias Aplicadas');

INSERT INTO `carreras` (`nombre`, `facultad_id`) VALUES
    ('Ingeniería en Sistemas', 1);

INSERT INTO `ciclos_academicos` (`nombre`, `fecha_inicio`, `fecha_fin`) VALUES
    ('Ciclo 01-2026', '2026-01-15', '2026-05-30');

-- Bcrypt hash REAL de "1234" (rounds=12) — todos los usuarios usan esa contraseña
INSERT INTO `usuarios` (`nombre`, `correo`, `password`, `rol`, `carnet`, `carrera_id`) VALUES
    ('Luis Mendoza', 'luis.m@utec.com',  '$2y$12$1agKoZ.AFoeUd5p4fOSMuewQY5p2ckVXXqKQs4JpJmG3//9/5OCzO', 'estudiante',   '24-0001-2024', 1),
    ('Ana Docente',  'ana.d@utec.com',   '$2y$12$1agKoZ.AFoeUd5p4fOSMuewQY5p2ckVXXqKQs4JpJmG3//9/5OCzO', 'docente',      NULL, NULL),
    ('Coord Facu',   'coord@utec.com',   '$2y$12$1agKoZ.AFoeUd5p4fOSMuewQY5p2ckVXXqKQs4JpJmG3//9/5OCzO', 'coordinador',  NULL, NULL),
    ('Admin Master', 'admin@utec.com',   '$2y$12$1agKoZ.AFoeUd5p4fOSMuewQY5p2ckVXXqKQs4JpJmG3//9/5OCzO', 'admin',        NULL, NULL);

UPDATE `facultades` SET `coordinador_id` = 3 WHERE `id` = 1;

INSERT INTO `materias` (`nombre`, `carrera_id`) VALUES
    ('Programación I', 1),
    ('Bases de Datos', 1);

INSERT INTO `asignaciones_docente` (`docente_id`, `materia_id`, `seccion`) VALUES
    (2, 1, '01'),
    (2, 2, '01');

INSERT INTO `asignaciones_estudiante` (`estudiante_id`, `materia_id`, `seccion`, `modalidad`) VALUES
    (1, 1, '01', 'presencial'),
    (1, 2, '01', 'presencial');

INSERT INTO `periodos_correccion` (`evaluacion`, `ciclo_id`, `fecha_inicio`, `fecha_fin`, `estado`) VALUES
    ('Primera Evaluación', 1, '2026-01-15', '2026-12-30', 1);
