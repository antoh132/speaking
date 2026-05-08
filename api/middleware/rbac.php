<?php
/**
 * SpeakOn! — RBAC (Role-Based Access Control) Middleware
 *
 * Provides role enforcement for API endpoints.
 * Must be called AFTER requireAuth() so that $currentUser is set.
 *
 * Requirements covered:
 *   - 11.1 : Super Admin can access all endpoints
 *   - 11.2 : Dosen can only access dosen-specific endpoints
 *   - 11.3 : Siswa can only access siswa-specific endpoints
 *   - 11.4 : Users cannot access other users' data
 */

require_once __DIR__ . '/../utils/Response.php';

/**
 * Enforce that the authenticated user has one of the allowed roles.
 *
 * @param  string[] $allowedRoles  Array of role strings that are permitted.
 *                                 Valid values: 'superadmin', 'dosen', 'siswa'
 * @return void  Terminates with 403 if the user's role is not in $allowedRoles.
 */
function requireRole(array $allowedRoles): void
{
    global $currentUser;

    if ($currentUser === null) {
        Response::unauthorized('TOKEN_MISSING', 'Autentikasi diperlukan.');
    }

    if (!in_array($currentUser->role, $allowedRoles, true)) {
        Response::forbidden(
            'FORBIDDEN',
            'Anda tidak memiliki izin untuk mengakses resource ini.'
        );
    }
}

/**
 * Enforce that the authenticated user is a Super Admin.
 *
 * @return void
 */
function requireSuperAdmin(): void
{
    requireRole(['superadmin']);
}

/**
 * Enforce that the authenticated user is a Dosen.
 *
 * @return void
 */
function requireDosen(): void
{
    requireRole(['dosen']);
}

/**
 * Enforce that the authenticated user is a Siswa.
 *
 * @return void
 */
function requireSiswa(): void
{
    requireRole(['siswa']);
}

/**
 * Enforce that the authenticated user is either a Dosen or Super Admin.
 *
 * @return void
 */
function requireDosenOrAdmin(): void
{
    requireRole(['dosen', 'superadmin']);
}

/**
 * Enforce that the authenticated user owns the resource (by user ID),
 * OR is a Super Admin.
 *
 * Use this to prevent users from accessing other users' data.
 *
 * @param  string $resourceOwnerId  The UUID of the resource owner.
 * @return void  Terminates with 403 if the user is not the owner and not superadmin.
 */
function requireOwnerOrAdmin(string $resourceOwnerId): void
{
    global $currentUser;

    if ($currentUser === null) {
        Response::unauthorized('TOKEN_MISSING', 'Autentikasi diperlukan.');
    }

    if ($currentUser->role === 'superadmin') {
        return; // Super Admin can access any resource
    }

    if ($currentUser->id !== $resourceOwnerId) {
        Response::forbidden(
            'FORBIDDEN',
            'Anda hanya dapat mengakses data milik Anda sendiri.'
        );
    }
}
