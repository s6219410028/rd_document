<?php
$db = getDB();

switch ($method) {
    case 'POST':
        if ($action === 'login') {
            $data = getInput();
            if (empty($data['username']) || empty($data['password'])) {
                jsonResponse(['error' => 'กรุณากรอกชื่อผู้ใช้และรหัสผ่าน'], 400);
            }
            $stmt = $db->prepare("SELECT * FROM users WHERE username = ? AND active = 1");
            $stmt->execute([trim($data['username'])]);
            $user = $stmt->fetch();
            if (!$user || !password_verify($data['password'], $user['password_hash'])) {
                jsonResponse(['error' => 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง'], 401);
            }
            $token = bin2hex(random_bytes(32));
            $db->prepare("INSERT INTO user_tokens (user_id, token) VALUES (?, ?)")
               ->execute([$user['id'], $token]);
            jsonResponse([
                'token' => $token,
                'user' => [
                    'id'           => (int)$user['id'],
                    'username'     => $user['username'],
                    'display_name' => $user['display_name'],
                    'role'         => $user['role'],
                ],
            ]);
        }
        if ($action === 'logout') {
            $headers = getallheaders();
            $auth = $headers['Authorization'] ?? $headers['authorization'] ?? '';
            if (str_starts_with($auth, 'Bearer ')) {
                $token = substr($auth, 7);
                $db->prepare("DELETE FROM user_tokens WHERE token = ?")->execute([$token]);
            }
            jsonResponse(['message' => 'Logged out']);
        }
        jsonResponse(['error' => 'Unknown action'], 404);
        break;

    case 'GET':
        if ($action === 'me') {
            jsonResponse(requireAuth());
        }
        jsonResponse(['error' => 'Not found'], 404);
        break;

    default:
        jsonResponse(['error' => 'Method not allowed'], 405);
}
