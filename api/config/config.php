<?php
/**
 * SpeakOn! — Application Configuration
 *
 * Copy this file to config.local.php and override values for your environment.
 * Never commit real credentials to version control.
 */

// ── Load local overrides FIRST so they take precedence ───────────────────────
$localConfig = __DIR__ . '/config.local.php';
if (file_exists($localConfig)) {
    require_once $localConfig;
}

// ── Database ──────────────────────────────────────────────────────────────────
defined('DB_HOST')    || define('DB_HOST',    getenv('DB_HOST')    ?: 'localhost');
defined('DB_PORT')    || define('DB_PORT',    getenv('DB_PORT')    ?: '3306');
defined('DB_NAME')    || define('DB_NAME',    getenv('DB_NAME')    ?: 'speakon_db');
defined('DB_USER')    || define('DB_USER',    getenv('DB_USER')    ?: 'root');
defined('DB_PASS')    || define('DB_PASS',    getenv('DB_PASS')    ?: 'Hardiansyah_23');
defined('DB_CHARSET') || define('DB_CHARSET', 'utf8mb4');

// ── JWT ───────────────────────────────────────────────────────────────────────
defined('JWT_SECRET')         || define('JWT_SECRET',         getenv('JWT_SECRET')         ?: 'change_this_jwt_secret_in_production');
defined('JWT_REFRESH_SECRET') || define('JWT_REFRESH_SECRET', getenv('JWT_REFRESH_SECRET') ?: 'change_this_refresh_secret_in_production');
defined('JWT_ACCESS_EXPIRY')  || define('JWT_ACCESS_EXPIRY',  30 * 60);         // 30 minutes
defined('JWT_REFRESH_EXPIRY') || define('JWT_REFRESH_EXPIRY', 7 * 24 * 60 * 60); // 7 days

// ── File Upload ───────────────────────────────────────────────────────────────
defined('UPLOAD_DIR')          || define('UPLOAD_DIR',          __DIR__ . '/../../uploads/recordings/');
defined('UPLOAD_MAX_BYTES')    || define('UPLOAD_MAX_BYTES',    50 * 1024 * 1024); // 50 MB
defined('ALLOWED_AUDIO_TYPES') || define('ALLOWED_AUDIO_TYPES', ['audio/webm', 'video/webm', 'audio/mpeg', 'audio/mp3', 'audio/ogg', 'audio/wav', 'audio/x-wav', 'audio/ogg; codecs=opus', 'video/webm; codecs=opus']);

// ── Application ───────────────────────────────────────────────────────────────
defined('APP_ENV')              || define('APP_ENV',              getenv('APP_ENV') ?: 'development');
defined('APP_DEBUG')            || define('APP_DEBUG',            APP_ENV === 'development');
defined('BCRYPT_COST')          || define('BCRYPT_COST',          12);
defined('LOCKOUT_MAX_ATTEMPTS') || define('LOCKOUT_MAX_ATTEMPTS', 5);
defined('LOCKOUT_DURATION_MIN') || define('LOCKOUT_DURATION_MIN', 15); // minutes
defined('SESSION_TIMEOUT_MIN')  || define('SESSION_TIMEOUT_MIN',  30); // minutes

// ── Logging ───────────────────────────────────────────────────────────────────
defined('LOG_DIR') || define('LOG_DIR', __DIR__ . '/../../logs/');
