<?php
/**
 * SpeakOn! — Authentication Middleware
 *
 * Reads the JWT access token from the Authorization header,
 * verifies it, and sets the global $currentUser object.
 *
 * Requirements covered:
 *   - 1.1  : Authenticate user via JWT
 *   - 11.7 : Return 401 TOKEN_EXPIRED when token is expired
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../utils/JwtHelper.php';

/**
 * Authenticate the current request by verifying the Bearer JWT token.
 *
 * On success, sets the global $currentUser stdClass with:
 *   - id   : user UUID
 *   - role : user role ('superadmin' | 'dosen' | 'siswa')
 *
 * On failure, sends a 401 JSON response and terminates execution.
 *
 * @return void
 */
function requireAuth(): void
{
    global $currentUser;

    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

    // Support Apache environments that strip Authorization header
    if (empty($authHeader) && function_exists('apache_request_headers')) {
        $headers    = apache_request_headers();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
    }

    if (empty($authHeader) || !str_starts_with($authHeader, 'Bearer ')) {
        Response::unauthorized('TOKEN_MISSING', 'Token autentikasi tidak ditemukan. Sertakan header Authorization: Bearer <token>.');
    }

    $token = substr($authHeader, 7); // Strip "Bearer " prefix

    if (empty(trim($token))) {
        Response::unauthorized('TOKEN_MISSING', 'Token autentikasi kosong.');
    }

    try {
        $jwtHelper   = new JwtHelper();
        $decoded     = $jwtHelper->verifyAccessToken($token);
        $currentUser = (object)[
            'id'   => $decoded->sub,
            'role' => $decoded->role,
        ];
    } catch (RuntimeException $e) {
        $error = json_decode($e->getMessage(), true);
        $code  = $error['code']    ?? 'TOKEN_INVALID';
        $msg   = $error['message'] ?? 'Token autentikasi tidak valid.';

        if ($code === 'TOKEN_EXPIRED') {
            Response::unauthorized('TOKEN_EXPIRED', $msg);
        }

        Response::unauthorized('TOKEN_INVALID', $msg);
    }
}

/**
 * Optionally authenticate the current request.
 * Sets $currentUser if a valid token is present, but does NOT terminate
 * execution if no token is provided. Useful for endpoints that behave
 * differently for authenticated vs. anonymous users.
 *
 * @return void
 */
function optionalAuth(): void
{
    global $currentUser;

    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

    if (empty($authHeader) || !str_starts_with($authHeader, 'Bearer ')) {
        return; // No token — continue as anonymous
    }

    $token = substr($authHeader, 7);

    if (empty(trim($token))) {
        return;
    }

    try {
        $jwtHelper   = new JwtHelper();
        $decoded     = $jwtHelper->verifyAccessToken($token);
        $currentUser = (object)[
            'id'   => $decoded->sub,
            'role' => $decoded->role,
        ];
    } catch (RuntimeException $e) {
        // Invalid token — treat as anonymous (do not terminate)
        $currentUser = null;
    }
}
