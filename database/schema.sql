-- PyAprende: esquema de base de datos (MySQL / MariaDB de XAMPP)
-- Importar desde phpMyAdmin o con:  mysql -u root < database/schema.sql

CREATE DATABASE IF NOT EXISTS pyaprende CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE pyaprende;

CREATE TABLE IF NOT EXISTS users (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username      VARCHAR(40)  NOT NULL UNIQUE,
    email         VARCHAR(120) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    avatar        VARCHAR(16)  NOT NULL DEFAULT '🐍',
    xp            INT UNSIGNED NOT NULL DEFAULT 0,
    streak        INT UNSIGNED NOT NULL DEFAULT 0,
    last_active   DATE NULL,
    created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Cada paso (quiz o ejercicio) resuelto dentro de una lección.
CREATE TABLE IF NOT EXISTS step_progress (
    user_id     INT UNSIGNED NOT NULL,
    lesson_slug VARCHAR(64)  NOT NULL,
    step_index  SMALLINT UNSIGNED NOT NULL,
    xp_earned   SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    completed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, lesson_slug, step_index),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Lecciones terminadas.
CREATE TABLE IF NOT EXISTS lesson_progress (
    user_id      INT UNSIGNED NOT NULL,
    lesson_slug  VARCHAR(64)  NOT NULL,
    completed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, lesson_slug),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Logros desbloqueados.
CREATE TABLE IF NOT EXISTS user_achievements (
    user_id     INT UNSIGNED NOT NULL,
    code        VARCHAR(40)  NOT NULL,
    unlocked_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, code),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Contadores de actividad (ejecuciones de código, gráficos...).
CREATE TABLE IF NOT EXISTS user_stats (
    user_id    INT UNSIGNED NOT NULL,
    stat       VARCHAR(40)  NOT NULL,
    value      INT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (user_id, stat),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;
