<?php
require_once __DIR__ . '/database.php';

$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri    = rtrim($uri, '/');
$method = $_SERVER['REQUEST_METHOD'];
// Method tunneling for IIS hosts that block PUT/PATCH/DELETE
if ($method === 'POST') {
    $override = $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] ?? '';
    if (in_array(strtoupper($override), ['PUT', 'PATCH', 'DELETE'])) {
        $method = strtoupper($override);
    }
}

// Extract base path after /api/
if (preg_match('#/api/(next_run_number)$#', $uri, $m)) {
    $resource = $m[1];
    $id = null;
    require __DIR__ . "/api/{$resource}.php";
} elseif (preg_match('#/api/upload(?:/(\d+))?$#', $uri, $m)) {
    $id = isset($m[1]) ? (int)$m[1] : null;
    require __DIR__ . '/api/upload.php';
} elseif (preg_match('#/api/auth/(\w+)$#', $uri, $m)) {
    $action = $m[1];
    require __DIR__ . '/api/auth.php';
} elseif (preg_match('#/api/users(?:/(\d+))?$#', $uri, $m)) {
    $id = isset($m[1]) ? (int)$m[1] : null;
    require __DIR__ . '/api/users.php';
} elseif (preg_match('#/api/(stability|dissolution|quality_check)(?:/(\d+))?#', $uri, $m)) {
    $resource = $m[1];
    $id = isset($m[2]) ? (int)$m[2] : null;
    require __DIR__ . "/api/{$resource}.php";
} else {
    jsonResponse(['error' => 'Not found'], 404);
}
