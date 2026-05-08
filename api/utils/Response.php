<?php
/**
 * SpeakOn! — Response Helper
 *
 * Provides static helpers for sending consistent JSON responses.
 * All responses set Content-Type: application/json.
 *
 * Success format:
 *   { "data": <payload>, "error": null }
 *
 * Error format:
 *   { "data": null, "error": { "code": "...", "message": "...", "details": ... } }
 */

class Response
{
    /**
     * Send a successful JSON response and terminate execution.
     *
     * @param  mixed $data        The response payload (array, object, or scalar).
     * @param  int   $statusCode  HTTP status code (default 200).
     * @return never
     */
    public static function success(mixed $data, int $statusCode = 200): never
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');

        echo json_encode(
            ['data' => $data, 'error' => null],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
        exit;
    }

    /**
     * Send an error JSON response and terminate execution.
     *
     * @param  int         $statusCode  HTTP status code (e.g. 400, 401, 403, 404, 500).
     * @param  string      $errorCode   Machine-readable error code (e.g. 'INVALID_CREDENTIALS').
     * @param  string      $message     Human-readable error message.
     * @param  mixed|null  $details     Optional additional details (array or null).
     * @return never
     */
    public static function error(
        int    $statusCode,
        string $errorCode,
        string $message,
        mixed  $details = null
    ): never {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');

        echo json_encode(
            [
                'data'  => null,
                'error' => [
                    'code'    => $errorCode,
                    'message' => $message,
                    'details' => $details,
                ],
            ],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
        exit;
    }

    /**
     * Send a 400 Bad Request / validation error response.
     *
     * @param  string     $message  Human-readable validation message.
     * @param  mixed|null $details  Optional field-level validation details.
     * @return never
     */
    public static function validationError(string $message, mixed $details = null): never
    {
        self::error(400, 'VALIDATION_ERROR', $message, $details);
    }

    /**
     * Send a 401 Unauthorized response for invalid or expired tokens.
     *
     * @param  string $code     Error code: 'TOKEN_EXPIRED' or 'TOKEN_INVALID'.
     * @param  string $message  Human-readable message.
     * @return never
     */
    public static function unauthorized(
        string $code    = 'TOKEN_INVALID',
        string $message = 'Autentikasi diperlukan.'
    ): never {
        self::error(401, $code, $message);
    }

    /**
     * Send a 403 Forbidden response.
     *
     * @param  string $code     Error code (e.g. 'FORBIDDEN', 'ACCOUNT_LOCKED').
     * @param  string $message  Human-readable message.
     * @return never
     */
    public static function forbidden(
        string $code    = 'FORBIDDEN',
        string $message = 'Anda tidak memiliki izin untuk mengakses resource ini.'
    ): never {
        self::error(403, $code, $message);
    }

    /**
     * Send a 404 Not Found response.
     *
     * @param  string $message  Human-readable message.
     * @return never
     */
    public static function notFound(string $message = 'Resource tidak ditemukan.'): never
    {
        self::error(404, 'NOT_FOUND', $message);
    }

    /**
     * Send a 409 Conflict response (e.g. duplicate email).
     *
     * @param  string $code     Error code (e.g. 'EMAIL_ALREADY_EXISTS').
     * @param  string $message  Human-readable message.
     * @return never
     */
    public static function conflict(string $code, string $message): never
    {
        self::error(409, $code, $message);
    }

    /**
     * Send a 500 Internal Server Error response.
     *
     * @param  string $message  Human-readable message (do not expose internal details).
     * @return never
     */
    public static function serverError(string $message = 'Terjadi kesalahan pada server.'): never
    {
        self::error(500, 'INTERNAL_ERROR', $message);
    }
}
