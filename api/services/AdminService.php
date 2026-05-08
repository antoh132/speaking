<?php
/**
 * SpeakOn! — AdminService
 *
 * Provides statistics and monitoring data for the Super Admin dashboard.
 *
 * Requirements covered:
 *   - 2.1 : Super Admin can view real-time user login status
 *   - 2.2 : Super Admin can view system statistics
 *   - 2.3 : Super Admin can view active sessions
 *   - 2.4 : Super Admin can view pending tasks (recordings without feedback)
 *   - 2.5 : Super Admin can view recent activity
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

class AdminService
{
    /**
     * Count currently active sessions (non-expired, non-revoked refresh tokens).
     *
     * @return int  Number of active sessions.
     */
    public static function getActiveSessionsCount(): int
    {
        $pdo  = getDB();
        $stmt = $pdo->query(
            'SELECT COUNT(*) FROM refresh_tokens
              WHERE revoked_at IS NULL
                AND expires_at > NOW()'
        );
        return (int)$stmt->fetchColumn();
    }

    /**
     * Get total user counts broken down by role.
     *
     * @return array{total: int, superadmin: int, dosen: int, siswa: int, active: int, inactive: int}
     */
    public static function getTotalUsersCount(): array
    {
        $pdo  = getDB();
        $stmt = $pdo->query(
            'SELECT
                COUNT(*)                                    AS total,
                SUM(role = "superadmin")                    AS superadmin,
                SUM(role = "dosen")                         AS dosen,
                SUM(role = "siswa")                         AS siswa,
                SUM(is_active = 1)                          AS active,
                SUM(is_active = 0)                          AS inactive
               FROM users'
        );
        $row = $stmt->fetch();

        return [
            'total'      => (int)$row['total'],
            'superadmin' => (int)$row['superadmin'],
            'dosen'      => (int)$row['dosen'],
            'siswa'      => (int)$row['siswa'],
            'active'     => (int)$row['active'],
            'inactive'   => (int)$row['inactive'],
        ];
    }

    /**
     * Count recordings that have not yet received feedback (pending tasks for Dosen).
     *
     * @return int  Number of pending recordings.
     */
    public static function getPendingTasksCount(): int
    {
        $pdo  = getDB();
        $stmt = $pdo->query(
            'SELECT COUNT(*)
               FROM recordings r
               LEFT JOIN feedback f ON f.recording_id = r.id
              WHERE r.is_current = 1
                AND f.id IS NULL'
        );
        return (int)$stmt->fetchColumn();
    }

    /**
     * Get the most recent audit log entries.
     *
     * @param  int $limit  Number of entries to return (default 20).
     * @return array       Array of recent activity entries.
     */
    public static function getRecentActivity(int $limit = 20): array
    {
        $pdo  = getDB();
        $stmt = $pdo->prepare(
            'SELECT al.id, al.user_id, al.user_role, al.action_type,
                    al.entity_type, al.entity_id, al.ip_address, al.created_at,
                    u.full_name AS user_name
               FROM audit_logs al
               LEFT JOIN users u ON u.id = al.user_id
              ORDER BY al.created_at DESC
              LIMIT :limit'
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Get a list of currently active sessions with user details.
     *
     * @param  int $limit   Max results.
     * @param  int $offset  Pagination offset.
     * @return array        Array of active session objects.
     */
    public static function getActiveSessions(int $limit = 50, int $offset = 0): array
    {
        $pdo  = getDB();
        $stmt = $pdo->prepare(
            'SELECT rt.id AS session_id, rt.user_id, rt.created_at AS session_started,
                    rt.expires_at,
                    u.full_name, u.email, u.role
               FROM refresh_tokens rt
               JOIN users u ON u.id = rt.user_id
              WHERE rt.revoked_at IS NULL
                AND rt.expires_at > NOW()
              ORDER BY rt.created_at DESC
              LIMIT :limit OFFSET :offset'
        );
        $stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Get a comprehensive dashboard statistics summary.
     *
     * @return array  Dashboard stats object.
     */
    public static function getDashboardStats(): array
    {
        return [
            'users'          => self::getTotalUsersCount(),
            'activeSessions' => self::getActiveSessionsCount(),
            'pendingTasks'   => self::getPendingTasksCount(),
        ];
    }
}
