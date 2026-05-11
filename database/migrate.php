<?php
/**
 * SpeakOn! — Database Migration Script
 *
 * Jalankan file ini sekali untuk menambah kolom baru ke database yang sudah ada.
 * Usage: php database/migrate.php
 * Atau akses via browser: http://localhost/speakon/database/migrate.php
 */

require_once __DIR__ . '/../api/config/config.php';
require_once __DIR__ . '/../api/config/db.php';

$pdo = getDB();
$results = [];

// ── Migration 1: Tambah kolom task_index ke tabel recordings ─────────────────
try {
    // Cek apakah kolom sudah ada
    $check = $pdo->query("SHOW COLUMNS FROM recordings LIKE 'task_index'");
    if ($check->rowCount() === 0) {
        $pdo->exec("ALTER TABLE recordings ADD COLUMN task_index TINYINT NULL DEFAULT NULL COMMENT 'NULL=Step3 main, 0/1/2=Step2 task recordings'");
        $results[] = '✅ Kolom task_index berhasil ditambahkan ke tabel recordings.';
    } else {
        $results[] = 'ℹ️ Kolom task_index sudah ada di tabel recordings.';
    }
} catch (Exception $e) {
    $results[] = '❌ Gagal menambah kolom task_index: ' . $e->getMessage();
}

// Output hasil
header('Content-Type: text/html; charset=utf-8');
echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>SpeakOn! Migration</title>';
echo '<style>body{font-family:sans-serif;max-width:600px;margin:2rem auto;padding:1rem}';
echo '.ok{color:#2e7d32}.info{color:#1565c0}.err{color:#c62828}</style></head><body>';
echo '<h2>SpeakOn! — Database Migration</h2>';
foreach ($results as $r) {
    $cls = str_starts_with($r, '✅') ? 'ok' : (str_starts_with($r, 'ℹ️') ? 'info' : 'err');
    echo "<p class=\"$cls\">$r</p>";
}
echo '<p><a href="/speakon/dashboard-dosen.html">Kembali ke Dashboard Dosen</a></p>';
echo '</body></html>';
