<?php
/**
 * SpeakOn! — Audit Log Routes
 *
 * GET /api/audit-logs         — Search audit logs (superadmin only)
 * GET /api/audit-logs/export  — Export audit logs as CSV (superadmin only)
 *
 * Requirements covered:
 *   - 2.6 : Super Admin can export audit logs
 *   - 9.3 : Audit logs can be searched and filtered
 *   - 9.4 : Audit log export includes all relevant fields
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../middleware/rbac.php';
require_once __DIR__ . '/../services/AuditService.php';
require_once __DIR__ . '/../utils/Response.php';

handleCors();

$method = $_SERVER['REQUEST_METHOD'];
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Strip base path prefix
$uri = preg_replace('#^.*/api/audit-logs#', '', $uri);

// ── GET /api/audit-logs/export — Export CSV ───────────────────────────────────
if ($method === 'GET' && $uri === '/export') {
    requireAuth();
    requireSuperAdmin();

    $filters = buildFilters();
    $logs    = queryAuditLogs($filters, 10000, 0); // Export up to 10,000 rows

    // Log the export action itself
    AuditService::writeLog(
        $currentUser->id,
        $currentUser->role,
        AuditService::ACTION_AUDIT_LOG_EXPORTED,
        AuditService::ENTITY_AUDIT_LOG,
        null,
        ['filters' => $filters, 'row_count' => count($logs)],
        AuditService::getClientIp()
    );

    $filename = 'audit-log-' . date('Y-m-d-His') . '.csv';

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-cache, no-store, must-revalidate');

    $output = fopen('php://output', 'w');

    // UTF-8 BOM for Excel compatibility
    fputs($output, "\xEF\xBB\xBF");

    // CSV header row
    fputcsv($output, [
        'ID', 'User ID', 'User Role', 'Action Type',
        'Entity Type', 'Entity ID', 'Metadata', 'IP Address', 'Created At',
    ]);

    foreach ($logs as $log) {
        fputcsv($output, [
            $log['id'],
            $log['user_id']     ?? '',
            $log['user_role']   ?? '',
            $log['action_type'],
            $log['entity_type'] ?? '',
            $log['entity_id']   ?? '',
            $log['metadata']    ?? '',
            $log['ip_address']  ?? '',
            $log['created_at'],
        ]);
    }

    fclose($output);
    exit;
}

// ── GET /api/audit-logs — Search audit logs ───────────────────────────────────
elseif ($method === 'GET' && ($uri === '' || $uri === '/')) {
    requireAuth();
    requireSuperAdmin();

    $filters = buildFilters();
    $limit   = min((int)($_GET['limit']  ?? 50), 500);
    $offset  = max((int)($_GET['offset'] ?? 0), 0);

    $logs  = queryAuditLogs($filters, $limit, $offset);
    $total = countAuditLogs($filters);

    Response::success([
        'logs'   => $logs,
        'count'  => count($logs),
        'total'  => $total,
        'limit'  => $limit,
        'offset' => $offset,
    ]);
}

// ── 404 fallback ──────────────────────────────────────────────────────────────
else {
    Response::notFound('Endpoint tidak ditemukan.');
}

// ── Helper functions ──────────────────────────────────────────────────────────

/**
 * Build filter array from query string parameters.
 *
 * @return array
 */
function buildFilters(): array
{
    $filters = [];

    if (!empty($_GET['user_id']))     $filters['user_id']     = $_GET['user_id'];
    if (!empty($_GET['user_role']))   $filters['user_role']   = $_GET['user_role'];
    if (!empty($_GET['action_type'])) $filters['action_type'] = $_GET['action_type'];
    if (!empty($_GET['entity_type'])) $filters['entity_type'] = $_GET['entity_type'];
    if (!empty($_GET['date_from']))   $filters['date_from']   = $_GET['date_from'];
    if (!empty($_GET['date_to']))     $filters['date_to']     = $_GET['date_to'];
    if (!empty($_GET['ip_address']))  $filters['ip_address']  = $_GET['ip_address'];

    return $filters;
}

/**
 * Query audit logs with filters.
 *
 * @param  array $filters
 * @param  int   $limit
 * @param  int   $offset
 * @return array
 */
function queryAuditLogs(array $filters, int $limit, int $offset): array
{
    $pdo    = getDB();
    $where  = ['1=1'];
    $params = [];

    applyFilters($filters, $where, $params);

    $sql = 'SELECT id, user_id, user_role, action_type, entity_type, entity_id,
                   metadata, ip_address, created_at
              FROM audit_logs
             WHERE ' . implode(' AND ', $where) . '
             ORDER BY created_at DESC
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

/**
 * Count total audit logs matching filters.
 *
 * @param  array $filters
 * @return int
 */
function countAuditLogs(array $filters): int
{
    $pdo    = getDB();
    $where  = ['1=1'];
    $params = [];

    applyFilters($filters, $where, $params);

    $sql  = 'SELECT COUNT(*) FROM audit_logs WHERE ' . implode(' AND ', $where);
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return (int)$stmt->fetchColumn();
}

/**
 * Apply filter conditions to WHERE clause and params arrays.
 *
 * @param  array  $filters
 * @param  array  &$where
 * @param  array  &$params
 * @return void
 */
function applyFilters(array $filters, array &$where, array &$params): void
{
    if (!empty($filters['user_id'])) {
        $where[]             = 'user_id = :user_id';
        $params[':user_id']  = $filters['user_id'];
    }
    if (!empty($filters['user_role'])) {
        $where[]               = 'user_role = :user_role';
        $params[':user_role']  = $filters['user_role'];
    }
    if (!empty($filters['action_type'])) {
        $where[]                 = 'action_type = :action_type';
        $params[':action_type']  = $filters['action_type'];
    }
    if (!empty($filters['entity_type'])) {
        $where[]                 = 'entity_type = :entity_type';
        $params[':entity_type']  = $filters['entity_type'];
    }
    if (!empty($filters['date_from'])) {
        $where[]               = 'created_at >= :date_from';
        $params[':date_from']  = $filters['date_from'] . ' 00:00:00';
    }
    if (!empty($filters['date_to'])) {
        $where[]             = 'created_at <= :date_to';
        $params[':date_to']  = $filters['date_to'] . ' 23:59:59';
    }
    if (!empty($filters['ip_address'])) {
        $where[]                = 'ip_address = :ip_address';
        $params[':ip_address']  = $filters['ip_address'];
    }
}
