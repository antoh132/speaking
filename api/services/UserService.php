<?php
/**
 * SpeakOn! — UserService
 *
 * Handles user creation, update, deactivation, and retrieval.
 *
 * Requirements covered:
 *   - 3.1 : Super Admin can create user accounts (Dosen and Siswa)
 *   - 3.3 : Super Admin can update user information
 *   - 3.4 : Super Admin can deactivate user accounts
 *   - 3.5 : Deactivated users cannot log in
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/AuthService.php';
require_once __DIR__ . '/LevelService.php';

class UserService
{
    /**
     * Create a new user account.
     *
     * Generates a temporary password, hashes it, and inserts the user.
     * If the role is 'siswa', initialises their level progress (Level 1 unlocked).
     *
     * @param  string $fullName   The user's full name.
     * @param  string $email      The user's email address (must be unique).
     * @param  string $role       'dosen' or 'siswa' (superadmin cannot be created via API).
     * @param  string $createdBy  UUID of the Super Admin creating this account.
     * @return array{user: array, tempPassword: string}
     * @throws RuntimeException on duplicate email or DB error.
     */
    public static function createUser(
        string $fullName,
        string $email,
        string $role,
        string $createdBy
    ): array {
        $pdo   = getDB();
        $email = strtolower(trim($email));

        // Check for duplicate email
        $check = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
        $check->execute([':email' => $email]);
        if ($check->fetch()) {
            throw new RuntimeException(json_encode([
                'code'    => 'EMAIL_ALREADY_EXISTS',
                'message' => 'Email sudah terdaftar. Gunakan email lain.',
                'details' => null,
            ]));
        }

        // Generate a temporary password
        $tempPassword = self::generateTempPassword();
        $passwordHash = AuthService::hashPassword($tempPassword);
        $userId       = generateUUID();

        $stmt = $pdo->prepare(
            'INSERT INTO users (id, full_name, email, password_hash, role, is_active, language_pref, created_at, created_by)
             VALUES (:id, :full_name, :email, :password_hash, :role, 1, :lang, NOW(), :created_by)'
        );
        $stmt->execute([
            ':id'            => $userId,
            ':full_name'     => trim($fullName),
            ':email'         => $email,
            ':password_hash' => $passwordHash,
            ':role'          => $role,
            ':lang'          => 'id',
            ':created_by'    => $createdBy,
        ]);

        // Initialise level progress for new students
        if ($role === 'siswa') {
            LevelService::initializeStudentLevels($userId);
        }

        return [
            'user'         => self::getUserById($userId),
            'tempPassword' => $tempPassword,
        ];
    }

    /**
     * Update a user's profile information.
     *
     * Only the fields provided in $updates will be changed.
     * Allowed fields: full_name, email, language_pref, role.
     *
     * @param  string $userId   UUID of the user to update.
     * @param  array  $updates  Associative array of fields to update.
     * @return array            The updated user object.
     * @throws RuntimeException if user not found or email conflict.
     */
    public static function updateUser(string $userId, array $updates): array
    {
        $pdo = getDB();

        // Verify user exists
        $user = self::getUserById($userId);
        if (!$user) {
            throw new RuntimeException(json_encode([
                'code'    => 'USER_NOT_FOUND',
                'message' => 'Pengguna tidak ditemukan.',
                'details' => null,
            ]));
        }

        $setClauses = [];
        $params     = [':id' => $userId];

        if (isset($updates['full_name'])) {
            $setClauses[]          = 'full_name = :full_name';
            $params[':full_name']  = trim($updates['full_name']);
        }

        if (isset($updates['email'])) {
            $newEmail = strtolower(trim($updates['email']));
            // Check email uniqueness (excluding current user)
            $check = $pdo->prepare('SELECT id FROM users WHERE email = :email AND id != :id LIMIT 1');
            $check->execute([':email' => $newEmail, ':id' => $userId]);
            if ($check->fetch()) {
                throw new RuntimeException(json_encode([
                    'code'    => 'EMAIL_ALREADY_EXISTS',
                    'message' => 'Email sudah digunakan oleh pengguna lain.',
                    'details' => null,
                ]));
            }
            $setClauses[]     = 'email = :email';
            $params[':email'] = $newEmail;
        }

        if (isset($updates['language_pref'])) {
            $setClauses[]          = 'language_pref = :lang';
            $params[':lang']       = $updates['language_pref'];
        }

        if (isset($updates['role'])) {
            $setClauses[]      = 'role = :role';
            $params[':role']   = $updates['role'];
        }

        if (empty($setClauses)) {
            return $user; // Nothing to update
        }

        $sql  = 'UPDATE users SET ' . implode(', ', $setClauses) . ' WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return self::getUserById($userId);
    }

    /**
     * Deactivate a user account.
     *
     * Sets is_active = 0 and revokes all active refresh tokens for the user.
     *
     * @param  string $userId  UUID of the user to deactivate.
     * @return bool            true if the user was deactivated, false if not found.
     */
    public static function deactivateUser(string $userId): bool
    {
        $pdo = getDB();

        // Deactivate the user
        $stmt = $pdo->prepare('UPDATE users SET is_active = 0 WHERE id = :id AND is_active = 1');
        $stmt->execute([':id' => $userId]);

        if ($stmt->rowCount() === 0) {
            return false;
        }

        // Revoke all active refresh tokens for this user
        $revokeStmt = $pdo->prepare(
            'UPDATE refresh_tokens SET revoked_at = NOW() WHERE user_id = :user_id AND revoked_at IS NULL'
        );
        $revokeStmt->execute([':user_id' => $userId]);

        return true;
    }

    /**
     * Get a single user by their UUID.
     *
     * @param  string $userId  The user's UUID.
     * @return array|null      User data array, or null if not found.
     */
    public static function getUserById(string $userId): ?array
    {
        $pdo  = getDB();
        $stmt = $pdo->prepare(
            'SELECT id, full_name, email, role, is_active, language_pref, created_at, created_by
               FROM users
              WHERE id = :id
              LIMIT 1'
        );
        $stmt->execute([':id' => $userId]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    /**
     * Get all users (Super Admin only).
     *
     * @param  array $filters  Optional filters: role, is_active, search (name/email).
     * @param  int   $limit    Maximum number of results (default 100).
     * @param  int   $offset   Pagination offset (default 0).
     * @return array           Array of user objects.
     */
    public static function getAllUsers(array $filters = [], int $limit = 100, int $offset = 0): array
    {
        $pdo    = getDB();
        $where  = ['1=1'];
        $params = [];

        if (isset($filters['role'])) {
            $where[]         = 'role = :role';
            $params[':role'] = $filters['role'];
        }

        if (isset($filters['is_active'])) {
            $where[]              = 'is_active = :is_active';
            $params[':is_active'] = (int)$filters['is_active'];
        }

        if (!empty($filters['search'])) {
            $where[]            = '(full_name LIKE :search OR email LIKE :search)';
            $params[':search']  = '%' . $filters['search'] . '%';
        }

        $sql  = 'SELECT id, full_name, email, role, is_active, language_pref, created_at
                   FROM users
                  WHERE ' . implode(' AND ', $where) . '
                  ORDER BY created_at DESC
                  LIMIT :limit OFFSET :offset';

        $stmt = $pdo->prepare($sql);

        // Bind limit/offset as integers (PDO requires explicit binding for LIMIT/OFFSET)
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Count total users, optionally filtered by role.
     *
     * @param  string|null $role  Optional role filter.
     * @return int                Total count.
     */
    public static function countUsers(?string $role = null): int
    {
        $pdo    = getDB();
        $where  = '1=1';
        $params = [];

        if ($role !== null) {
            $where          = 'role = :role';
            $params[':role'] = $role;
        }

        $stmt = $pdo->prepare("SELECT COUNT(*) AS cnt FROM users WHERE {$where}");
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Internal helpers
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Generate a random temporary password (12 characters).
     *
     * Format: 4 uppercase + 4 digits + 4 lowercase, shuffled.
     *
     * @return string
     */
    private static function generateTempPassword(): string
    {
        $upper   = substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ'), 0, 4);
        $digits  = substr(str_shuffle('23456789'), 0, 4);
        $lower   = substr(str_shuffle('abcdefghjkmnpqrstuvwxyz'), 0, 4);
        $raw     = $upper . $digits . $lower;
        return str_shuffle($raw);
    }
}
