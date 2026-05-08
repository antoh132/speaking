<?php
/**
 * SpeakOn! — Database Connection (PDO)
 *
 * Provides a singleton PDO instance connected to MySQL.
 * All queries MUST use prepared statements — never interpolate user input.
 *
 * Usage:
 *   require_once __DIR__ . '/db.php';
 *   $pdo = getDB();
 *   $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email');
 *   $stmt->execute([':email' => $email]);
 *   $user = $stmt->fetch();
 */

require_once __DIR__ . '/config.php';

/**
 * Returns the singleton PDO database connection.
 *
 * @throws RuntimeException if the connection cannot be established.
 * @return PDO
 */
function getDB(): PDO
{
    static $pdo = null;

    if ($pdo !== null) {
        return $pdo;
    }

    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=%s',
        DB_HOST,
        DB_PORT,
        DB_NAME,
        DB_CHARSET
    );

    $options = [
        // Throw PDOException on errors instead of returning false
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,

        // Return rows as associative arrays by default
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,

        // Disable emulated prepared statements — use real MySQL prepared statements
        // This prevents a class of SQL injection attacks
        PDO::ATTR_EMULATE_PREPARES   => false,

        // Persistent connections for performance under XAMPP
        PDO::ATTR_PERSISTENT         => false,

        // Ensure strict mode is active
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci, time_zone = '+00:00'",
    ];

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (PDOException $e) {
        // Log the real error internally but never expose credentials or stack traces
        $logMessage = sprintf(
            "[%s] DB connection failed: %s\n",
            date('Y-m-d H:i:s'),
            $e->getMessage()
        );

        $logDir = LOG_DIR;
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }
        @file_put_contents($logDir . 'db-errors.log', $logMessage, FILE_APPEND | LOCK_EX);

        // Return a generic error to the client
        http_response_code(503);
        header('Content-Type: application/json');
        echo json_encode([
            'error' => [
                'code'    => 'DB_UNAVAILABLE',
                'message' => 'Layanan database tidak tersedia. Silakan coba beberapa saat lagi.',
                'details' => null,
            ],
        ]);
        exit;
    }

    return $pdo;
}

/**
 * Checks whether the database connection is healthy.
 *
 * @return bool true if the database responds to a ping, false otherwise.
 */
function checkDBHealth(): bool
{
    try {
        $pdo = getDB();
        $pdo->query('SELECT 1');
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Generates a UUID v4 string.
 * Used for primary keys since MySQL does not have a built-in gen_random_uuid()
 * equivalent that is available in all versions.
 *
 * @return string UUID v4 e.g. "550e8400-e29b-41d4-a716-446655440000"
 */
function generateUUID(): string
{
    $data = random_bytes(16);

    // Set version to 0100 (UUID v4)
    $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
    // Set bits 6-7 to 10 (variant RFC 4122)
    $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}
