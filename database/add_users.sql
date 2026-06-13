-- ============================================================
-- SpeakOn! — Tambah 3 User Baru
-- Jalankan di phpMyAdmin atau MySQL CLI setelah schema.sql
--   mysql -u root -p speakon_db < database/add_users.sql
-- ============================================================

USE speakon_db;

-- ── Dosen 1 ───────────────────────────────────────────────────────────────────
-- Nama    : Budi Santoso
-- Email   : budi.santoso@speakon.id
-- Password: BudiD0sen!
INSERT IGNORE INTO users (
    id, full_name, email, password_hash, role, is_active, language_pref, created_at, updated_at, created_by
) VALUES (
    '00000000-0000-0000-0000-000000000004',
    'Budi Santoso',
    'budi.santoso@speakon.id',
    '$2y$12$7WeCQzdop.WwhJsMCdapge6Izsi4.9EFe9Q2sQGe2AyoA4.CClqsS',
    'dosen',
    1,
    'id',
    NOW(),
    NOW(),
    '00000000-0000-0000-0000-000000000001'
);

-- ── Dosen 2 ───────────────────────────────────────────────────────────────────
-- Nama    : Siti Rahayu
-- Email   : siti.rahayu@speakon.id
-- Password: SitiD0sen!
INSERT IGNORE INTO users (
    id, full_name, email, password_hash, role, is_active, language_pref, created_at, updated_at, created_by
) VALUES (
    '00000000-0000-0000-0000-000000000005',
    'Siti Rahayu',
    'siti.rahayu@speakon.id',
    '$2y$12$92gUv7ICG072jIFw7VN/KuKDVBEGAD27.W6bhrzvN5XoVWzGRVFTC',
    'dosen',
    1,
    'id',
    NOW(),
    NOW(),
    '00000000-0000-0000-0000-000000000001'
);

-- ── Siswa 1 ───────────────────────────────────────────────────────────────────
-- Nama    : Ahmad Fauzi
-- Email   : ahmad.fauzi@speakon.id
-- Password: AhmadS1swa!
INSERT IGNORE INTO users (
    id, full_name, email, password_hash, role, is_active, language_pref, created_at, updated_at, created_by
) VALUES (
    '00000000-0000-0000-0000-000000000006',
    'Ahmad Fauzi',
    'ahmad.fauzi@speakon.id',
    '$2y$12$l8BX.T.pfADi.wHRKoX5ze/KipgJgsvEfjIyBUogTXf/O9hgDAdtu',
    'siswa',
    1,
    'id',
    NOW(),
    NOW(),
    '00000000-0000-0000-0000-000000000001'
);

-- ── Level progress untuk Ahmad Fauzi (siswa baru) ────────────────────────────
-- Level 1 active, level 2-5 locked
INSERT IGNORE INTO student_level_progress (
    id, student_id, level_id, status, unlocked_at, passed_at
) VALUES
    ('00000000-0000-0000-0000-000000000020', '00000000-0000-0000-0000-000000000006', 1, 'active', NOW(), NULL),
    ('00000000-0000-0000-0000-000000000021', '00000000-0000-0000-0000-000000000006', 2, 'locked', NULL, NULL),
    ('00000000-0000-0000-0000-000000000022', '00000000-0000-0000-0000-000000000006', 3, 'locked', NULL, NULL),
    ('00000000-0000-0000-0000-000000000023', '00000000-0000-0000-0000-000000000006', 4, 'locked', NULL, NULL),
    ('00000000-0000-0000-0000-000000000024', '00000000-0000-0000-0000-000000000006', 5, 'locked', NULL, NULL);
