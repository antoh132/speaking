<?php
/**
 * SpeakOn! — CORS Handler
 *
 * Sets the appropriate CORS headers for all API responses and handles
 * preflight OPTIONS requests.
 *
 * Requirements covered:
 *   - 11.5 : Allow cross-origin requests from the frontend origin
 */

require_once __DIR__ . '/config.php';

/**
 * Apply CORS headers and handle preflight OPTIONS requests.
 *
 * Call this at the very top of every API entry point, before any output.
 *
 * @return void  Terminates execution for OPTIONS preflight requests.
 */
function handleCors(): void
{
    // Allowed origins — in development allow localhost; in production restrict to your domain
    $allowedOrigins = [
        'http://localhost',
        'http://localhost:80',
        'http://127.0.0.1',
        'http://localhost/speakon',
        // InfinityFree domains — tambah domain kamu di sini setelah dapat dari InfinityFree
        'https://speakon.ct.ws',
        'https://speakon.rf.gd',
        'http://speakon.ct.ws',
        'http://speakon.rf.gd',
    ];

    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

    if (in_array($origin, $allowedOrigins, true)) {
        header('Access-Control-Allow-Origin: ' . $origin);
    } elseif (APP_ENV === 'development') {
        // In development, allow any localhost origin
        if (str_starts_with($origin, 'http://localhost') || str_starts_with($origin, 'http://127.0.0.1')) {
            header('Access-Control-Allow-Origin: ' . $origin);
        }
    }

    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    header('Access-Control-Max-Age: 86400'); // Cache preflight for 24 hours

    // Handle preflight OPTIONS request — respond immediately with 204 No Content
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204);
        exit;
    }
}
