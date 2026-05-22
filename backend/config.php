<?php
define('DB_HOST', 'localhost');
define('DB_PORT', '3308');
define('DB_NAME', 'rd_document');
define('DB_USER', 'root');
define('DB_PASS', '');

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}
