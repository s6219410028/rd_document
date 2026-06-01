<?php
try {
    $db = getDB();
} catch (Throwable $e) {
    jsonResponse(['error' => 'DB connect failed: ' . $e->getMessage()], 500);
}

$uploadDir = __DIR__ . '/../uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

switch ($method) {
    case 'GET':
        $formType = $_GET['form_type'] ?? null;
        $formId   = $_GET['form_id']   ?? null;
        if (!$formType || !$formId) jsonResponse(['error' => 'Missing form_type or form_id'], 400);
        $stmt = $db->prepare("SELECT * FROM uploads WHERE form_type = ? AND form_id = ? ORDER BY created_at");
        $stmt->execute([$formType, (int)$formId]);
        jsonResponse($stmt->fetchAll());
        break;

    case 'POST':
        $formType   = $_POST['form_type']   ?? null;
        $formId     = $_POST['form_id']     ?? null;
        $paramKey   = $_POST['param_key']   ?? null;
        $paramLabel = $_POST['param_label'] ?? '';

        if (!$formType || !$formId || !$paramKey || empty($_FILES['file'])) {
            jsonResponse(['error' => 'Missing required fields or file'], 400);
        }
        $file = $_FILES['file'];
        if ($file['error'] !== UPLOAD_ERR_OK) jsonResponse(['error' => 'File upload error code: ' . $file['error']], 400);

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['pdf', 'jpg', 'jpeg'])) jsonResponse(['error' => 'Only PDF or JPEG files are allowed'], 400);

        try {
            $stmt = $db->prepare("SELECT filename FROM uploads WHERE form_type=? AND form_id=? AND param_key=?");
            $stmt->execute([$formType, (int)$formId, $paramKey]);
            $existing = $stmt->fetch();
            if ($existing) {
                @unlink($uploadDir . $existing['filename']);
                $db->prepare("DELETE FROM uploads WHERE form_type=? AND form_id=? AND param_key=?")->execute([$formType, (int)$formId, $paramKey]);
            }
        } catch (Throwable $e) {
            jsonResponse(['error' => 'DB query failed: ' . $e->getMessage()], 500);
        }

        $filename = uniqid("{$formType}_{$formId}_{$paramKey}_") . '.' . $ext;
        if (!move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
            jsonResponse(['error' => 'move_uploaded_file failed. uploadDir: ' . $uploadDir . ' writable: ' . (is_writable($uploadDir) ? 'yes' : 'no')], 500);
        }

        try {
            $stmt = $db->prepare("INSERT INTO uploads (form_type, form_id, param_key, param_label, filename, original_name) VALUES (?,?,?,?,?,?)");
            $stmt->execute([$formType, (int)$formId, $paramKey, $paramLabel, $filename, $file['name']]);
            jsonResponse(['id' => $db->lastInsertId(), 'filename' => $filename, 'original_name' => $file['name']], 201);
        } catch (Throwable $e) {
            jsonResponse(['error' => 'DB insert failed: ' . $e->getMessage()], 500);
        }
        break;

    case 'DELETE':
        if ($id === null) jsonResponse(['error' => 'ID required'], 400);
        try {
            $stmt = $db->prepare("SELECT filename FROM uploads WHERE id = ?");
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            if ($row) {
                @unlink($uploadDir . $row['filename']);
                $db->prepare("DELETE FROM uploads WHERE id = ?")->execute([$id]);
            }
            jsonResponse(['message' => 'Deleted']);
        } catch (Throwable $e) {
            jsonResponse(['error' => 'Delete failed: ' . $e->getMessage()], 500);
        }
        break;

    default:
        jsonResponse(['error' => 'Method not allowed'], 405);
}
