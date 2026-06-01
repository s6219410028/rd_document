<?php
// TEMPORARY - delete this file after use
require_once __DIR__ . '/config.php';

$dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';
$db  = new PDO($dsn, DB_USER, DB_PASS);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$hash = password_hash('admin123', PASSWORD_DEFAULT);

$existing = $db->prepare("SELECT id FROM users WHERE username = 'admin'");
$existing->execute();
$user = $existing->fetch(PDO::FETCH_ASSOC);

if ($user) {
    $db->prepare("UPDATE users SET password_hash = ?, active = 1 WHERE username = 'admin'")
       ->execute([$hash]);
    echo "Reset password for existing admin user. Login: admin / admin123\n";
} else {
    $db->prepare("INSERT INTO users (username, password_hash, display_name, role, active) VALUES ('admin', ?, 'Administrator', 'admin', 1)")
       ->execute([$hash]);
    echo "Created new admin user. Login: admin / admin123\n";
}

echo "Done. DELETE this file from the server now.\n";
