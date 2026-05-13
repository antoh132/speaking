<?php
/**
 * SpeakOn! — AuthService
 *
 * Handles password hashing, login, account lockout, and logout.
 *
 * Requirements covered:
 *   - 11.6  : Password hashing with bcrypt cost 12
 *   - 1.1   : Authenticate user and create active session
 *   - 1.2   : Reject invalid credentials without revealing security details
 *   - 1.4   : Logout — revoke refresh token
 *   - 1.6   : Account lockout after 5 failed attempts (15 minutes)
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../utils/JwtHelper.php';

class AuthService
{
    // ──────────────────────────────────────────────────────────────────────────
    // 3.1  Password hashing
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Hash a plaintext password using bcrypt with the configured cost factor.
     *
     * @param  string $plaintext  The raw password supplied by the user.
     * @return string             The bcrypt hash (starts with $2y$12$…).
     */
    public static function hashPassword(string $plaintext): string
    {
        return password_hash($plaintext, PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]);
    }

    /**
     * Verify a plaintext password against a stored bcrypt hash.
     *
     * @param  string $plaintext  The raw password to verify.
     * @param  string $hash       The stored bcrypt hash.
     * @return bool               true if the password matches, false otherwise.
     */
    public static function verifyPassword(string $plaintext, string $hash): bool
    {
        return password_verify($plaintext, $hash);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 3.3  Login service
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Authenticate a user by email and password.
     *
     * Steps:
     *   1. Check account lockout.
     *   2. Look up user by email.
     *   3. Verify account is active.
     *   4. Verify password.
     *   5. Record login attempt (success or failure).
     *   6. On success: generate access + refresh tokens, return them with user data.
     *
     * @param  string $email      The user's email address.
     * @param  string $password   The plaintext password.
     * @param  string $ipAddress  The client's IP address (for audit / lockout).
     * @return array{
     *   accessToken: string,
     *   refreshToken: string,
     *   user: array
     * }
     * @throws RuntimeException with a structured error array on failure.
     */
    public static function login(string $email, string $password, string $ipAddress): array
    {
        $email = strtolower(trim($email));

        // 1. Check lockout before doing anything else
        $lockout = self::checkLockout($email);
        if ($lockout['locked']) {
            throw new RuntimeException(json_encode([
                'code'    => 'ACCOUNT_LOCKED',
                'message' => 'Akun dikunci sementara karena terlalu banyak percobaan login gagal. '
                           . 'Silakan coba lagi setelah ' . $lockout['locked_until'] . '.',
                'details' => ['locked_until' => $lockout['locked_until']],
            ]));
        }

        $pdo = getDB();

        // 2. Look up user by email
        $stmt = $pdo->prepare(
            'SELECT id, full_name, email, password_hash, role, is_active, language_pref
               FROM users
              WHERE email = :email
              LIMIT 1'
        );
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        var_dump($user, $email); 
        die();

        // 3. Verify user exists and account is active
        if (!$user) {
            // Record failed attempt even for non-existent emails (prevents enumeration timing)
            self::recordFailedAttempt($email, $ipAddress);
            throw new RuntimeException(json_encode([
                'code'    => 'INVALID_CREDENTIALS',
                'message' => 'Email atau password tidak valid.',
                'details' => null,
            ]));
        }

        if (!(bool)$user['is_active']) {
            // Do NOT record a lockout attempt for inactive accounts — just reject
            throw new RuntimeException(json_encode([
                'code'    => 'ACCOUNT_INACTIVE',
                'message' => 'Akun Anda telah dinonaktifkan. Hubungi administrator.',
                'details' => null,
            ]));
        }

        // 4. Verify password
        if (!self::verifyPassword($password, $user['password_hash'])) {
            self::recordFailedAttempt($email, $ipAddress);

            // Re-check lockout so we can surface the locked message immediately
            $lockoutAfter = self::checkLockout($email);
            if ($lockoutAfter['locked']) {
                throw new RuntimeException(json_encode([
                    'code'    => 'ACCOUNT_LOCKED',
                    'message' => 'Akun dikunci sementara karena terlalu banyak percobaan login gagal. '
                               . 'Silakan coba lagi setelah ' . $lockoutAfter['locked_until'] . '.',
                    'details' => ['locked_until' => $lockoutAfter['locked_until']],
                ]));
            }

            throw new RuntimeException(json_encode([
                'code'    => 'INVALID_CREDENTIALS',
                'message' => 'Email atau password tidak valid.',
                'details' => null,
            ]));
        }

        // 5. Record successful login attempt
        self::recordLoginAttempt($email, $ipAddress, true);

        // 6. Generate tokens
        $jwtHelper    = new JwtHelper();
        $accessToken  = $jwtHelper->generateAccessToken($user['id'], $user['role']);
        $refreshToken = $jwtHelper->generateRefreshToken($user['id']);

        // 7. Return tokens + sanitised user object (never return password_hash)
        return [
            'accessToken'  => $accessToken,
            'refreshToken' => $refreshToken,
            'user'         => [
                'id'           => $user['id'],
                'fullName'     => $user['full_name'],
                'email'        => $user['email'],
                'role'         => $user['role'],
                'languagePref' => $user['language_pref'],
            ],
        ];
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 3.4  Account lockout
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Check whether an email address is currently locked out.
     *
     * Lockout is determined by counting consecutive failed login attempts
     * within the last LOCKOUT_DURATION_MIN minutes.
     *
     * @param  string $email  The email address to check.
     * @return array{locked: bool, locked_until: string|null}
     */
    public static function checkLockout(string $email): array
    {
        $pdo = getDB();

        // First check the account_lockouts table for an active lockout record
        $stmt = $pdo->prepare(
            'SELECT al.locked_until
               FROM account_lockouts al
               JOIN users u ON u.id = al.user_id
              WHERE u.email = :email
                AND al.locked_until > NOW()
              ORDER BY al.locked_until DESC
              LIMIT 1'
        );
        $stmt->execute([':email' => strtolower(trim($email))]);
        $row = $stmt->fetch();

        if ($row) {
            return [
                'locked'       => true,
                'locked_until' => $row['locked_until'],
            ];
        }

        return ['locked' => false, 'locked_until' => null];
    }

    /**
     * Record a failed login attempt and lock the account if the threshold is reached.
     *
     * Counts failed attempts in the last LOCKOUT_DURATION_MIN minutes.
     * If the count reaches LOCKOUT_MAX_ATTEMPTS, inserts a lockout record.
     *
     * @param  string $email      The email address that failed to authenticate.
     * @param  string $ipAddress  The client's IP address.
     * @return void
     */
    public static function recordFailedAttempt(string $email, string $ipAddress): void
    {
        $email = strtolower(trim($email));

        // Record the failed attempt
        self::recordLoginAttempt($email, $ipAddress, false);

        // Count consecutive failures in the lockout window
        $pdo  = getDB();
        $stmt = $pdo->prepare(
            'SELECT COUNT(*) AS cnt
               FROM login_attempts
              WHERE email   = :email
                AND success = 0
                AND attempted_at >= DATE_SUB(NOW(), INTERVAL :minutes MINUTE)'
        );
        $stmt->execute([
            ':email'   => $email,
            ':minutes' => LOCKOUT_DURATION_MIN,
        ]);
        $row = $stmt->fetch();

        if ((int)$row['cnt'] >= LOCKOUT_MAX_ATTEMPTS) {
            // Look up the user to get their ID
            $userStmt = $pdo->prepare(
                'SELECT id FROM users WHERE email = :email LIMIT 1'
            );
            $userStmt->execute([':email' => $email]);
            $user = $userStmt->fetch();

            if ($user) {
                // Insert a lockout record (or update if one already exists)
                $lockStmt = $pdo->prepare(
                    'INSERT INTO account_lockouts (id, user_id, locked_at, locked_until, reason)
                          VALUES (:id, :user_id, NOW(), DATE_ADD(NOW(), INTERVAL :minutes MINUTE), :reason)'
                );
                $lockStmt->execute([
                    ':id'      => generateUUID(),
                    ':user_id' => $user['id'],
                    ':minutes' => LOCKOUT_DURATION_MIN,
                    ':reason'  => 'too_many_attempts',
                ]);
            }
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 3.6  Logout
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Revoke a refresh token by marking it as revoked in the database.
     *
     * @param  string $refreshToken  The raw refresh token string.
     * @return bool                  true if a token was revoked, false if not found.
     */
    public static function logout(string $refreshToken): bool
    {
        $tokenHash = hash('sha256', $refreshToken);
        $pdo       = getDB();

        $stmt = $pdo->prepare(
            'UPDATE refresh_tokens
                SET revoked_at = NOW()
              WHERE token_hash = :hash
                AND revoked_at IS NULL'
        );
        $stmt->execute([':hash' => $tokenHash]);

        return $stmt->rowCount() > 0;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Internal helpers
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Insert a row into the login_attempts table.
     *
     * @param  string $email      Email address used in the attempt.
     * @param  string $ipAddress  Client IP address.
     * @param  bool   $success    Whether the attempt was successful.
     * @return void
     */
    private static function recordLoginAttempt(string $email, string $ipAddress, bool $success): void
    {
        try {
            $pdo  = getDB();
            $stmt = $pdo->prepare(
                'INSERT INTO login_attempts (id, email, ip_address, attempted_at, success)
                      VALUES (:id, :email, :ip, NOW(), :success)'
            );
            $stmt->execute([
                ':id'      => generateUUID(),
                ':email'   => strtolower(trim($email)),
                ':ip'      => $ipAddress,
                ':success' => $success ? 1 : 0,
            ]);
        } catch (PDOException $e) {
            // Non-fatal — log and continue
            $logDir = LOG_DIR;
            if (!is_dir($logDir)) {
                @mkdir($logDir, 0755, true);
            }
            @file_put_contents(
                $logDir . 'auth-errors.log',
                sprintf("[%s] Failed to record login attempt: %s\n", date('Y-m-d H:i:s'), $e->getMessage()),
                FILE_APPEND | LOCK_EX
            );
        }
    }
}
