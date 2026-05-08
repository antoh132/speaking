<?php
/**
 * SpeakOn! — Authentication Routes
 *
 * POST /api/auth/login    — Authenticate user, return tokens
 * POST /api/auth/logout   — Revoke refresh token
 * POST /api/auth/refresh  — Get new access token using refresh token
 *
 * Requirements covered:
 *   - 1.1 : Authenticate user and create active session
 *   - 1.2 : Reject invalid credentials
 *   - 1.3 : Redirect to role-specific dashboard after login
 *   - 1.4 : Logout revokes refresh token
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../services/AuthService.php';
require_once __DIR__ . '/../services/AuditService.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../utils/Validator.php';
require_once __DIR__ . '/../utils/JwtHelper.php';

handleCors();

$method = $_SERVER['REQUEST_METHOD'];
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Strip base path prefix (e.g. /speakon/api/auth/login → /login)
$uri = preg_replace('#^.*/api/auth#', '', $uri);

// ── POST /api/auth/login ──────────────────────────────────────────────────────
if ($method === 'POST' && $uri === '/login') {
    try {
        $body = Validator::parseJsonBody();

        $email    = Validator::email(Validator::get($body, 'email'));
        $password = Validator::required(Validator::get($body, 'password'), 'password');
        $ip       = AuditService::getClientIp();

        $result = AuthService::login($email, $password, $ip);

        // Log successful login
        AuditService::logAuth(
            AuditService::ACTION_LOGIN,
            $result['user']['id'],
            $result['user']['role'],
            $email,
            $ip
        );

        Response::success([
            'accessToken'  => $result['accessToken'],
            'refreshToken' => $result['refreshToken'],
            'user'         => $result['user'],
            'redirectPath' => getRedirectPath($result['user']['role']),
        ]);

    } catch (InvalidArgumentException $e) {
        Response::validationError($e->getMessage());
    } catch (RuntimeException $e) {
        $error = json_decode($e->getMessage(), true);
        $code  = $error['code']    ?? 'AUTH_ERROR';
        $msg   = $error['message'] ?? 'Autentikasi gagal.';

        // Log failed login attempt
        $ip    = AuditService::getClientIp();
        $email = Validator::get(Validator::parseJsonBody(), 'email') ?? '';
        AuditService::logAuth(AuditService::ACTION_LOGIN_FAILED, null, null, (string)$email, $ip);

        if ($code === 'ACCOUNT_LOCKED') {
            AuditService::logAuth(AuditService::ACTION_ACCOUNT_LOCKED, null, null, (string)$email, $ip);
            Response::error(423, $code, $msg, $error['details'] ?? null);
        }

        if ($code === 'ACCOUNT_INACTIVE') {
            Response::error(403, $code, $msg);
        }

        Response::error(401, $code, $msg);
    }
}

// ── POST /api/auth/logout ─────────────────────────────────────────────────────
elseif ($method === 'POST' && $uri === '/logout') {
    try {
        $body         = Validator::parseJsonBody();
        $refreshToken = Validator::required(Validator::get($body, 'refreshToken'), 'refreshToken');

        // Optionally get current user from access token for audit log
        $userId   = null;
        $userRole = null;
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (!empty($authHeader) && str_starts_with($authHeader, 'Bearer ')) {
            try {
                $jwtHelper = new JwtHelper();
                $decoded   = $jwtHelper->verifyAccessToken(substr($authHeader, 7));
                $userId    = $decoded->sub;
                $userRole  = $decoded->role;
            } catch (RuntimeException $e) {
                // Token may be expired — still allow logout
            }
        }

        AuthService::logout($refreshToken);

        AuditService::logAuth(
            AuditService::ACTION_LOGOUT,
            $userId,
            $userRole,
            '',
            AuditService::getClientIp()
        );

        Response::success(['message' => 'Logout berhasil.']);

    } catch (InvalidArgumentException $e) {
        Response::validationError($e->getMessage());
    } catch (RuntimeException $e) {
        Response::serverError('Gagal melakukan logout.');
    }
}

// ── POST /api/auth/refresh ────────────────────────────────────────────────────
elseif ($method === 'POST' && $uri === '/refresh') {
    try {
        $body         = Validator::parseJsonBody();
        $refreshToken = Validator::required(Validator::get($body, 'refreshToken'), 'refreshToken');

        $jwtHelper = new JwtHelper();
        $decoded   = $jwtHelper->verifyRefreshToken($refreshToken);

        // Generate a new access token
        $pdo  = getDB();
        $stmt = $pdo->prepare('SELECT role FROM users WHERE id = :id AND is_active = 1 LIMIT 1');
        $stmt->execute([':id' => $decoded->sub]);
        $user = $stmt->fetch();

        if (!$user) {
            Response::unauthorized('TOKEN_INVALID', 'Pengguna tidak ditemukan atau tidak aktif.');
        }

        $newAccessToken = $jwtHelper->generateAccessToken($decoded->sub, $user['role']);

        Response::success(['accessToken' => $newAccessToken]);

    } catch (InvalidArgumentException $e) {
        Response::validationError($e->getMessage());
    } catch (RuntimeException $e) {
        $error = json_decode($e->getMessage(), true);
        $code  = $error['code']    ?? 'TOKEN_INVALID';
        $msg   = $error['message'] ?? 'Refresh token tidak valid.';

        if ($code === 'TOKEN_EXPIRED') {
            Response::unauthorized('TOKEN_EXPIRED', $msg);
        }
        Response::unauthorized('TOKEN_INVALID', $msg);
    }
}

// ── 404 fallback ──────────────────────────────────────────────────────────────
else {
    Response::notFound('Endpoint tidak ditemukan.');
}

// ── Helper: redirect path by role ────────────────────────────────────────────

/**
 * Return the frontend dashboard path for a given role.
 *
 * @param  string $role  'superadmin' | 'dosen' | 'siswa'
 * @return string        Relative path to the dashboard HTML page.
 */
function getRedirectPath(string $role): string
{
    // Deteksi apakah berjalan di subfolder /speakon/ atau di root
    $requestUri = $_SERVER['REQUEST_URI'] ?? '';
    $base = str_starts_with($requestUri, '/speakon/') ? '/speakon' : '';

    return match ($role) {
        'superadmin' => $base . '/dashboard-superadmin.html',
        'dosen'      => $base . '/dashboard-dosen.html',
        'siswa'      => $base . '/dashboard-siswa.html',
        default      => $base . '/login.html',
    };
}
