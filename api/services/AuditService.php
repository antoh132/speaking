<?php
/**
 * SpeakOn! — AuditService
 *
 * Provides append-only audit logging for all significant system actions.
 * Writes are non-blocking: if the database write fails, the error is
 * logged to a local file and execution continues.
 *
 * Requirements covered:
 *   - 1.7  : Log all authentication events
 *   - 9.1  : Audit log records all significant actions
 *   - 9.2  : Audit log includes user, action, entity, timestamp, IP
 *   - 9.5  : Audit log writes are non-blocking
 *   - 9.6  : Audit log is append-only (enforced at DB level)
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

class AuditService
{
    // ── Valid action types ────────────────────────────────────────────────────
    public const ACTION_LOGIN               = 'login';
    public const ACTION_LOGOUT              = 'logout';
    public const ACTION_LOGIN_FAILED        = 'login_failed';
    public const ACTION_ACCOUNT_LOCKED      = 'account_locked';
    public const ACTION_USER_CREATED        = 'user_created';
    public const ACTION_USER_UPDATED        = 'user_updated';
    public const ACTION_USER_DEACTIVATED    = 'user_deactivated';
    public const ACTION_RECORDING_UPLOADED  = 'recording_uploaded';
    public const ACTION_FEEDBACK_GIVEN      = 'feedback_given';
    public const ACTION_FEEDBACK_UPDATED    = 'feedback_updated';
    public const ACTION_LEVEL_UNLOCKED      = 'level_unlocked';
    public const ACTION_NOTIFICATION_SENT   = 'notification_sent';
    public const ACTION_NOTIFICATION_READ   = 'notification_read';
    public const ACTION_AUDIT_LOG_EXPORTED  = 'audit_log_exported';

    // ── Valid entity types ────────────────────────────────────────────────────
    public const ENTITY_USER         = 'user';
    public const ENTITY_RECORDING    = 'recording';
    public const ENTITY_FEEDBACK     = 'feedback';
    public const ENTITY_LEVEL        = 'level';
    public const ENTITY_NOTIFICATION = 'notification';
    public const ENTITY_AUDIT_LOG    = 'audit_log';
    public const ENTITY_SESSION      = 'session';

    /**
     * Write an audit log entry to the database.
     *
     * This method is non-blocking: any exception is caught and written to
     * a local error log file so that audit failures never break the main flow.
     *
     * @param  string|null  $userId      UUID of the acting user (null for anonymous actions).
     * @param  string|null  $userRole    Role of the acting user ('superadmin'|'dosen'|'siswa'|null).
     * @param  string       $actionType  One of the ACTION_* constants.
     * @param  string|null  $entityType  One of the ENTITY_* constants (or null).
     * @param  string|null  $entityId    UUID of the affected entity (or null).
     * @param  array|null   $metadata    Additional context as key-value pairs (will be JSON-encoded).
     * @param  string|null  $ipAddress   Client IP address.
     * @return void
     */
    public static function writeLog(
        ?string $userId,
        ?string $userRole,
        string  $actionType,
        ?string $entityType = null,
        ?string $entityId   = null,
        ?array  $metadata   = null,
        ?string $ipAddress  = null
    ): void {
        try {
            $pdo = getDB();

            $stmt = $pdo->prepare(
                'INSERT INTO audit_logs
                    (user_id, user_role, action_type, entity_type, entity_id, metadata, ip_address, created_at)
                 VALUES
                    (:user_id, :user_role, :action_type, :entity_type, :entity_id, :metadata, :ip_address, NOW())'
            );

            $stmt->execute([
                ':user_id'     => $userId,
                ':user_role'   => $userRole,
                ':action_type' => $actionType,
                ':entity_type' => $entityType,
                ':entity_id'   => $entityId,
                ':metadata'    => $metadata !== null ? json_encode($metadata, JSON_UNESCAPED_UNICODE) : null,
                ':ip_address'  => $ipAddress,
            ]);
        } catch (\Throwable $e) {
            // Non-blocking: log the failure to a local file and continue
            self::logError($e->getMessage(), $actionType, $userId);
        }
    }

    /**
     * Convenience method: log an authentication event.
     *
     * @param  string      $actionType  ACTION_LOGIN, ACTION_LOGOUT, ACTION_LOGIN_FAILED, or ACTION_ACCOUNT_LOCKED.
     * @param  string|null $userId      User UUID (null if user not found).
     * @param  string|null $userRole    User role.
     * @param  string      $email       Email used in the attempt.
     * @param  string      $ipAddress   Client IP address.
     * @return void
     */
    public static function logAuth(
        string  $actionType,
        ?string $userId,
        ?string $userRole,
        string  $email,
        string  $ipAddress
    ): void {
        self::writeLog(
            $userId,
            $userRole,
            $actionType,
            self::ENTITY_SESSION,
            null,
            ['email' => $email],
            $ipAddress
        );
    }

    /**
     * Get the client's IP address from the request.
     *
     * Checks X-Forwarded-For (for proxies/load balancers) before REMOTE_ADDR.
     *
     * @return string  The client IP address, or '0.0.0.0' if not determinable.
     */
    public static function getClientIp(): string
    {
        // Check for proxy-forwarded IP (take the first IP in the chain)
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip  = trim($ips[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }

        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Internal helpers
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Write an audit log error to the local error log file.
     *
     * @param  string      $errorMessage  The exception message.
     * @param  string      $actionType    The action that was being logged.
     * @param  string|null $userId        The user ID involved.
     * @return void
     */
    private static function logError(string $errorMessage, string $actionType, ?string $userId): void
    {
        $logDir = LOG_DIR;

        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }

        $line = sprintf(
            "[%s] Audit log write failed | action=%s | user=%s | error=%s\n",
            date('Y-m-d H:i:s'),
            $actionType,
            $userId ?? 'anonymous',
            $errorMessage
        );

        @file_put_contents($logDir . 'audit-errors.log', $line, FILE_APPEND | LOCK_EX);
    }
}
