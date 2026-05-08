<?php
/**
 * SpeakOn! — Production Config Template untuk InfinityFree
 *
 * CARA PAKAI:
 * 1. Copy file ini ke: api/config/config.local.php
 * 2. Isi dengan credentials dari InfinityFree cPanel
 * 3. JANGAN commit file config.local.php ke GitHub!
 *
 * Di InfinityFree, credentials database ada di:
 * cPanel → MySQL Databases → lihat nama DB, username, password
 */

// ── Database InfinityFree ─────────────────────────────────────────────────────
// Nama database di InfinityFree biasanya: epiz_XXXXXXX_speakon_db
// Username biasanya: epiz_XXXXXXX
// Host biasanya: sql200.infinityfree.com (cek di cPanel)

define('DB_HOST', 'sql200.infinityfree.com');  // ganti sesuai cPanel
define('DB_NAME', 'epiz_XXXXXXX_speakon');     // ganti dengan nama DB kamu
define('DB_USER', 'epiz_XXXXXXX');             // ganti dengan username DB kamu
define('DB_PASS', 'password_database_kamu');   // ganti dengan password DB kamu

// ── JWT Secrets (ganti dengan string acak yang kuat!) ─────────────────────────
define('JWT_SECRET',         'ganti_dengan_32_karakter_acak_production');
define('JWT_REFRESH_SECRET', 'ganti_dengan_32_karakter_acak_berbeda');

// ── Environment ───────────────────────────────────────────────────────────────
define('APP_ENV', 'production');
