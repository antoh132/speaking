<?php
/**
 * SpeakOn! — API Router
 *
 * Single entry point for all API requests.
 * Routes requests to the appropriate route file based on the URL path.
 *
 * URL pattern: /speakon/api/{resource}/{...}
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/cors.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/utils/Response.php';

// Handle CORS preflight
handleCors();

// Parse the request URI
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Strip the base path (e.g. /speakon/api) to get the resource segment
$uri = preg_replace('#^.*?/api#', '', $uri);

// Extract the first path segment as the resource name
$segments = explode('/', trim($uri, '/'));
$resource = $segments[0] ?? '';

// Route to the appropriate handler
$routeFile = __DIR__ . '/routes/' . $resource . '.php';

if (file_exists($routeFile)) {
    require_once $routeFile;
} else {
    // Health check endpoint
    if ($resource === 'health') {
        require_once __DIR__ . '/config/db.php';
        $healthy = checkDBHealth();
        Response::success([
            'status'  => $healthy ? 'ok' : 'degraded',
            'db'      => $healthy ? 'connected' : 'unavailable',
            'version' => '1.0.0',
            'time'    => date('Y-m-d H:i:s'),
        ]);
    }

    Response::notFound("Endpoint '/{$resource}' tidak ditemukan.");
}
