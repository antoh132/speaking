<?php
/**
 * SpeakOn! — Notification Routes
 *
 * GET /api/notifications                — List notifications (siswa only)
 * GET /api/notifications/unread-count   — Unread badge count (siswa only)
 * PUT /api/notifications/{id}/read      — Mark as read (siswa only)
 *
 * Requirements covered:
 *   - 7.2 : Students can view their notifications
 *   - 7.4 : Students can mark notifications as read
 *   - 7.5 : Unread count for badge display
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../middleware/rbac.php';
require_once __DIR__ . '/../services/NotificationService.php';
require_once __DIR__ . '/../utils/Response.php';

handleCors();

$method = $_SERVER['REQUEST_METHOD'];
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Strip base path prefix
$uri = preg_replace('#^.*/api/notifications#', '', $uri);

// ── GET /api/notifications/unread-count ───────────────────────────────────────
if ($method === 'GET' && $uri === '/unread-count') {
    requireAuth();
    requireSiswa();

    $count = NotificationService::getUnreadCount($currentUser->id);
    Response::success(['unreadCount' => $count]);
}

// ── GET /api/notifications — List notifications ───────────────────────────────
elseif ($method === 'GET' && ($uri === '' || $uri === '/')) {
    requireAuth();
    requireSiswa();

    $limit  = min((int)($_GET['limit']  ?? 50), 200);
    $offset = max((int)($_GET['offset'] ?? 0), 0);

    $notifications = NotificationService::getAllNotifications($currentUser->id, $limit, $offset);
    $unreadCount   = NotificationService::getUnreadCount($currentUser->id);

    Response::success([
        'notifications' => $notifications,
        'count'         => count($notifications),
        'unreadCount'   => $unreadCount,
    ]);
}

// ── PUT /api/notifications/{id}/read — Mark as read ──────────────────────────
elseif ($method === 'PUT' && preg_match('#^/([0-9a-f\-]{36})/read$#i', $uri, $m)) {
    requireAuth();
    requireSiswa();

    $notificationId = $m[1];
    $success        = NotificationService::markAsRead($notificationId, $currentUser->id);

    if (!$success) {
        Response::notFound('Notifikasi tidak ditemukan atau sudah dibaca.');
    }

    Response::success(['message' => 'Notifikasi ditandai sebagai sudah dibaca.']);
}

// ── 404 fallback ──────────────────────────────────────────────────────────────
else {
    Response::notFound('Endpoint tidak ditemukan.');
}
