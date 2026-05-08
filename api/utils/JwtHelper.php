<?php
/**
 * SpeakOn! — JwtHelper
 *
 * Wraps firebase/php-jwt to generate and verify JWT access and refresh tokens.
 *
 * Requirements covered:
 *   - 1.1  : Generate access token on successful login
 *   - 1.5  : Access token expires after 30 minutes (session timeout)
 *   - 1.4  : Refresh token stored as SHA-256 hash in refresh_tokens table
 *   - 11.7 : Return 401 TOKEN_EXPIRED when token is expired
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

// Load firebase/php-jwt via Composer autoloader
$composerAutoload = __DIR__ . '/../../vendor/autoload.php';
if (file_exists($composerAutoload)) {
    require_once $composerAutoload;
} else {
    // Fallback: try one level up (project root)
    $fallback = __DIR__ . '/../../../vendor/autoload.php';
    if (file_exists($fallback)) {
        require_once $fallback;
    }
}

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use Firebase\JWT\BeforeValidException;

class JwtHelper
{
    /** Algorithm used for signing JWTs */
    private const ALGORITHM = 'HS256';

    // ──────────────────────────────────────────────────────────────────────────
    // Access Token
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Generate a signed JWT access token for the given user.
     *
     * Payload:
     *   - sub  : user UUID
     *   - role : user role ('superadmin' | 'dosen' | 'siswa')
     *   - iat  : issued-at timestamp
     *   - exp  : expiry timestamp (iat + JWT_ACCESS_EXPIRY seconds, default 30 min)
     *   - type : 'access'
     *
     * @param  string $userId  The user's UUID.
     * @param  string $role    The user's role.
     * @return string          Signed JWT string.
     */
    public function generateAccessToken(string $userId, string $role): string
    {
        $now = time();

        $payload = [
            'sub'  => $userId,
            'role' => $role,
            'iat'  => $now,
            'exp'  => $now + JWT_ACCESS_EXPIRY,
            'type' => 'access',
        ];

        return JWT::encode($payload, JWT_SECRET, self::ALGORITHM);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Refresh Token
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Generate a signed JWT refresh token and persist its SHA-256 hash to the
     * refresh_tokens table.
     *
     * Payload:
     *   - sub  : user UUID
     *   - iat  : issued-at timestamp
     *   - exp  : expiry timestamp (iat + JWT_REFRESH_EXPIRY seconds, default 7 days)
     *   - type : 'refresh'
     *   - jti  : unique token ID (UUID v4) — stored in DB for revocation
     *
     * @param  string $userId  The user's UUID.
     * @return string          Signed JWT refresh token string.
     */
    public function generateRefreshToken(string $userId): string
    {
        $now = time();
        $jti = generateUUID(); // unique token identifier

        $payload = [
            'sub'  => $userId,
            'iat'  => $now,
            'exp'  => $now + JWT_REFRESH_EXPIRY,
            'type' => 'refresh',
            'jti'  => $jti,
        ];

        $token = JWT::encode($payload, JWT_REFRESH_SECRET, self::ALGORITHM);

        // Persist the SHA-256 hash of the raw token string to the database
        $this->storeRefreshToken($jti, $userId, $token, $now + JWT_REFRESH_EXPIRY);

        return $token;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Token Verification
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Verify and decode a JWT token (access or refresh).
     *
     * Returns the decoded payload as a stdClass on success.
     * Throws a RuntimeException with a structured JSON error on failure.
     *
     * Error codes thrown:
     *   - TOKEN_EXPIRED   : token has passed its exp claim
     *   - TOKEN_INVALID   : signature mismatch, malformed, or wrong type
     *
     * @param  string $token      The raw JWT string.
     * @param  string $tokenType  'access' or 'refresh' — validated against the 'type' claim.
     * @return stdClass           Decoded JWT payload.
     * @throws RuntimeException   On invalid or expired token.
     */
    public function verifyToken(string $token, string $tokenType = 'access'): stdClass
    {
        $secret = ($tokenType === 'refresh') ? JWT_REFRESH_SECRET : JWT_SECRET;

        try {
            $decoded = JWT::decode($token, new Key($secret, self::ALGORITHM));
        } catch (ExpiredException $e) {
            throw new RuntimeException(json_encode([
                'code'    => 'TOKEN_EXPIRED',
                'message' => 'Token autentikasi telah kedaluwarsa. Silakan login kembali.',
                'details' => null,
            ]));
        } catch (SignatureInvalidException $e) {
            throw new RuntimeException(json_encode([
                'code'    => 'TOKEN_INVALID',
                'message' => 'Token autentikasi tidak valid.',
                'details' => null,
            ]));
        } catch (BeforeValidException $e) {
            throw new RuntimeException(json_encode([
                'code'    => 'TOKEN_INVALID',
                'message' => 'Token autentikasi belum berlaku.',
                'details' => null,
            ]));
        } catch (\Exception $e) {
            throw new RuntimeException(json_encode([
                'code'    => 'TOKEN_INVALID',
                'message' => 'Token autentikasi tidak valid atau rusak.',
                'details' => null,
            ]));
        }

        // Validate the 'type' claim to prevent access tokens being used as refresh tokens
        if (!isset($decoded->type) || $decoded->type !== $tokenType) {
            throw new RuntimeException(json_encode([
                'code'    => 'TOKEN_INVALID',
                'message' => 'Jenis token tidak sesuai.',
                'details' => null,
            ]));
        }

        // For refresh tokens: also verify the token has not been revoked in the DB
        if ($tokenType === 'refresh') {
            $this->assertRefreshTokenNotRevoked($token);
        }

        return $decoded;
    }

    /**
     * Verify an access token and return its decoded payload.
     * Convenience wrapper around verifyToken() for access tokens.
     *
     * @param  string $token  The raw JWT access token string.
     * @return stdClass       Decoded payload with sub, role, iat, exp.
     * @throws RuntimeException on TOKEN_EXPIRED or TOKEN_INVALID.
     */
    public function verifyAccessToken(string $token): stdClass
    {
        return $this->verifyToken($token, 'access');
    }

    /**
     * Verify a refresh token and return its decoded payload.
     * Also checks that the token has not been revoked in the database.
     *
     * @param  string $token  The raw JWT refresh token string.
     * @return stdClass       Decoded payload with sub, jti, iat, exp.
     * @throws RuntimeException on TOKEN_EXPIRED, TOKEN_INVALID, or revoked token.
     */
    public function verifyRefreshToken(string $token): stdClass
    {
        return $this->verifyToken($token, 'refresh');
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Internal helpers
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Persist the SHA-256 hash of a refresh token to the refresh_tokens table.
     *
     * @param  string $jti        The unique token ID (UUID) stored in the JWT payload.
     * @param  string $userId     The user's UUID.
     * @param  string $rawToken   The raw JWT string (will be hashed before storage).
     * @param  int    $expiresAt  Unix timestamp when the token expires.
     * @return void
     */
    private function storeRefreshToken(string $jti, string $userId, string $rawToken, int $expiresAt): void
    {
        $tokenHash = hash('sha256', $rawToken);
        $pdo       = getDB();

        $stmt = $pdo->prepare(
            'INSERT INTO refresh_tokens (id, user_id, token_hash, expires_at, created_at, revoked_at)
                  VALUES (:id, :user_id, :token_hash, :expires_at, NOW(), NULL)'
        );
        $stmt->execute([
            ':id'         => $jti,
            ':user_id'    => $userId,
            ':token_hash' => $tokenHash,
            ':expires_at' => date('Y-m-d H:i:s', $expiresAt),
        ]);
    }

    /**
     * Assert that a refresh token has not been revoked in the database.
     *
     * @param  string $rawToken  The raw JWT refresh token string.
     * @return void
     * @throws RuntimeException if the token is revoked or not found.
     */
    private function assertRefreshTokenNotRevoked(string $rawToken): void
    {
        $tokenHash = hash('sha256', $rawToken);
        $pdo       = getDB();

        $stmt = $pdo->prepare(
            'SELECT revoked_at
               FROM refresh_tokens
              WHERE token_hash = :hash
              LIMIT 1'
        );
        $stmt->execute([':hash' => $tokenHash]);
        $row = $stmt->fetch();

        if (!$row) {
            throw new RuntimeException(json_encode([
                'code'    => 'TOKEN_INVALID',
                'message' => 'Refresh token tidak ditemukan.',
                'details' => null,
            ]));
        }

        if ($row['revoked_at'] !== null) {
            throw new RuntimeException(json_encode([
                'code'    => 'TOKEN_INVALID',
                'message' => 'Refresh token telah dicabut. Silakan login kembali.',
                'details' => null,
            ]));
        }
    }
}
