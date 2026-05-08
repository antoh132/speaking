-- ============================================================
-- SpeakOn! — Seed Data Only
-- Run this AFTER schema.sql has been imported.
-- ============================================================

USE speakon_db;

-- ── 5 Levels ─────────────────────────────────────────────────────────────────
INSERT IGNORE INTO levels (id, name, description, order_index) VALUES
    (1, 'Basic Pronunciation',      'Latihan pengucapan kata dasar bahasa Inggris dengan benar', 1),
    (2, 'Intonation',               'Latihan intonasi dan ritme dalam kalimat bahasa Inggris',   2),
    (3, 'Guided Dialogue',          'Percakapan terbimbing dengan skrip dan panduan konteks',    3),
    (4, 'Scenario-based Roleplay',  'Simulasi situasi nyata seperti wawancara dan presentasi',   4),
    (5, 'Independent Speaking',     'Monolog bebas tanpa panduan untuk mengekspresikan ide',     5);

-- ── Default Super Admin ───────────────────────────────────────────────────────
-- Email   : admin@speakon.id
-- Password: Admin@SpeakOn2024!
-- IMPORTANT: Change this password immediately after first login!
INSERT IGNORE INTO users (
    id, full_name, email, password_hash, role, is_active, language_pref, created_at, updated_at, created_by
) VALUES (
    '00000000-0000-0000-0000-000000000001',
    'Super Admin',
    'admin@speakon.id',
    '$2y$12$EwWNtNdv.QSuWZa8aBl03.mbM30PRCT0BMBHFo6UaWzAY6r5ZBxd2',
    'superadmin',
    1,
    'id',
    NOW(),
    NOW(),
    NULL
);

-- ── Default Dosen ─────────────────────────────────────────────────────────────
-- Email   : dosen@speakon.id
-- Password: Dosen@SpeakOn2024!
INSERT IGNORE INTO users (
    id, full_name, email, password_hash, role, is_active, language_pref, created_at, updated_at, created_by
) VALUES (
    '00000000-0000-0000-0000-000000000002',
    'Budi Santoso',
    'dosen@speakon.id',
    '$2y$12$KMXT1UZCn/BTYq33qw9SOeTEOFH6rNxS18X.nnSYMTSHSJWTA7rn.',
    'dosen',
    1,
    'id',
    NOW(),
    NOW(),
    '00000000-0000-0000-0000-000000000001'
);

-- ── Default Siswa ─────────────────────────────────────────────────────────────
-- Email   : siswa@speakon.id
-- Password: Siswa@SpeakOn2024!
INSERT IGNORE INTO users (
    id, full_name, email, password_hash, role, is_active, language_pref, created_at, updated_at, created_by
) VALUES (
    '00000000-0000-0000-0000-000000000003',
    'Andi Pratama',
    'siswa@speakon.id',
    '$2y$12$MBtgl.70ouoMNQk6FBWfQuVN47/Qqx4W.FZSHnmKSW0mPaCoOVJby',
    'siswa',
    1,
    'id',
    NOW(),
    NOW(),
    '00000000-0000-0000-0000-000000000001'
);

-- ── Level 1 aktif untuk siswa default ────────────────────────────────────────
INSERT IGNORE INTO student_level_progress (
    id, student_id, level_id, status, unlocked_at, passed_at
) VALUES
    ('00000000-0000-0000-0000-000000000010', '00000000-0000-0000-0000-000000000003', 1, 'active', NOW(), NULL),
    ('00000000-0000-0000-0000-000000000011', '00000000-0000-0000-0000-000000000003', 2, 'active', NOW(), NULL),
    ('00000000-0000-0000-0000-000000000012', '00000000-0000-0000-0000-000000000003', 3, 'active', NOW(), NULL),
    ('00000000-0000-0000-0000-000000000013', '00000000-0000-0000-0000-000000000003', 4, 'active', NOW(), NULL),
    ('00000000-0000-0000-0000-000000000014', '00000000-0000-0000-0000-000000000003', 5, 'active', NOW(), NULL);
