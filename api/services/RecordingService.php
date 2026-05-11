<?php
/**
 * SpeakOn! — RecordingService
 *
 * Handles audio recording uploads, storage, and retrieval.
 *
 * Requirements covered:
 *   - 5.1 : Students can upload audio recordings per level
 *   - 5.2 : File size must not exceed 50 MB
 *   - 5.4 : Only one current recording per (student, level) combination
 *   - 5.5 : Previous recordings are preserved but marked as not current
 *   - 5.6 : Dosen can list all recordings; students can only see their own
 *   - 5.7 : Allowed audio MIME types enforced
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../services/LevelService.php';

class RecordingService
{
    /**
     * Upload a new audio recording for a student at a specific level.
     *
     * Validates file size and MIME type, saves the file to the uploads folder,
     * marks any previous recording for this (student, level) as not current,
     * and inserts a new recording record.
     *
     * @param  string $studentId  UUID of the student uploading.
     * @param  string $levelId    UUID of the level this recording is for.
     * @param  array  $file       The $_FILES entry for the uploaded file.
     * @return array              The newly created recording record.
     * @throws RuntimeException   On validation failure or storage error.
     */
    public static function uploadRecording(string $studentId, string $levelId, array $file, ?int $taskIndex = null): array
    {
        // Validate file was uploaded without errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException(json_encode([
                'code'    => 'UPLOAD_ERROR',
                'message' => self::uploadErrorMessage($file['error']),
                'details' => null,
            ]));
        }

        // Validate file size (≤ 50 MB)
        if ($file['size'] > UPLOAD_MAX_BYTES) {
            $maxMb = UPLOAD_MAX_BYTES / (1024 * 1024);
            throw new RuntimeException(json_encode([
                'code'    => 'FILE_TOO_LARGE',
                'message' => "Ukuran file melebihi batas maksimum {$maxMb} MB.",
                'details' => ['max_bytes' => UPLOAD_MAX_BYTES, 'actual_bytes' => $file['size']],
            ]));
        }

        // Validate MIME type — normalize video/webm to audio/webm (Chrome records as video/webm)
        $mimeType = mime_content_type($file['tmp_name']);
        // Strip codec suffix for comparison (e.g. "audio/ogg; codecs=opus" → "audio/ogg")
        $mimeBase = strtolower(trim(explode(';', $mimeType)[0]));
        // Treat video/webm as audio/webm (Chrome/Edge MediaRecorder behavior)
        if ($mimeBase === 'video/webm') {
            $mimeBase = 'audio/webm';
        }
        $allowedBase = ['audio/webm', 'audio/mpeg', 'audio/mp3', 'audio/ogg', 'audio/wav', 'audio/x-wav'];
        if (!in_array($mimeBase, $allowedBase, true)) {
            throw new RuntimeException(json_encode([
                'code'    => 'INVALID_FILE_TYPE',
                'message' => 'Tipe file tidak didukung. Gunakan format audio: webm, mp3, ogg, atau wav.',
                'details' => ['detected_type' => $mimeType],
            ]));
        }
        $mimeType = $mimeBase; // use normalized type

        // Verify student has access to this level
        if (!LevelService::hasAccessToLevel($studentId, $levelId)) {
            throw new RuntimeException(json_encode([
                'code'    => 'LEVEL_LOCKED',
                'message' => 'Level ini belum terbuka. Selesaikan level sebelumnya terlebih dahulu.',
                'details' => null,
            ]));
        }

        // Determine file extension from MIME type
        $ext      = self::mimeToExtension($mimeType);
        $timestamp = time();
        $filename  = "{$studentId}_{$levelId}_{$timestamp}.{$ext}";
        $uploadDir = UPLOAD_DIR;

        // Ensure upload directory exists
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                throw new RuntimeException(json_encode([
                    'code'    => 'STORAGE_ERROR',
                    'message' => 'Gagal membuat direktori penyimpanan.',
                    'details' => null,
                ]));
            }
        }

        $filePath = $uploadDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            throw new RuntimeException(json_encode([
                'code'    => 'STORAGE_ERROR',
                'message' => 'Gagal menyimpan file rekaman.',
                'details' => null,
            ]));
        }

        $pdo = getDB();

        // Mark previous recordings for this (student, level, task_index) as not current
        if ($taskIndex === null) {
            // Step 3 main recording — mark old main recordings as not current
            $markOldStmt = $pdo->prepare(
                'UPDATE recordings SET is_current = 0
                  WHERE student_id = :student_id
                    AND level_id   = :level_id
                    AND task_index IS NULL
                    AND is_current = 1'
            );
        } else {
            // Task recording — mark old task recordings for same task as not current
            $markOldStmt = $pdo->prepare(
                'UPDATE recordings SET is_current = 0
                  WHERE student_id = :student_id
                    AND level_id   = :level_id
                    AND task_index = :task_index
                    AND is_current = 1'
            );
        }
        $params = [':student_id' => $studentId, ':level_id' => $levelId];
        if ($taskIndex !== null) $params[':task_index'] = $taskIndex;
        $markOldStmt->execute($params);

        // Insert new recording record
        $recordingId = generateUUID();
        $relPath     = 'uploads/recordings/' . $filename;

        $insertStmt = $pdo->prepare(
            'INSERT INTO recordings
                (id, student_id, level_id, file_path, file_size_bytes, duration_seconds, uploaded_at, is_current, task_index)
             VALUES
                (:id, :student_id, :level_id, :file_path, :file_size, NULL, NOW(), 1, :task_index)'
        );
        $insertStmt->execute([
            ':id'         => $recordingId,
            ':student_id' => $studentId,
            ':level_id'   => $levelId,
            ':file_path'  => $relPath,
            ':file_size'  => $file['size'],
            ':task_index' => $taskIndex,
        ]);

        return self::getRecordingById($recordingId);
    }

    /**
     * Get a single recording by its UUID.
     *
     * @param  string $recordingId  UUID of the recording.
     * @return array|null           Recording data, or null if not found.
     */
    public static function getRecordingById(string $recordingId): ?array
    {
        $pdo  = getDB();

        // Check if task_index column exists
        $hasTaskIndex = false;
        try {
            $check = $pdo->query("SHOW COLUMNS FROM recordings LIKE 'task_index'");
            $hasTaskIndex = $check->rowCount() > 0;
        } catch (\Exception $e) {}

        $taskIndexSelect = $hasTaskIndex ? 'r.task_index' : 'NULL AS task_index';

        $stmt = $pdo->prepare(
            "SELECT r.id, r.student_id, r.level_id, r.file_path, r.file_size_bytes,
                    r.duration_seconds, r.uploaded_at, r.is_current,
                    {$taskIndexSelect},
                    u.full_name AS student_name,
                    l.name AS level_name, l.order_index AS level_order
               FROM recordings r
               JOIN users u ON u.id = r.student_id
               JOIN levels l ON l.id = r.level_id
              WHERE r.id = :id
              LIMIT 1"
        );
        $stmt->execute([':id' => $recordingId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * List recordings with optional filters.
     *
     * Dosen and Super Admin can see all recordings.
     * Students can only see their own recordings.
     *
     * @param  string|null $studentId  Filter by student UUID (null = all).
     * @param  string|null $levelId    Filter by level UUID (null = all).
     * @param  bool        $currentOnly  If true, only return is_current = 1 recordings.
     * @param  int         $limit      Max results.
     * @param  int         $offset     Pagination offset.
     * @return array                   Array of recording objects.
     */
    public static function listRecordings(
        ?string $studentId  = null,
        ?string $levelId    = null,
        bool    $currentOnly = false,
        int     $limit      = 50,
        int     $offset     = 0
    ): array {
        $pdo    = getDB();
        $where  = ['1=1'];
        $params = [];

        if ($studentId !== null) {
            $where[]              = 'r.student_id = :student_id';
            $params[':student_id'] = $studentId;
        }

        if ($levelId !== null) {
            $where[]            = 'r.level_id = :level_id';
            $params[':level_id'] = $levelId;
        }

        if ($currentOnly) {
            $where[] = 'r.is_current = 1';
        }

        // Check if task_index column exists (migration may not have run yet)
        $hasTaskIndex = false;
        try {
            $check = $pdo->query("SHOW COLUMNS FROM recordings LIKE 'task_index'");
            $hasTaskIndex = $check->rowCount() > 0;
        } catch (\Exception $e) {}

        $taskIndexSelect = $hasTaskIndex ? 'r.task_index' : 'NULL AS task_index';

        $sql = "SELECT r.id, r.student_id, r.level_id, r.file_path, r.file_size_bytes,
                       r.duration_seconds, r.uploaded_at, r.is_current,
                       {$taskIndexSelect},
                       u.full_name AS student_name,
                       l.name AS level_name, l.order_index AS level_order
                  FROM recordings r
                  JOIN users u ON u.id = r.student_id
                  JOIN levels l ON l.id = r.level_id
                 WHERE " . implode(' AND ', $where) . "
                 ORDER BY r.uploaded_at DESC
                 LIMIT :limit OFFSET :offset";

        $stmt = $pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Get the absolute filesystem path for a recording file.
     *
     * @param  string $relPath  The relative path stored in the database.
     * @return string           Absolute path.
     */
    public static function getAbsolutePath(string $relPath): string
    {
        // relPath is like "uploads/recordings/filename.webm"
        return __DIR__ . '/../../' . $relPath;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Internal helpers
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Map a MIME type to a file extension.
     *
     * @param  string $mimeType
     * @return string
     */
    private static function mimeToExtension(string $mimeType): string
    {
        return match ($mimeType) {
            'audio/webm'  => 'webm',
            'audio/mpeg',
            'audio/mp3'   => 'mp3',
            'audio/ogg'   => 'ogg',
            'audio/wav'   => 'wav',
            default       => 'bin',
        };
    }

    /**
     * Return a human-readable message for a PHP upload error code.
     *
     * @param  int $errorCode  The $_FILES['error'] value.
     * @return string
     */
    private static function uploadErrorMessage(int $errorCode): string
    {
        return match ($errorCode) {
            UPLOAD_ERR_INI_SIZE,
            UPLOAD_ERR_FORM_SIZE => 'Ukuran file melebihi batas yang diizinkan.',
            UPLOAD_ERR_PARTIAL   => 'File hanya terupload sebagian. Coba lagi.',
            UPLOAD_ERR_NO_FILE   => 'Tidak ada file yang diupload.',
            UPLOAD_ERR_NO_TMP_DIR => 'Folder sementara tidak tersedia.',
            UPLOAD_ERR_CANT_WRITE => 'Gagal menulis file ke disk.',
            default              => 'Terjadi kesalahan saat upload file.',
        };
    }
}
