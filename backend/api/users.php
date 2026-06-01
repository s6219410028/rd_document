<?php
try {
    $admin = requireAdmin();
    $db    = getDB();
} catch (Throwable $e) {
    jsonResponse(['error' => 'Init failed: ' . $e->getMessage()], 500);
}

switch ($method) {
    case 'GET':
        try {
            $stmt = $db->query(
                "SELECT id, username, display_name, role, active, created_at FROM users ORDER BY id ASC"
            );
            jsonResponse($stmt->fetchAll());
        } catch (Throwable $e) {
            jsonResponse(['error' => 'Query failed: ' . $e->getMessage()], 500);
        }
        break;

    case 'POST':
        $data = getInput();
        if (empty($data['username']) || empty($data['password']) || empty($data['display_name']) || empty($data['role'])) {
            jsonResponse(['error' => 'กรุณากรอกข้อมูลให้ครบถ้วน'], 400);
        }
        if (!in_array($data['role'], ['admin', 'sender', 'tester'])) {
            jsonResponse(['error' => 'บทบาทไม่ถูกต้อง'], 400);
        }
        try {
            $dup = $db->prepare("SELECT id FROM users WHERE username = ?");
            $dup->execute([$data['username']]);
            if ($dup->fetch()) {
                jsonResponse(['error' => 'ชื่อผู้ใช้นี้มีอยู่แล้ว'], 409);
            }
            $hash = password_hash($data['password'], PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO users (username, password_hash, display_name, role) VALUES (?,?,?,?)");
            $stmt->execute([$data['username'], $hash, $data['display_name'], $data['role']]);
            jsonResponse(['id' => (int)$db->lastInsertId(), 'message' => 'Created'], 201);
        } catch (Throwable $e) {
            jsonResponse(['error' => 'Create failed: ' . $e->getMessage()], 500);
        }
        break;

    case 'PUT':
        if ($id === null) jsonResponse(['error' => 'ID required'], 400);
        $data = getInput();
        if (empty($data['username']) || empty($data['display_name']) || empty($data['role'])) {
            jsonResponse(['error' => 'กรุณากรอกข้อมูลให้ครบถ้วน'], 400);
        }
        if (!in_array($data['role'], ['admin', 'sender', 'tester'])) {
            jsonResponse(['error' => 'บทบาทไม่ถูกต้อง'], 400);
        }
        try {
            $dup = $db->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
            $dup->execute([$data['username'], $id]);
            if ($dup->fetch()) {
                jsonResponse(['error' => 'ชื่อผู้ใช้นี้มีอยู่แล้ว'], 409);
            }
            $active = isset($data['active']) ? (int)(bool)$data['active'] : 1;
            if (!empty($data['password'])) {
                $hash = password_hash($data['password'], PASSWORD_DEFAULT);
                $db->prepare(
                    "UPDATE users SET username=?, password_hash=?, display_name=?, role=?, active=?, updated_at=CURRENT_TIMESTAMP WHERE id=?"
                )->execute([$data['username'], $hash, $data['display_name'], $data['role'], $active, $id]);
            } else {
                $db->prepare(
                    "UPDATE users SET username=?, display_name=?, role=?, active=?, updated_at=CURRENT_TIMESTAMP WHERE id=?"
                )->execute([$data['username'], $data['display_name'], $data['role'], $active, $id]);
            }
            jsonResponse(['message' => 'Updated']);
        } catch (Throwable $e) {
            jsonResponse(['error' => 'Update failed: ' . $e->getMessage()], 500);
        }
        break;

    case 'DELETE':
        if ($id === null) jsonResponse(['error' => 'ID required'], 400);
        if ((int)$id === (int)$admin['id']) {
            jsonResponse(['error' => 'ไม่สามารถลบบัญชีของตัวเองได้'], 400);
        }
        try {
            $db->prepare("DELETE FROM users WHERE id=?")->execute([$id]);
            jsonResponse(['message' => 'Deleted']);
        } catch (Throwable $e) {
            jsonResponse(['error' => 'Delete failed: ' . $e->getMessage()], 500);
        }
        break;

    default:
        jsonResponse(['error' => 'Method not allowed'], 405);
}
