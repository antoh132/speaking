-- ============================================================
-- Migration: Tambah kolom attempt_number ke tabel recordings
-- Jalankan sekali di phpMyAdmin atau MySQL CLI
-- ============================================================

USE speakon_db;

-- Tambah kolom attempt_number (1, 2, atau 3 untuk main recording; NULL untuk task/skenario)
ALTER TABLE recordings
    ADD COLUMN attempt_number TINYINT UNSIGNED NULL
    COMMENT 'Nomor percobaan ke-N (1/2/3) untuk main recording; NULL untuk task/skenario'
    AFTER task_index;

-- Isi attempt_number untuk rekaman yang sudah ada (hitung urutan per student+level)
UPDATE recordings r
JOIN (
    SELECT id,
           ROW_NUMBER() OVER (PARTITION BY student_id, level_id ORDER BY uploaded_at ASC) AS rn
    FROM recordings
    WHERE task_index IS NULL
) ranked ON r.id = ranked.id
SET r.attempt_number = ranked.rn
WHERE r.task_index IS NULL;
