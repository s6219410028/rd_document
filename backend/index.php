<?php
require_once __DIR__ . '/database.php';

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = rtrim($uri, '/');
$method = $_SERVER['REQUEST_METHOD'];

// Extract base path after /api/
if (preg_match('#/api/(stability|dissolution|quality_check)(?:/(\d+))?#', $uri, $m)) {
    $resource = $m[1];
    $id = isset($m[2]) ? (int)$m[2] : null;
    require __DIR__ . "/api/{$resource}.php";
} else {
    jsonResponse(['error' => 'Not found'], 404);
}
