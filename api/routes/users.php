<?php
/**
 * SpeakOn! — User Management Routes
 *
 * POST   /api/users          — Create user (superadmin only)
 * GET    /api/users          — List all users (superadmin only)
 * GET    /api/users/{id}     — Get user by ID
 * PUT    /api/users/{id}     — Update user
 * DELETE /api/users/{id}     — Deactivate user (superadmin only)
 *
 * Requirements covered:
 *   - 3.1 : Create user accounts
 *   - 3.2 : Email uniqueness
 *   - 3.3 : Update user information
 *   - 3.4 : Deactivate user accounts
 *   - 3.5 : Role-based access control
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../middleware/rbac.php';
require_once __DIR__ . '/../services/UserService.php';
require_once __DIR__ . '/../services/AuditService.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../utils/Validator.php';

handleCors();

$method = $_SERVER['REQUEST_METHOD'];
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Strip base path prefix (e.g. /speakon/api/users → /users)
$uri = preg_replace('#^.*/api/users#', '', $uri);

// ── POST /api/users — Create user ────────────────────────────────────────────
if ($method === 'POST' && ($uri === '' || $uri === '/')) {
    requireAuth();
    requireSuperAdmin();

    try {
        $body = Validator::parseJsonBody();

        $fullName = Validator::required(Validator::get($body, 'full_name'), 'full_name');
        $fullName = Validator::minLength($fullName, 2, 'full_name');
        $fullName = Validator::maxLength($fullName, 100, 'full_name');

        $email = Validator::email(Validator::get($body, 'email'));

        $role = Validator::enum(
            Validator::get($body, 'role'),
            ['dosen', 'siswa'],
            'role'
        );

        $result = UserService::createUser($fullName, $email, $role, $currentUser->id);

        AuditService::writeLog(
            $currentUser->id,
            $currentUser->role,
            AuditService::ACTION_USER_CREATED,
            AuditService::ENTITY_USER,
            $result['user']['id'],
            ['email' => $email, 'role' => $role],
            AuditService::getClientIp()
        );

        Response::success([
            'user'         => $result['user'],
            'tempPassword' => $result['tempPassword'],
        ], 201);

    } catch (InvalidArgumentException $e) {
        Response::validationError($e->getMessage());
    } catch (RuntimeException $e) {
        $error = json_decode($e->getMessage(), true);
        if ($error && isset($error['code'])) {
            if ($error['code'] === 'EMAIL_ALREADY_EXISTS') {
                Response::conflict($error['code'], $error['message']);
            }
        }
        Response::serverError('Gagal membuat pengguna.');
    }
}

// ── GET /api/users — List all users ──────────────────────────────────────────
elseif ($method === 'GET' && ($uri === '' || $uri === '/')) {
    requireAuth();
    requireSuperAdmin();

    $filters = [];
    if (!empty($_GET['role']))      $filters['role']      = $_GET['role'];
    if (isset($_GET['is_active']))  $filters['is_active'] = (int)$_GET['is_active'];
    if (!empty($_GET['search']))    $filters['search']    = $_GET['search'];

    $limit  = min((int)($_GET['limit']  ?? 50), 200);
    $offset = max((int)($_GET['offset'] ?? 0), 0);

    $users = UserService::getAllUsers($filters, $limit, $offset);
    Response::success(['users' => $users, 'count' => count($users)]);
}

// ── GET /api/users/{id} — Get user by ID ─────────────────────────────────────
elseif ($method === 'GET' && preg_match('#^/([0-9a-f\-]{36})$#i', $uri, $m)) {
    requireAuth();

    $targetId = $m[1];

    // Siswa and Dosen can only view their own profile; superadmin can view any
    if ($currentUser->role !== 'superadmin' && $currentUser->id !== $targetId) {
        Response::forbidden('FORBIDDEN', 'Anda hanya dapat melihat profil Anda sendiri.');
    }

    $user = UserService::getUserById($targetId);
    if (!$user) {
        Response::notFound('Pengguna tidak ditemukan.');
    }

    Response::success(['user' => $user]);
}

// ── PUT /api/users/{id} — Update user ────────────────────────────────────────
elseif ($method === 'PUT' && preg_match('#^/([0-9a-f\-]{36})$#i', $uri, $m)) {
    requireAuth();

    $targetId = $m[1];

    // Only superadmin can update any user; others can only update themselves
    if ($currentUser->role !== 'superadmin' && $currentUser->id !== $targetId) {
        Response::forbidden('FORBIDDEN', 'Anda hanya dapat mengubah profil Anda sendiri.');
    }

    try {
        $body    = Validator::parseJsonBody();
        $updates = [];

        if (isset($body['full_name'])) {
            $updates['full_name'] = Validator::minLength(
                Validator::maxLength(trim($body['full_name']), 100, 'full_name'),
                2, 'full_name'
            );
        }

        if (isset($body['email'])) {
            $updates['email'] = Validator::email($body['email']);
        }

        if (isset($body['language_pref'])) {
            $updates['language_pref'] = Validator::enum($body['language_pref'], ['id', 'en'], 'language_pref');
        }

        // Only superadmin can change roles
        if (isset($body['role']) && $currentUser->role === 'superadmin') {
            $updates['role'] = Validator::enum($body['role'], ['dosen', 'siswa', 'superadmin'], 'role');
        }

        $updatedUser = UserService::updateUser($targetId, $updates);

        AuditService::writeLog(
            $currentUser->id,
            $currentUser->role,
            AuditService::ACTION_USER_UPDATED,
            AuditService::ENTITY_USER,
            $targetId,
            ['updated_fields' => array_keys($updates)],
            AuditService::getClientIp()
        );

        Response::success(['user' => $updatedUser]);

    } catch (InvalidArgumentException $e) {
        Response::validationError($e->getMessage());
    } catch (RuntimeException $e) {
        $error = json_decode($e->getMessage(), true);
        if ($error && isset($error['code'])) {
            if ($error['code'] === 'EMAIL_ALREADY_EXISTS') {
                Response::conflict($error['code'], $error['message']);
            }
            if ($error['code'] === 'USER_NOT_FOUND') {
                Response::notFound($error['message']);
            }
        }
        Response::serverError('Gagal memperbarui pengguna.');
    }
}

// ── DELETE /api/users/{id} — Deactivate user ─────────────────────────────────
elseif ($method === 'DELETE' && preg_match('#^/([0-9a-f\-]{36})$#i', $uri, $m)) {
    requireAuth();
    requireSuperAdmin();

    $targetId = $m[1];

    // Prevent superadmin from deactivating themselves
    if ($currentUser->id === $targetId) {
        Response::error(400, 'SELF_DEACTIVATION', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
    }

    $success = UserService::deactivateUser($targetId);

    if (!$success) {
        Response::notFound('Pengguna tidak ditemukan atau sudah tidak aktif.');
    }

    AuditService::writeLog(
        $currentUser->id,
        $currentUser->role,
        AuditService::ACTION_USER_DEACTIVATED,
        AuditService::ENTITY_USER,
        $targetId,
        null,
        AuditService::getClientIp()
    );

    Response::success(['message' => 'Pengguna berhasil dinonaktifkan.']);
}

// ── 404 fallback ──────────────────────────────────────────────────────────────
else {
    Response::notFound('Endpoint tidak ditemukan.');
}
