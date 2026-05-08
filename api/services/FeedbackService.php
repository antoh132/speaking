<?php
/**
 * SpeakOn! — FeedbackService
 *
 * Handles creation and update of feedback from Dosen on student recordings.
 * Automatically triggers level unlock and notification creation when a student passes.
 *
 * Requirements covered:
 *   - 6.4 : Dosen can submit feedback with comment and pass/fail status
 *   - 6.5 : Comment must be at least 10 characters
 *   - 6.6 : Passing feedback automatically unlocks the next level
 *   - 6.7 : Dosen can update feedback before student uploads a new recording
 *   - 6.8 : pass_status must be 'lulus' or 'tidak_lulus'
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../services/LevelService.php';
require_once __DIR__ . '/../services/NotificationService.php';
require_once __DIR__ . '/../services/AuditService.php';

class FeedbackService
{
    public const STATUS_LULUS       = 'lulus';
    public const STATUS_TIDAK_LULUS = 'tidak_lulus';

    /**
     * Create feedback for a recording.
     *
     * If passStatus = 'lulus', automatically unlocks the next level and
     * creates a notification for the student.
     *
     * @param  string $recordingId  UUID of the recording being assessed.
     * @param  string $dosenId      UUID of the Dosen giving feedback.
     * @param  string $comment      Feedback comment (min 10 characters).
     * @param  string $passStatus   'lulus' or 'tidak_lulus'.
     * @return array                The created feedback record.
     * @throws RuntimeException     On validation failure or if recording not found.
     */
    public static function createFeedback(
        string $recordingId,
        string $dosenId,
        string $comment,
        string $passStatus
    ): array {
        // Validate comment length
        if (mb_strlen(trim($comment)) < 10) {
            throw new RuntimeException(json_encode([
                'code'    => 'COMMENT_TOO_SHORT',
                'message' => 'Komentar feedback minimal 10 karakter.',
                'details' => ['min_length' => 10, 'actual_length' => mb_strlen(trim($comment))],
            ]));
        }

        // Validate pass status
        if (!in_array($passStatus, [self::STATUS_LULUS, self::STATUS_TIDAK_LULUS], true)) {
            throw new RuntimeException(json_encode([
                'code'    => 'INVALID_PASS_STATUS',
                'message' => "Status penilaian tidak valid. Gunakan 'lulus' atau 'tidak_lulus'.",
                'details' => null,
            ]));
        }

        $pdo = getDB();

        // Verify recording exists and get student/level info
        $recStmt = $pdo->prepare(
            'SELECT r.id, r.student_id, r.level_id, r.is_current
               FROM recordings r
              WHERE r.id = :id
              LIMIT 1'
        );
        $recStmt->execute([':id' => $recordingId]);
        $recording = $recStmt->fetch();

        if (!$recording) {
            throw new RuntimeException(json_encode([
                'code'    => 'RECORDING_NOT_FOUND',
                'message' => 'Rekaman tidak ditemukan.',
                'details' => null,
            ]));
        }

        // Check if feedback already exists for this recording
        $existingStmt = $pdo->prepare(
            'SELECT id FROM feedback WHERE recording_id = :recording_id LIMIT 1'
        );
        $existingStmt->execute([':recording_id' => $recordingId]);
        if ($existingStmt->fetch()) {
            throw new RuntimeException(json_encode([
                'code'    => 'FEEDBACK_ALREADY_EXISTS',
                'message' => 'Feedback untuk rekaman ini sudah ada. Gunakan endpoint update untuk mengubahnya.',
                'details' => null,
            ]));
        }

        $feedbackId = generateUUID();

        $insertStmt = $pdo->prepare(
            'INSERT INTO feedback (id, recording_id, dosen_id, comment, pass_status, created_at, updated_at)
             VALUES (:id, :recording_id, :dosen_id, :comment, :pass_status, NOW(), NOW())'
        );
        $insertStmt->execute([
            ':id'           => $feedbackId,
            ':recording_id' => $recordingId,
            ':dosen_id'     => $dosenId,
            ':comment'      => trim($comment),
            ':pass_status'  => $passStatus,
        ]);

        // If student passed, unlock the next level
        if ($passStatus === self::STATUS_LULUS) {
            $nextLevelId = LevelService::unlockNextLevel($recording['student_id'], $recording['level_id']);

            if ($nextLevelId) {
                AuditService::writeLog(
                    $dosenId,
                    'dosen',
                    AuditService::ACTION_LEVEL_UNLOCKED,
                    AuditService::ENTITY_LEVEL,
                    $nextLevelId,
                    ['student_id' => $recording['student_id'], 'triggered_by_feedback' => $feedbackId],
                    null
                );
            }
        }

        // Create notification for the student
        NotificationService::createNotification($recording['student_id'], $feedbackId);

        return self::getFeedbackById($feedbackId);
    }

    /**
     * Update existing feedback.
     *
     * Feedback can only be updated if the student has NOT uploaded a new recording
     * after the feedback was given (i.e., the original recording is still current).
     *
     * @param  string $feedbackId  UUID of the feedback to update.
     * @param  string $dosenId     UUID of the Dosen (must be the original author).
     * @param  string $comment     Updated comment (min 10 characters).
     * @param  string $passStatus  Updated pass status.
     * @return array               The updated feedback record.
     * @throws RuntimeException    On validation failure or permission error.
     */
    public static function updateFeedback(
        string $feedbackId,
        string $dosenId,
        string $comment,
        string $passStatus
    ): array {
        // Validate inputs
        if (mb_strlen(trim($comment)) < 10) {
            throw new RuntimeException(json_encode([
                'code'    => 'COMMENT_TOO_SHORT',
                'message' => 'Komentar feedback minimal 10 karakter.',
                'details' => null,
            ]));
        }

        if (!in_array($passStatus, [self::STATUS_LULUS, self::STATUS_TIDAK_LULUS], true)) {
            throw new RuntimeException(json_encode([
                'code'    => 'INVALID_PASS_STATUS',
                'message' => "Status penilaian tidak valid. Gunakan 'lulus' atau 'tidak_lulus'.",
                'details' => null,
            ]));
        }

        $pdo = getDB();

        // Get existing feedback with recording info
        $stmt = $pdo->prepare(
            'SELECT f.id, f.dosen_id, f.recording_id, f.pass_status,
                    r.student_id, r.level_id, r.is_current
               FROM feedback f
               JOIN recordings r ON r.id = f.recording_id
              WHERE f.id = :id
              LIMIT 1'
        );
        $stmt->execute([':id' => $feedbackId]);
        $feedback = $stmt->fetch();

        if (!$feedback) {
            throw new RuntimeException(json_encode([
                'code'    => 'FEEDBACK_NOT_FOUND',
                'message' => 'Feedback tidak ditemukan.',
                'details' => null,
            ]));
        }

        // Only the original Dosen can update their feedback
        if ($feedback['dosen_id'] !== $dosenId) {
            throw new RuntimeException(json_encode([
                'code'    => 'FORBIDDEN',
                'message' => 'Anda hanya dapat mengubah feedback yang Anda buat.',
                'details' => null,
            ]));
        }

        // Cannot update if student has uploaded a new recording (is_current = 0 means replaced)
        if (!(bool)$feedback['is_current']) {
            throw new RuntimeException(json_encode([
                'code'    => 'RECORDING_REPLACED',
                'message' => 'Feedback tidak dapat diubah karena siswa telah mengupload rekaman baru.',
                'details' => null,
            ]));
        }

        $oldPassStatus = $feedback['pass_status'];

        $updateStmt = $pdo->prepare(
            'UPDATE feedback SET comment = :comment, pass_status = :pass_status, updated_at = NOW()
              WHERE id = :id'
        );
        $updateStmt->execute([
            ':comment'     => trim($comment),
            ':pass_status' => $passStatus,
            ':id'          => $feedbackId,
        ]);

        // Handle level unlock if pass status changed to 'lulus'
        if ($passStatus === self::STATUS_LULUS && $oldPassStatus !== self::STATUS_LULUS) {
            LevelService::unlockNextLevel($feedback['student_id'], $feedback['level_id']);
        }

        return self::getFeedbackById($feedbackId);
    }

    /**
     * Get a single feedback record by UUID.
     *
     * @param  string $feedbackId  UUID of the feedback.
     * @return array|null          Feedback data, or null if not found.
     */
    public static function getFeedbackById(string $feedbackId): ?array
    {
        $pdo  = getDB();
        $stmt = $pdo->prepare(
            'SELECT f.id, f.recording_id, f.dosen_id, f.comment, f.pass_status,
                    f.created_at, f.updated_at,
                    d.full_name AS dosen_name,
                    r.student_id, r.level_id,
                    u.full_name AS student_name,
                    l.name AS level_name
               FROM feedback f
               JOIN users d ON d.id = f.dosen_id
               JOIN recordings r ON r.id = f.recording_id
               JOIN users u ON u.id = r.student_id
               JOIN levels l ON l.id = r.level_id
              WHERE f.id = :id
              LIMIT 1'
        );
        $stmt->execute([':id' => $feedbackId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * List feedback records with optional filters.
     *
     * @param  string|null $dosenId    Filter by Dosen UUID.
     * @param  string|null $studentId  Filter by student UUID (via recording).
     * @param  int         $limit      Max results.
     * @param  int         $offset     Pagination offset.
     * @return array                   Array of feedback objects.
     */
    public static function listFeedback(
        ?string $dosenId   = null,
        ?string $studentId = null,
        int     $limit     = 50,
        int     $offset    = 0
    ): array {
        $pdo    = getDB();
        $where  = ['1=1'];
        $params = [];

        if ($dosenId !== null) {
            $where[]           = 'f.dosen_id = :dosen_id';
            $params[':dosen_id'] = $dosenId;
        }

        if ($studentId !== null) {
            $where[]              = 'r.student_id = :student_id';
            $params[':student_id'] = $studentId;
        }

        $sql = 'SELECT f.id, f.recording_id, f.dosen_id, f.comment, f.pass_status,
                       f.created_at, f.updated_at,
                       d.full_name AS dosen_name,
                       r.student_id, r.level_id,
                       u.full_name AS student_name,
                       l.name AS level_name
                  FROM feedback f
                  JOIN users d ON d.id = f.dosen_id
                  JOIN recordings r ON r.id = f.recording_id
                  JOIN users u ON u.id = r.student_id
                  JOIN levels l ON l.id = r.level_id
                 WHERE ' . implode(' AND ', $where) . '
                 ORDER BY f.created_at DESC
                 LIMIT :limit OFFSET :offset';

        $stmt = $pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
