<?php
/**
 * SpeakOn! — Feedback Routes
 *
 * GET  /api/feedback       — List feedback
 * POST /api/feedback       — Create feedback (dosen only)
 * PUT  /api/feedback/{id}  — Update feedback (dosen only)
 *
 * Requirements covered:
 *   - 6.1 : Dosen can view recordings pending feedback
 *   - 6.2 : Dosen can submit feedback
 *   - 6.3 : Students can view feedback they received
 *   - 6.4 : Feedback includes comment and pass/fail status
 *   - 6.7 : Dosen can update feedback before new recording is uploaded
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../middleware/rbac.php';
require_once __DIR__ . '/../services/FeedbackService.php';
require_once __DIR__ . '/../services/AuditService.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../utils/Validator.php';

handleCors();

$method = $_SERVER['REQUEST_METHOD'];
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Strip base path prefix
$uri = preg_replace('#^.*/api/feedback#', '', $uri);

// ── GET /api/feedback — List feedback ────────────────────────────────────────
if ($method === 'GET' && ($uri === '' || $uri === '/')) {
    requireAuth();

    $dosenId   = null;
    $studentId = null;
    $limit     = min((int)($_GET['limit']  ?? 50), 200);
    $offset    = max((int)($_GET['offset'] ?? 0), 0);

    if ($currentUser->role === 'dosen') {
        $dosenId = $currentUser->id; // Dosen sees feedback they gave
    } elseif ($currentUser->role === 'siswa') {
        $studentId = $currentUser->id; // Students see feedback they received
    }
    // Superadmin sees all feedback (no filter)

    $feedbackList = FeedbackService::listFeedback($dosenId, $studentId, $limit, $offset);
    Response::success(['feedback' => $feedbackList, 'count' => count($feedbackList)]);
}

// ── POST /api/feedback — Create feedback ─────────────────────────────────────
elseif ($method === 'POST' && ($uri === '' || $uri === '/')) {
    requireAuth();
    requireDosen();

    try {
        $body = Validator::parseJsonBody();

        $recordingId = Validator::required(Validator::get($body, 'recording_id'), 'recording_id');
        $comment     = Validator::required(Validator::get($body, 'comment'), 'comment');
        $passStatus  = Validator::required(Validator::get($body, 'pass_status'), 'pass_status');

        $feedback = FeedbackService::createFeedback(
            $recordingId,
            $currentUser->id,
            $comment,
            $passStatus
        );

        AuditService::writeLog(
            $currentUser->id,
            $currentUser->role,
            AuditService::ACTION_FEEDBACK_GIVEN,
            AuditService::ENTITY_FEEDBACK,
            $feedback['id'],
            ['recording_id' => $recordingId, 'pass_status' => $passStatus],
            AuditService::getClientIp()
        );

        Response::success(['feedback' => $feedback], 201);

    } catch (InvalidArgumentException $e) {
        Response::validationError($e->getMessage());
    } catch (RuntimeException $e) {
        $error = json_decode($e->getMessage(), true);
        $code  = $error['code']    ?? 'FEEDBACK_ERROR';
        $msg   = $error['message'] ?? 'Gagal menyimpan feedback.';

        if ($code === 'COMMENT_TOO_SHORT' || $code === 'INVALID_PASS_STATUS') {
            Response::validationError($msg, $error['details'] ?? null);
        }
        if ($code === 'RECORDING_NOT_FOUND') {
            Response::notFound($msg);
        }
        if ($code === 'FEEDBACK_ALREADY_EXISTS') {
            Response::conflict($code, $msg);
        }

        Response::serverError($msg);
    }
}

// ── PUT /api/feedback/{id} — Update feedback ─────────────────────────────────
elseif ($method === 'PUT' && preg_match('#^/([0-9a-f\-]{36})$#i', $uri, $m)) {
    requireAuth();
    requireDosen();

    $feedbackId = $m[1];

    try {
        $body = Validator::parseJsonBody();

        $comment    = Validator::required(Validator::get($body, 'comment'), 'comment');
        $passStatus = Validator::required(Validator::get($body, 'pass_status'), 'pass_status');

        $feedback = FeedbackService::updateFeedback(
            $feedbackId,
            $currentUser->id,
            $comment,
            $passStatus
        );

        AuditService::writeLog(
            $currentUser->id,
            $currentUser->role,
            AuditService::ACTION_FEEDBACK_UPDATED,
            AuditService::ENTITY_FEEDBACK,
            $feedbackId,
            ['pass_status' => $passStatus],
            AuditService::getClientIp()
        );

        Response::success(['feedback' => $feedback]);

    } catch (InvalidArgumentException $e) {
        Response::validationError($e->getMessage());
    } catch (RuntimeException $e) {
        $error = json_decode($e->getMessage(), true);
        $code  = $error['code']    ?? 'FEEDBACK_ERROR';
        $msg   = $error['message'] ?? 'Gagal memperbarui feedback.';

        if ($code === 'COMMENT_TOO_SHORT' || $code === 'INVALID_PASS_STATUS') {
            Response::validationError($msg);
        }
        if ($code === 'FEEDBACK_NOT_FOUND') {
            Response::notFound($msg);
        }
        if ($code === 'FORBIDDEN') {
            Response::forbidden($code, $msg);
        }
        if ($code === 'RECORDING_REPLACED') {
            Response::error(409, $code, $msg);
        }

        Response::serverError($msg);
    }
}

// ── 404 fallback ──────────────────────────────────────────────────────────────
else {
    Response::notFound('Endpoint tidak ditemukan.');
}
