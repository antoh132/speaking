<?php
/**
 * SpeakOn! — Validator Helper
 *
 * Provides static helpers for validating and sanitising request input.
 * All methods return the sanitised value on success or throw a
 * RuntimeException with a structured JSON error payload on failure.
 */

class Validator
{
    // ──────────────────────────────────────────────────────────────────────────
    // Required field helpers
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Assert that a value is present and non-empty after trimming.
     *
     * @param  mixed  $value      The value to check.
     * @param  string $fieldName  Human-readable field name for error messages.
     * @return string             The trimmed string value.
     * @throws InvalidArgumentException
     */
    public static function required(mixed $value, string $fieldName): string
    {
        if ($value === null || trim((string)$value) === '') {
            throw new InvalidArgumentException("Field '{$fieldName}' wajib diisi.");
        }
        return trim((string)$value);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Type-specific validators
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Validate and normalise an email address.
     *
     * @param  mixed  $value      The raw email value.
     * @param  string $fieldName  Field name for error messages.
     * @return string             Lowercase, trimmed, validated email.
     * @throws InvalidArgumentException
     */
    public static function email(mixed $value, string $fieldName = 'email'): string
    {
        $email = strtolower(trim((string)$value));

        if ($email === '') {
            throw new InvalidArgumentException("Field '{$fieldName}' wajib diisi.");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Format email '{$fieldName}' tidak valid.");
        }

        return $email;
    }

    /**
     * Validate a password meets minimum length requirements.
     *
     * @param  mixed  $value      The raw password value.
     * @param  int    $minLength  Minimum character length (default 8).
     * @param  string $fieldName  Field name for error messages.
     * @return string             The password string (not trimmed — spaces are valid).
     * @throws InvalidArgumentException
     */
    public static function password(mixed $value, int $minLength = 8, string $fieldName = 'password'): string
    {
        $password = (string)$value;

        if ($password === '') {
            throw new InvalidArgumentException("Field '{$fieldName}' wajib diisi.");
        }

        if (mb_strlen($password) < $minLength) {
            throw new InvalidArgumentException(
                "Password minimal {$minLength} karakter."
            );
        }

        return $password;
    }

    /**
     * Validate that a value is one of the allowed enum values.
     *
     * @param  mixed    $value      The raw value.
     * @param  array    $allowed    Array of allowed string values.
     * @param  string   $fieldName  Field name for error messages.
     * @return string               The validated value.
     * @throws InvalidArgumentException
     */
    public static function enum(mixed $value, array $allowed, string $fieldName): string
    {
        $val = trim((string)$value);

        if (!in_array($val, $allowed, true)) {
            $allowedStr = implode(', ', array_map(fn($v) => "'{$v}'", $allowed));
            throw new InvalidArgumentException(
                "Nilai '{$fieldName}' tidak valid. Nilai yang diizinkan: {$allowedStr}."
            );
        }

        return $val;
    }

    /**
     * Validate a string has a minimum length.
     *
     * @param  mixed  $value      The raw value.
     * @param  int    $minLength  Minimum character length.
     * @param  string $fieldName  Field name for error messages.
     * @return string             The trimmed string.
     * @throws InvalidArgumentException
     */
    public static function minLength(mixed $value, int $minLength, string $fieldName): string
    {
        $str = trim((string)$value);

        if (mb_strlen($str) < $minLength) {
            throw new InvalidArgumentException(
                "Field '{$fieldName}' minimal {$minLength} karakter."
            );
        }

        return $str;
    }

    /**
     * Validate a string does not exceed a maximum length.
     *
     * @param  mixed  $value      The raw value.
     * @param  int    $maxLength  Maximum character length.
     * @param  string $fieldName  Field name for error messages.
     * @return string             The trimmed string.
     * @throws InvalidArgumentException
     */
    public static function maxLength(mixed $value, int $maxLength, string $fieldName): string
    {
        $str = trim((string)$value);

        if (mb_strlen($str) > $maxLength) {
            throw new InvalidArgumentException(
                "Field '{$fieldName}' maksimal {$maxLength} karakter."
            );
        }

        return $str;
    }

    /**
     * Validate that a value is a valid UUID v4 string.
     *
     * @param  mixed  $value      The raw value.
     * @param  string $fieldName  Field name for error messages.
     * @return string             The validated UUID string.
     * @throws InvalidArgumentException
     */
    public static function uuid(mixed $value, string $fieldName): string
    {
        $uuid = trim((string)$value);
        $pattern = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';

        if (!preg_match($pattern, $uuid)) {
            throw new InvalidArgumentException("Format '{$fieldName}' tidak valid (harus UUID v4).");
        }

        return strtolower($uuid);
    }

    /**
     * Validate that a value is a positive integer.
     *
     * @param  mixed  $value      The raw value.
     * @param  string $fieldName  Field name for error messages.
     * @return int                The validated integer.
     * @throws InvalidArgumentException
     */
    public static function positiveInt(mixed $value, string $fieldName): int
    {
        if (!is_numeric($value) || (int)$value <= 0) {
            throw new InvalidArgumentException("Field '{$fieldName}' harus berupa bilangan bulat positif.");
        }

        return (int)$value;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Request body helpers
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Parse and return the JSON request body as an associative array.
     * Returns an empty array if the body is empty or not valid JSON.
     *
     * @return array<string, mixed>
     */
    public static function parseJsonBody(): array
    {
        $raw = file_get_contents('php://input');

        if (empty($raw)) {
            return [];
        }

        $decoded = json_decode($raw, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException('Request body bukan JSON yang valid.');
        }

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Get a value from an array by key, returning null if not set.
     *
     * @param  array  $data  The source array (e.g. parsed JSON body).
     * @param  string $key   The key to retrieve.
     * @return mixed|null
     */
    public static function get(array $data, string $key): mixed
    {
        return $data[$key] ?? null;
    }

    /**
     * Sanitise a string for safe output (strip tags, trim whitespace).
     *
     * @param  mixed  $value  The raw value.
     * @return string         The sanitised string.
     */
    public static function sanitizeString(mixed $value): string
    {
        return trim(strip_tags((string)$value));
    }
}
