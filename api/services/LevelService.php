<?php
/**
 * SpeakOn! — LevelService
 *
 * Manages the 5-level progression system for students.
 *
 * Requirements covered:
 *   - 4.2 : Level 1 is unlocked when a student account is created
 *   - 4.3 : Levels 2–5 are locked until the previous level is passed
 *   - 4.4 : Passing a level unlocks the next level
 *   - 4.5 : Students can view their progress across all 5 levels
 *   - 8.1 : Progress tracker shows percentage completion
 *   - 8.2 : Progress tracker shows status of each level
 *   - 8.4 : Progress percentage is calculated as (passed levels / 5) × 100
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

class LevelService
{
    // Level status constants
    public const STATUS_LOCKED   = 'locked';
    public const STATUS_ACTIVE   = 'active';
    public const STATUS_PASSED   = 'passed';

    /**
     * Initialise level progress for a newly created student.
     *
     * Unlocks Level 1 (status = 'active') and locks Levels 2–5 (status = 'locked').
     *
     * @param  string $studentId  UUID of the student.
     * @return void
     */
    public static function initializeStudentLevels(string $studentId): void
    {
        $pdo    = getDB();
        $levels = $pdo->query('SELECT id, order_index FROM levels ORDER BY order_index ASC')->fetchAll();

        foreach ($levels as $level) {
            $status = ($level['order_index'] === 1) ? self::STATUS_ACTIVE : self::STATUS_LOCKED;

            $stmt = $pdo->prepare(
                'INSERT INTO student_level_progress
                    (id, student_id, level_id, status, unlocked_at, passed_at)
                 VALUES
                    (:id, :student_id, :level_id, :status, :unlocked_at, NULL)
                 ON DUPLICATE KEY UPDATE status = VALUES(status)'
            );
            $stmt->execute([
                ':id'          => generateUUID(),
                ':student_id'  => $studentId,
                ':level_id'    => $level['id'],
                ':status'      => $status,
                ':unlocked_at' => ($status === self::STATUS_ACTIVE) ? date('Y-m-d H:i:s') : null,
            ]);
        }
    }

    /**
     * Unlock the next level for a student after they pass the current level.
     *
     * Sets the current level's status to 'passed' and unlocks the next level.
     * If the student has already passed all 5 levels, this is a no-op.
     *
     * @param  string $studentId      UUID of the student.
     * @param  string $currentLevelId UUID of the level that was just passed.
     * @return string|null            UUID of the newly unlocked level, or null if none.
     */
    public static function unlockNextLevel(string $studentId, string $currentLevelId): ?string
    {
        $pdo = getDB();

        // Mark current level as passed
        $passStmt = $pdo->prepare(
            'UPDATE student_level_progress
                SET status = :status, passed_at = NOW()
              WHERE student_id = :student_id
                AND level_id   = :level_id'
        );
        $passStmt->execute([
            ':status'     => self::STATUS_PASSED,
            ':student_id' => $studentId,
            ':level_id'   => $currentLevelId,
        ]);

        // Find the next level (order_index + 1)
        $nextStmt = $pdo->prepare(
            'SELECT l.id AS next_level_id
               FROM levels l
               JOIN levels curr ON curr.id = :current_level_id
              WHERE l.order_index = curr.order_index + 1
              LIMIT 1'
        );
        $nextStmt->execute([':current_level_id' => $currentLevelId]);
        $nextLevel = $nextStmt->fetch();

        if (!$nextLevel) {
            return null; // No next level — student has completed all levels
        }

        // Unlock the next level
        $unlockStmt = $pdo->prepare(
            'UPDATE student_level_progress
                SET status = :status, unlocked_at = NOW()
              WHERE student_id = :student_id
                AND level_id   = :level_id'
        );
        $unlockStmt->execute([
            ':status'     => self::STATUS_ACTIVE,
            ':student_id' => $studentId,
            ':level_id'   => $nextLevel['next_level_id'],
        ]);

        return $nextLevel['next_level_id'];
    }

    /**
     * Get the progress status of all 5 levels for a student.
     *
     * @param  string $studentId  UUID of the student.
     * @return array              Array of level progress objects, ordered by level order_index.
     */
    public static function getStudentProgress(string $studentId): array
    {
        $pdo  = getDB();
        $stmt = $pdo->prepare(
            'SELECT
                l.id,
                l.name,
                l.description,
                l.order_index,
                COALESCE(slp.status, :locked) AS status,
                slp.unlocked_at,
                slp.passed_at
               FROM levels l
               LEFT JOIN student_level_progress slp
                      ON slp.level_id = l.id
                     AND slp.student_id = :student_id
              ORDER BY l.order_index ASC'
        );
        $stmt->execute([
            ':student_id' => $studentId,
            ':locked'     => self::STATUS_LOCKED,
        ]);

        return $stmt->fetchAll();
    }

    /**
     * Calculate the progress percentage for a student.
     *
     * Percentage = (number of levels with status 'passed') / 5 × 100
     *
     * @param  string $studentId  UUID of the student.
     * @return float              Progress percentage (0–100).
     */
    public static function getProgressPercentage(string $studentId): float
    {
        $pdo  = getDB();
        $stmt = $pdo->prepare(
            'SELECT COUNT(*) AS passed_count
               FROM student_level_progress
              WHERE student_id = :student_id
                AND status     = :status'
        );
        $stmt->execute([
            ':student_id' => $studentId,
            ':status'     => self::STATUS_PASSED,
        ]);
        $row = $stmt->fetch();

        return round(((int)$row['passed_count'] / 5) * 100, 1);
    }

    /**
     * Get detailed progress information for the progress tracker UI.
     *
     * Returns each level with its status, visual icon hint, and metadata.
     *
     * @param  string $studentId  UUID of the student.
     * @return array{
     *   percentage: float,
     *   levels: array
     * }
     */
    public static function getProgressDetails(string $studentId): array
    {
        $levels     = self::getStudentProgress($studentId);
        $percentage = self::getProgressPercentage($studentId);

        $levelDetails = array_map(function (array $level): array {
            return [
                'id'          => $level['id'],
                'name'        => $level['name'],
                'description' => $level['description'],
                'orderIndex'  => (int)$level['order_index'],
                'status'      => $level['status'],
                'icon'        => match ($level['status']) {
                    self::STATUS_PASSED => 'check',
                    self::STATUS_ACTIVE => 'active',
                    default             => 'lock',
                },
                'unlockedAt'  => $level['unlocked_at'],
                'passedAt'    => $level['passed_at'],
            ];
        }, $levels);

        return [
            'percentage' => $percentage,
            'levels'     => $levelDetails,
        ];
    }

    /**
     * Check whether a student has access to a specific level.
     *
     * @param  string $studentId  UUID of the student.
     * @param  string $levelId    UUID of the level.
     * @return bool               true if the level is 'active' or 'passed'.
     */
    public static function hasAccessToLevel(string $studentId, string $levelId): bool
    {
        $pdo  = getDB();
        $stmt = $pdo->prepare(
            'SELECT status
               FROM student_level_progress
              WHERE student_id = :student_id
                AND level_id   = :level_id
              LIMIT 1'
        );
        $stmt->execute([
            ':student_id' => $studentId,
            ':level_id'   => $levelId,
        ]);
        $row = $stmt->fetch();

        if (!$row) {
            return false;
        }

        return in_array($row['status'], [self::STATUS_ACTIVE, self::STATUS_PASSED], true);
    }
}
