<?php
/**
 * SpeakOn! — Level Routes
 *
 * GET /api/levels              — List all levels with student progress status
 * GET /api/levels/progress     — Progress tracker data for current student
 * GET /api/levels/{id}/materials — Level materials (if student has access)
 *
 * Requirements covered:
 *   - 4.5 : Students can view their progress across all 5 levels
 *   - 4.6 : Students can access level materials for unlocked levels
 *   - 8.1 : Progress tracker shows percentage completion
 *   - 8.4 : Progress percentage is calculated correctly
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../middleware/rbac.php';
require_once __DIR__ . '/../services/LevelService.php';
require_once __DIR__ . '/../utils/Response.php';

handleCors();

$method = $_SERVER['REQUEST_METHOD'];
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Strip base path prefix
$uri = preg_replace('#^.*/api/levels#', '', $uri);

// ── GET /api/levels/progress — Progress tracker ───────────────────────────────
if ($method === 'GET' && $uri === '/progress') {
    requireAuth();
    requireSiswa();

    $details = LevelService::getProgressDetails($currentUser->id);
    Response::success($details);
}

// ── GET /api/levels — List all levels ────────────────────────────────────────
elseif ($method === 'GET' && ($uri === '' || $uri === '/')) {
    requireAuth();

    $pdo    = getDB();
    $levels = $pdo->query(
        'SELECT id, name, description, order_index FROM levels ORDER BY order_index ASC'
    )->fetchAll();

    // For students, enrich with their progress status
    if ($currentUser->role === 'siswa') {
        $progress = LevelService::getStudentProgress($currentUser->id);
        $statusMap = [];
        foreach ($progress as $p) {
            $statusMap[$p['id']] = $p['status'];
        }
        foreach ($levels as &$level) {
            $level['status'] = $statusMap[$level['id']] ?? LevelService::STATUS_LOCKED;
        }
        unset($level);
    }

    Response::success(['levels' => $levels]);
}

// ── GET /api/levels/{id}/materials — Level materials ─────────────────────────
elseif ($method === 'GET' && preg_match('#^/([0-9a-f\-]{36})/materials$#i', $uri, $m)) {
    requireAuth();

    $levelId = $m[1];

    // Students can only access materials for unlocked levels
    if ($currentUser->role === 'siswa') {
        if (!LevelService::hasAccessToLevel($currentUser->id, $levelId)) {
            Response::forbidden('LEVEL_LOCKED', 'Level ini belum terbuka. Selesaikan level sebelumnya terlebih dahulu.');
        }
    }

    $pdo  = getDB();
    $stmt = $pdo->prepare(
        'SELECT id, level_id, title, content, order_index
           FROM level_materials
          WHERE level_id = :level_id
          ORDER BY order_index ASC'
    );
    $stmt->execute([':level_id' => $levelId]);
    $materials = $stmt->fetchAll();

    Response::success(['materials' => $materials]);
}

// ── 404 fallback ──────────────────────────────────────────────────────────────
else {
    Response::notFound('Endpoint tidak ditemukan.');
}
