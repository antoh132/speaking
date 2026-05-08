<?php
/**
 * SpeakOn! — Application Configuration
 *
 * Copy this file to config.local.php and override values for your environment.
 * Never commit real credentials to version control.
 */

// ── Database ──────────────────────────────────────────────────────────────────
define('DB_HOST',    getenv('DB_HOST')    ?: 'localhost');
define('DB_PORT',    getenv('DB_PORT')    ?: '3306');
define('DB_NAME',    getenv('DB_NAME')    ?: 'speakon_db');
define('DB_USER',    getenv('DB_USER')    ?: 'root');
define('DB_PASS',    getenv('DB_PASS')    ?: '');
define('DB_CHARSET', 'utf8mb4');

// ── JWT ───────────────────────────────────────────────────────────────────────
define('JWT_SECRET',          getenv('JWT_SECRET')          ?: 'change_this_jwt_secret_in_production');
define('JWT_REFRESH_SECRET',  getenv('JWT_REFRESH_SECRET')  ?: 'change_this_refresh_secret_in_production');
define('JWT_ACCESS_EXPIRY',   30 * 60);          // 30 minutes in seconds
define('JWT_REFRESH_EXPIRY',  7 * 24 * 60 * 60); // 7 days in seconds

// ── File Upload ───────────────────────────────────────────────────────────────
define('UPLOAD_DIR',          __DIR__ . '/../../uploads/recordings/');
define('UPLOAD_MAX_BYTES',    50 * 1024 * 1024); // 50 MB
define('ALLOWED_AUDIO_TYPES', ['audio/webm', 'video/webm', 'audio/mpeg', 'audio/mp3', 'audio/ogg', 'audio/wav', 'audio/x-wav', 'audio/ogg; codecs=opus', 'video/webm; codecs=opus']);

// ── Application ───────────────────────────────────────────────────────────────
define('APP_ENV',             getenv('APP_ENV') ?: 'development');
define('APP_DEBUG',           APP_ENV === 'development');
define('BCRYPT_COST',         12);
define('LOCKOUT_MAX_ATTEMPTS', 5);
define('LOCKOUT_DURATION_MIN', 15);              // minutes
define('SESSION_TIMEOUT_MIN',  30);              // minutes

// ── Logging ───────────────────────────────────────────────────────────────────
define('LOG_DIR', __DIR__ . '/../../logs/');

// ── Load local overrides if present ──────────────────────────────────────────
$localConfig = __DIR__ . '/config.local.php';
if (file_exists($localConfig)) {
    require_once $localConfig;
}
