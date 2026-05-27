<?php
$db = getDB();
$uploadDir = __DIR__ . '/../uploads/';

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
        if ($file['error'] !== UPLOAD_ERR_OK) jsonResponse(['error' => 'File upload error: ' . $file['error']], 400);

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['pdf', 'jpg', 'jpeg'])) jsonResponse(['error' => 'Only PDF or JPEG files are allowed'], 400);

        // Replace existing upload for same form + param
        $stmt = $db->prepare("SELECT filename FROM uploads WHERE form_type=? AND form_id=? AND param_key=?");
        $stmt->execute([$formType, (int)$formId, $paramKey]);
        $existing = $stmt->fetch();
        if ($existing) {
            @unlink($uploadDir . $existing['filename']);
            $db->prepare("DELETE FROM uploads WHERE form_type=? AND form_id=? AND param_key=?")->execute([$formType, (int)$formId, $paramKey]);
        }

        $filename = uniqid("{$formType}_{$formId}_{$paramKey}_") . '.' . $ext;
        if (!move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
            jsonResponse(['error' => 'Failed to save file'], 500);
        }

        $stmt = $db->prepare("INSERT INTO uploads (form_type, form_id, param_key, param_label, filename, original_name) VALUES (?,?,?,?,?,?)");
        $stmt->execute([$formType, (int)$formId, $paramKey, $paramLabel, $filename, $file['name']]);
        jsonResponse(['id' => $db->lastInsertId(), 'filename' => $filename, 'original_name' => $file['name']], 201);
        break;

    case 'DELETE':
        if ($id === null) jsonResponse(['error' => 'ID required'], 400);
        $stmt = $db->prepare("SELECT filename FROM uploads WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row) {
            @unlink($uploadDir . $row['filename']);
            $db->prepare("DELETE FROM uploads WHERE id = ?")->execute([$id]);
        }
        jsonResponse(['message' => 'Deleted']);
        break;

    default:
        jsonResponse(['error' => 'Method not allowed'], 405);
}
