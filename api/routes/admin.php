<?php
/**
 * SpeakOn! — Admin Dashboard Routes
 *
 * GET /api/admin/stats            — Dashboard statistics (superadmin only)
 * GET /api/admin/active-sessions  — Active session list (superadmin only)
 * GET /api/admin/recent-activity  — Recent audit log entries (superadmin only)
 *
 * Requirements covered:
 *   - 2.1 : Real-time user login status monitoring
 *   - 2.2 : System statistics overview
 *   - 2.3 : Active sessions list
 *   - 2.4 : Pending tasks count
 *   - 2.5 : Recent activity feed
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../middleware/rbac.php';
require_once __DIR__ . '/../services/AdminService.php';
require_once __DIR__ . '/../utils/Response.php';

handleCors();

$method = $_SERVER['REQUEST_METHOD'];
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Strip base path prefix
$uri = preg_replace('#^.*/api/admin#', '', $uri);

// All admin routes require superadmin role
requireAuth();
requireSuperAdmin();

// ── GET /api/admin/stats ──────────────────────────────────────────────────────
if ($method === 'GET' && $uri === '/stats') {
    $stats = AdminService::getDashboardStats();
    Response::success($stats);
}

// ── GET /api/admin/active-sessions ───────────────────────────────────────────
elseif ($method === 'GET' && $uri === '/active-sessions') {
    $limit    = min((int)($_GET['limit']  ?? 50), 200);
    $offset   = max((int)($_GET['offset'] ?? 0), 0);
    $sessions = AdminService::getActiveSessions($limit, $offset);

    Response::success([
        'sessions' => $sessions,
        'count'    => count($sessions),
        'total'    => AdminService::getActiveSessionsCount(),
    ]);
}

// ── GET /api/admin/recent-activity ───────────────────────────────────────────
elseif ($method === 'GET' && $uri === '/recent-activity') {
    $limit    = min((int)($_GET['limit'] ?? 20), 100);
    $activity = AdminService::getRecentActivity($limit);

    Response::success([
        'activity' => $activity,
        'count'    => count($activity),
    ]);
}

// ── 404 fallback ──────────────────────────────────────────────────────────────
else {
    Response::notFound('Endpoint tidak ditemukan.');
}
