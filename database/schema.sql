-- ============================================================
-- SpeakOn! User Management System — Database Schema
-- MySQL / MariaDB (XAMPP)
-- ============================================================
-- Run this file via phpMyAdmin or MySQL CLI:
--   mysql -u root -p < database/schema.sql
-- ============================================================

-- Create and select database
CREATE DATABASE IF NOT EXISTS speakon_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE speakon_db;

-- ============================================================
-- TABLE: users
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    id            CHAR(36)     NOT NULL,
    full_name     VARCHAR(255) NOT NULL,
    email         VARCHAR(255) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,          -- bcrypt cost 12
    role          ENUM('superadmin','dosen','siswa') NOT NULL,
    is_active     TINYINT(1)   NOT NULL DEFAULT 1,
    language_pref ENUM('id','en') NOT NULL DEFAULT 'id',
    created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by    CHAR(36)     NULL,              -- superadmin who created this user
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_email (email),
    CONSTRAINT fk_users_created_by FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: refresh_tokens
-- ============================================================
CREATE TABLE IF NOT EXISTS refresh_tokens (
    id         CHAR(36)     NOT NULL,
    user_id    CHAR(36)     NOT NULL,
    token_hash VARCHAR(255) NOT NULL,             -- SHA-256 hash of the refresh token
    expires_at DATETIME     NOT NULL,
    created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    revoked_at DATETIME     NULL,                 -- NULL = still valid
    PRIMARY KEY (id),
    CONSTRAINT fk_refresh_tokens_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: login_attempts  (for account lockout)
-- ============================================================
CREATE TABLE IF NOT EXISTS login_attempts (
    id           CHAR(36)     NOT NULL,
    email        VARCHAR(255) NOT NULL,
    ip_address   VARCHAR(45)  NOT NULL,           -- supports IPv4 and IPv6
    attempted_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    success      TINYINT(1)   NOT NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: account_lockouts
-- ============================================================
CREATE TABLE IF NOT EXISTS account_lockouts (
    id           CHAR(36)     NOT NULL,
    user_id      CHAR(36)     NOT NULL,
    locked_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    locked_until DATETIME     NOT NULL,
    reason       VARCHAR(100) NOT NULL DEFAULT 'too_many_attempts',
    PRIMARY KEY (id),
    CONSTRAINT fk_account_lockouts_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: levels  (master data — 5 levels)
-- ============================================================
CREATE TABLE IF NOT EXISTS levels (
    id          TINYINT      NOT NULL,            -- 1..5
    name        VARCHAR(100) NOT NULL,
    description TEXT         NULL,
    order_index TINYINT      NOT NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: level_materials
-- ============================================================
CREATE TABLE IF NOT EXISTS level_materials (
    id          CHAR(36)     NOT NULL,
    level_id    TINYINT      NOT NULL,
    title       VARCHAR(255) NOT NULL,
    content     TEXT         NOT NULL,
    order_index SMALLINT     NOT NULL DEFAULT 0,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    CONSTRAINT fk_level_materials_level FOREIGN KEY (level_id) REFERENCES levels(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: student_level_progress
-- ============================================================
CREATE TABLE IF NOT EXISTS student_level_progress (
    id          CHAR(36)  NOT NULL,
    student_id  CHAR(36)  NOT NULL,
    level_id    TINYINT   NOT NULL,
    status      ENUM('locked','active','passed') NOT NULL DEFAULT 'locked',
    unlocked_at DATETIME  NULL,
    passed_at   DATETIME  NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_student_level (student_id, level_id),
    CONSTRAINT fk_slp_student FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_slp_level   FOREIGN KEY (level_id)   REFERENCES levels(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: recordings
-- ============================================================
CREATE TABLE IF NOT EXISTS recordings (
    id               CHAR(36)       NOT NULL,
    student_id       CHAR(36)       NOT NULL,
    level_id         TINYINT        NOT NULL,
    file_path        VARCHAR(500)   NOT NULL,     -- local path: uploads/recordings/{filename}
    file_size_bytes  INT            NOT NULL,
    duration_seconds DECIMAL(6,2)   NULL,
    uploaded_at      DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    is_current       TINYINT(1)     NOT NULL DEFAULT 1,
    task_index       TINYINT        NULL,         -- NULL = Step 3 main recording, 0/1/2 = Step 2 task recordings
    PRIMARY KEY (id),
    CONSTRAINT fk_recordings_student FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_recordings_level   FOREIGN KEY (level_id)   REFERENCES levels(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: feedback
-- ============================================================
CREATE TABLE IF NOT EXISTS feedback (
    id           CHAR(36)  NOT NULL,
    recording_id CHAR(36)  NOT NULL,
    dosen_id     CHAR(36)  NOT NULL,
    comment      TEXT      NOT NULL,              -- min 10 chars enforced at app level
    pass_status  ENUM('lulus','tidak_lulus') NOT NULL,
    created_at   DATETIME  NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME  NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    CONSTRAINT fk_feedback_recording FOREIGN KEY (recording_id) REFERENCES recordings(id) ON DELETE CASCADE,
    CONSTRAINT fk_feedback_dosen     FOREIGN KEY (dosen_id)     REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: notifications
-- ============================================================
CREATE TABLE IF NOT EXISTS notifications (
    id          CHAR(36)   NOT NULL,
    student_id  CHAR(36)   NOT NULL,
    feedback_id CHAR(36)   NOT NULL,
    is_read     TINYINT(1) NOT NULL DEFAULT 0,
    created_at  DATETIME   NOT NULL DEFAULT CURRENT_TIMESTAMP,
    read_at     DATETIME   NULL,
    PRIMARY KEY (id),
    CONSTRAINT fk_notifications_student  FOREIGN KEY (student_id)  REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_notifications_feedback FOREIGN KEY (feedback_id) REFERENCES feedback(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: audit_logs  (append-only)
-- ============================================================
CREATE TABLE IF NOT EXISTS audit_logs (
    id          BIGINT       NOT NULL AUTO_INCREMENT,
    user_id     CHAR(36)     NULL,                -- NULL for system events
    user_role   VARCHAR(20)  NULL,
    action_type VARCHAR(50)  NOT NULL,
    -- Valid action_type values:
    -- 'login', 'logout', 'login_failed', 'account_locked',
    -- 'user_created', 'user_updated', 'user_deactivated',
    -- 'recording_uploaded', 'feedback_given', 'feedback_updated',
    -- 'level_unlocked', 'notification_sent', 'notification_read',
    -- 'audit_log_exported'
    entity_type VARCHAR(50)  NULL,                -- 'user', 'recording', 'feedback', etc.
    entity_id   VARCHAR(255) NULL,                -- UUID of affected entity
    metadata    JSON         NULL,                -- additional context
    ip_address  VARCHAR(45)  NULL,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    -- NOTE: No FK on user_id intentionally — audit logs must survive user deletion
    -- Append-only enforced via MySQL user permissions (see Task 13.2)
    INDEX idx_audit_logs_user_id    (user_id),
    INDEX idx_audit_logs_action_type (action_type),
    INDEX idx_audit_logs_created_at  (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- INDEXES for performance
-- ============================================================

-- users
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_role  ON users(role);

-- refresh_tokens
CREATE INDEX idx_refresh_tokens_user    ON refresh_tokens(user_id);
CREATE INDEX idx_refresh_tokens_expires ON refresh_tokens(expires_at);

-- login_attempts
CREATE INDEX idx_login_attempts_email_time ON login_attempts(email, attempted_at);

-- account_lockouts
CREATE INDEX idx_account_lockouts_user  ON account_lockouts(user_id);
CREATE INDEX idx_account_lockouts_until ON account_lockouts(locked_until);

-- student_level_progress
CREATE INDEX idx_slp_student ON student_level_progress(student_id);

-- recordings
CREATE INDEX idx_recordings_student_level   ON recordings(student_id, level_id);
CREATE INDEX idx_recordings_student_current ON recordings(student_id, is_current);

-- feedback
CREATE INDEX idx_feedback_recording ON feedback(recording_id);
CREATE INDEX idx_feedback_dosen     ON feedback(dosen_id);

-- notifications
CREATE INDEX idx_notifications_student_unread ON notifications(student_id, is_read);

-- ============================================================
-- SEED DATA: 5 Levels
-- ============================================================
INSERT IGNORE INTO levels (id, name, description, order_index) VALUES
    (1, 'Basic Pronunciation',      'Latihan pengucapan kata dasar bahasa Inggris dengan benar', 1),
    (2, 'Intonation',               'Latihan intonasi dan ritme dalam kalimat bahasa Inggris',   2),
    (3, 'Guided Dialogue',          'Percakapan terbimbing dengan skrip dan panduan konteks',    3),
    (4, 'Scenario-based Roleplay',  'Simulasi situasi nyata seperti wawancara dan presentasi',   4),
    (5, 'Independent Speaking',     'Monolog bebas tanpa panduan untuk mengekspresikan ide',     5);

-- ============================================================
-- SEED DATA: Default Super Admin account
-- Email   : admin@speakon.id
-- Password: Admin@SpeakOn2024!
-- Hash    : bcrypt cost 12 (pre-generated)
-- IMPORTANT: Change this password immediately after first login!
-- ============================================================
INSERT IGNORE INTO users (
    id,
    full_name,
    email,
    password_hash,
    role,
    is_active,
    language_pref,
    created_at,
    updated_at,
    created_by
) VALUES (
    '00000000-0000-0000-0000-000000000001',
    'Super Admin',
    'admin@speakon.id',
    '$2y$12$EwWNtNdv.QSuWZa8aBl03.mbM30PRCT0BMBHFo6UaWzAY6r5ZBxd2', -- password: Admin@SpeakOn2024!
    'superadmin',
    1,
    'id',
    NOW(),
    NOW(),
    NULL
);

-- ============================================================
-- MySQL user for application (append-only on audit_logs)
-- Run these commands as MySQL root after importing schema:
--
--   CREATE USER IF NOT EXISTS 'speakon_app'@'localhost'
--       IDENTIFIED BY 'change_this_password_in_production';
--
--   GRANT SELECT, INSERT, UPDATE, DELETE ON speakon_db.users             TO 'speakon_app'@'localhost';
--   GRANT SELECT, INSERT, UPDATE, DELETE ON speakon_db.refresh_tokens    TO 'speakon_app'@'localhost';
--   GRANT SELECT, INSERT, UPDATE, DELETE ON speakon_db.login_attempts    TO 'speakon_app'@'localhost';
--   GRANT SELECT, INSERT, UPDATE, DELETE ON speakon_db.account_lockouts  TO 'speakon_app'@'localhost';
--   GRANT SELECT                         ON speakon_db.levels            TO 'speakon_app'@'localhost';
--   GRANT SELECT, INSERT, UPDATE, DELETE ON speakon_db.level_materials   TO 'speakon_app'@'localhost';
--   GRANT SELECT, INSERT, UPDATE, DELETE ON speakon_db.student_level_progress TO 'speakon_app'@'localhost';
--   GRANT SELECT, INSERT, UPDATE, DELETE ON speakon_db.recordings        TO 'speakon_app'@'localhost';
--   GRANT SELECT, INSERT, UPDATE, DELETE ON speakon_db.feedback          TO 'speakon_app'@'localhost';
--   GRANT SELECT, INSERT, UPDATE, DELETE ON speakon_db.notifications     TO 'speakon_app'@'localhost';
--   GRANT SELECT, INSERT                 ON speakon_db.audit_logs        TO 'speakon_app'@'localhost';
--   -- NOTE: No UPDATE or DELETE on audit_logs — enforces append-only (Req 9.6)
--
--   FLUSH PRIVILEGES;
-- ============================================================
