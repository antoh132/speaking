<?php
/**
 * SpeakOn! — NotificationService
 *
 * Manages in-app notifications for students when they receive feedback.
 *
 * Requirements covered:
 *   - 7.1 : Notification created automatically when feedback is saved
 *   - 7.2 : Students can view their notifications
 *   - 7.3 : Notifications include feedback details
 *   - 7.4 : Students can mark notifications as read
 *   - 7.5 : Unread notification count for badge display
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../services/AuditService.php';

class NotificationService
{
    /**
     * Create a notification for a student when feedback is saved.
     *
     * Called automatically by FeedbackService after feedback is created.
     *
     * @param  string $studentId  UUID of the student to notify.
     * @param  string $feedbackId UUID of the feedback that triggered the notification.
     * @return string             UUID of the created notification.
     */
    public static function createNotification(string $studentId, string $feedbackId): string
    {
        $pdo            = getDB();
        $notificationId = generateUUID();

        $stmt = $pdo->prepare(
            'INSERT INTO notifications (id, student_id, feedback_id, is_read, created_at, read_at)
             VALUES (:id, :student_id, :feedback_id, 0, NOW(), NULL)'
        );
        $stmt->execute([
            ':id'          => $notificationId,
            ':student_id'  => $studentId,
            ':feedback_id' => $feedbackId,
        ]);

        // Log notification creation (non-blocking)
        AuditService::writeLog(
            null,
            null,
            AuditService::ACTION_NOTIFICATION_SENT,
            AuditService::ENTITY_NOTIFICATION,
            $notificationId,
            ['student_id' => $studentId, 'feedback_id' => $feedbackId],
            null
        );

        return $notificationId;
    }

    /**
     * Mark a notification as read.
     *
     * @param  string $notificationId  UUID of the notification.
     * @param  string $studentId       UUID of the student (ownership check).
     * @return bool                    true if marked as read, false if not found or already read.
     */
    public static function markAsRead(string $notificationId, string $studentId): bool
    {
        $pdo  = getDB();
        $stmt = $pdo->prepare(
            'UPDATE notifications
                SET is_read = 1, read_at = NOW()
              WHERE id         = :id
                AND student_id = :student_id
                AND is_read    = 0'
        );
        $stmt->execute([
            ':id'         => $notificationId,
            ':student_id' => $studentId,
        ]);

        if ($stmt->rowCount() > 0) {
            AuditService::writeLog(
                $studentId,
                'siswa',
                AuditService::ACTION_NOTIFICATION_READ,
                AuditService::ENTITY_NOTIFICATION,
                $notificationId,
                null,
                null
            );
            return true;
        }

        return false;
    }

    /**
     * Get the count of unread notifications for a student.
     *
     * @param  string $studentId  UUID of the student.
     * @return int                Number of unread notifications.
     */
    public static function getUnreadCount(string $studentId): int
    {
        $pdo  = getDB();
        $stmt = $pdo->prepare(
            'SELECT COUNT(*) AS cnt
               FROM notifications
              WHERE student_id = :student_id
                AND is_read    = 0'
        );
        $stmt->execute([':student_id' => $studentId]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Get all notifications for a student, enriched with feedback details.
     *
     * @param  string $studentId  UUID of the student.
     * @param  int    $limit      Max results (default 50).
     * @param  int    $offset     Pagination offset.
     * @return array              Array of notification objects.
     */
    public static function getAllNotifications(string $studentId, int $limit = 50, int $offset = 0): array
    {
        $pdo  = getDB();
        $stmt = $pdo->prepare(
            'SELECT
                n.id,
                n.student_id,
                n.feedback_id,
                n.is_read,
                n.created_at,
                n.read_at,
                f.comment        AS feedback_comment,
                f.pass_status    AS feedback_pass_status,
                d.full_name      AS dosen_name,
                l.name           AS level_name,
                l.order_index    AS level_order
               FROM notifications n
               JOIN feedback f   ON f.id = n.feedback_id
               JOIN users d      ON d.id = f.dosen_id
               JOIN recordings r ON r.id = f.recording_id
               JOIN levels l     ON l.id = r.level_id
              WHERE n.student_id = :student_id
              ORDER BY n.created_at DESC
              LIMIT :limit OFFSET :offset'
        );
        $stmt->bindValue(':student_id', $studentId);
        $stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
