<?php
/**
 * SpeakOn! — Recording Routes
 *
 * GET  /api/recordings           — List recordings
 * POST /api/recordings           — Upload new recording (siswa only)
 * GET  /api/recordings/{id}      — Get recording details
 * GET  /api/recordings/{id}/stream — Stream audio file
 *
 * Requirements covered:
 *   - 5.1 : Students can upload audio recordings
 *   - 5.3 : Dosen can listen to recordings
 *   - 5.4 : Only one current recording per (student, level)
 *   - 5.6 : Role-based access to recordings
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../middleware/rbac.php';
require_once __DIR__ . '/../services/RecordingService.php';
require_once __DIR__ . '/../services/AuditService.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../utils/Validator.php';

handleCors();

$method = $_SERVER['REQUEST_METHOD'];
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Strip base path prefix
$uri = preg_replace('#^.*/api/recordings#', '', $uri);

// ── GET /api/recordings/{id}/stream — Stream audio ───────────────────────────
if ($method === 'GET' && preg_match('#^/([0-9a-f\-]{36})/stream$#i', $uri, $m)) {
    requireAuth();
    requireRole(['dosen', 'superadmin', 'siswa']);

    $recordingId = $m[1];
    $recording   = RecordingService::getRecordingById($recordingId);

    if (!$recording) {
        Response::notFound('Rekaman tidak ditemukan.');
    }

    // Students can only stream their own recordings
    if ($currentUser->role === 'siswa' && $recording['student_id'] !== $currentUser->id) {
        Response::forbidden('FORBIDDEN', 'Anda hanya dapat mengakses rekaman milik Anda sendiri.');
    }

    $absPath = RecordingService::getAbsolutePath($recording['file_path']);

    if (!file_exists($absPath)) {
        Response::notFound('File rekaman tidak ditemukan di server.');
    }

    $fileSize = filesize($absPath);
    $mimeType = mime_content_type($absPath) ?: 'audio/webm';
    // Normalize video/webm → audio/webm so browser <audio> player works
    if ($mimeType === 'video/webm' || str_starts_with($mimeType, 'video/webm')) {
        $mimeType = 'audio/webm';
    }

    // Support HTTP Range requests for audio seeking
    $start = 0;
    $end   = $fileSize - 1;

    if (isset($_SERVER['HTTP_RANGE'])) {
        preg_match('/bytes=(\d+)-(\d*)/', $_SERVER['HTTP_RANGE'], $range);
        $start = (int)$range[1];
        $end   = isset($range[2]) && $range[2] !== '' ? (int)$range[2] : $fileSize - 1;

        http_response_code(206);
        header("Content-Range: bytes {$start}-{$end}/{$fileSize}");
    } else {
        http_response_code(200);
    }

    header('Content-Type: ' . $mimeType);
    header('Content-Length: ' . ($end - $start + 1));
    header('Accept-Ranges: bytes');
    header('Cache-Control: no-cache');

    $fp = fopen($absPath, 'rb');
    fseek($fp, $start);
    $remaining = $end - $start + 1;

    while ($remaining > 0 && !feof($fp)) {
        $chunk     = min(8192, $remaining);
        $data      = fread($fp, $chunk);
        $remaining -= strlen($data);
        echo $data;
        flush();
    }
    fclose($fp);
    exit;
}

// ── GET /api/recordings/{id} — Get recording details ─────────────────────────
elseif ($method === 'GET' && preg_match('#^/([0-9a-f\-]{36})$#i', $uri, $m)) {
    requireAuth();

    $recordingId = $m[1];
    $recording   = RecordingService::getRecordingById($recordingId);

    if (!$recording) {
        Response::notFound('Rekaman tidak ditemukan.');
    }

    // Students can only view their own recordings
    if ($currentUser->role === 'siswa' && $recording['student_id'] !== $currentUser->id) {
        Response::forbidden('FORBIDDEN', 'Anda hanya dapat mengakses rekaman milik Anda sendiri.');
    }

    Response::success(['recording' => $recording]);
}

// ── GET /api/recordings — List recordings ────────────────────────────────────
elseif ($method === 'GET' && ($uri === '' || $uri === '/')) {
    requireAuth();

    $studentId   = null;
    $levelId     = $_GET['level_id'] ?? null;
    $currentOnly = isset($_GET['current_only']) && $_GET['current_only'] === '1';
    $limit       = min((int)($_GET['limit']  ?? 50), 200);
    $offset      = max((int)($_GET['offset'] ?? 0), 0);

    // Students can only see their own recordings
    if ($currentUser->role === 'siswa') {
        $studentId = $currentUser->id;
    } elseif (!empty($_GET['student_id'])) {
        $studentId = $_GET['student_id'];
    }

    $recordings = RecordingService::listRecordings($studentId, $levelId, $currentOnly, $limit, $offset);
    Response::success(['recordings' => $recordings, 'count' => count($recordings)]);
}

// ── POST /api/recordings — Upload recording ───────────────────────────────────
elseif ($method === 'POST' && ($uri === '' || $uri === '/')) {
    requireAuth();
    requireSiswa();

    if (empty($_FILES['audio'])) {
        Response::validationError('File audio wajib disertakan. Gunakan field name "audio".');
    }

    $levelId = $_POST['level_id'] ?? null;
    if (empty($levelId)) {
        Response::validationError('Field "level_id" wajib diisi.');
    }

    try {
        $recording = RecordingService::uploadRecording($currentUser->id, $levelId, $_FILES['audio']);

        AuditService::writeLog(
            $currentUser->id,
            $currentUser->role,
            AuditService::ACTION_RECORDING_UPLOADED,
            AuditService::ENTITY_RECORDING,
            $recording['id'],
            ['level_id' => $levelId, 'file_size' => $recording['file_size_bytes']],
            AuditService::getClientIp()
        );

        Response::success(['recording' => $recording], 201);

    } catch (RuntimeException $e) {
        $error = json_decode($e->getMessage(), true);
        $code  = $error['code']    ?? 'UPLOAD_ERROR';
        $msg   = $error['message'] ?? 'Gagal mengupload rekaman.';

        if ($code === 'LEVEL_LOCKED') {
            Response::forbidden($code, $msg);
        }
        if ($code === 'FILE_TOO_LARGE' || $code === 'INVALID_FILE_TYPE') {
            Response::validationError($msg, $error['details'] ?? null);
        }

        Response::error(500, $code, $msg);
    }
}

// ── 404 fallback ──────────────────────────────────────────────────────────────
else {
    Response::notFound('Endpoint tidak ditemukan.');
}
