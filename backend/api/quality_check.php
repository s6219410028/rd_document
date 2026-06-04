<?php
$db = getDB();

switch ($method) {
    case 'GET':
        if ($id !== null) {
            $stmt = $db->prepare("SELECT * FROM quality_check_forms WHERE id = ?");
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            if (!$row) jsonResponse(['error' => 'Not found'], 404);
            $row['form_data'] = json_decode($row['form_data'], true);
            $curStatus = $row['form_data']['status'] ?? 'pending';
            $logStmt = $db->prepare("SELECT changed_at FROM status_log WHERE form_type='quality_check' AND form_id=? AND status=? ORDER BY changed_at DESC LIMIT 1");
            $logStmt->execute([$id, $curStatus]);
            $row['status_changed_at'] = $logStmt->fetchColumn() ?: $row['created_at'];
            jsonResponse($row);
        } else {
            $stmt = $db->query("SELECT id, JSON_UNQUOTE(JSON_EXTRACT(form_data,'$.analysis_number')) as analysis_number, JSON_UNQUOTE(JSON_EXTRACT(form_data,'$.product_name')) as product_name, JSON_UNQUOTE(JSON_EXTRACT(form_data,'$.lot_no')) as lot_no, JSON_UNQUOTE(JSON_EXTRACT(form_data,'$.send_date')) as send_date, JSON_UNQUOTE(JSON_EXTRACT(form_data,'$.result')) as result, JSON_UNQUOTE(JSON_EXTRACT(form_data,'$.requester')) as requester, JSON_UNQUOTE(JSON_EXTRACT(form_data,'$.qc_type')) as qc_type, JSON_UNQUOTE(JSON_EXTRACT(form_data,'$.urgency_level')) as urgency_level, IF(JSON_EXTRACT(form_data,'$.locked') = true, 1, 0) as locked, JSON_EXTRACT(form_data,'$.is_raw_material') as is_raw_material, JSON_EXTRACT(form_data,'$.is_pharmaceutical') as is_pharmaceutical, JSON_EXTRACT(form_data,'$.params') as params, JSON_EXTRACT(form_data,'$.custom_params') as custom_params, (SELECT COUNT(*) FROM uploads WHERE form_type='quality_check' AND form_id=quality_check_forms.id) as upload_count, COALESCE(JSON_UNQUOTE(JSON_EXTRACT(form_data,'$.status')), 'pending') as status, created_at, COALESCE((SELECT changed_at FROM status_log WHERE form_type='quality_check' AND form_id=quality_check_forms.id AND status=COALESCE(JSON_UNQUOTE(JSON_EXTRACT(form_data,'$.status')),'pending') ORDER BY changed_at DESC LIMIT 1), created_at) as status_changed_at FROM quality_check_forms ORDER BY created_at DESC");
            jsonResponse($stmt->fetchAll());
        }
        break;

    case 'POST':
        $data = getInput();
        if (empty($data)) jsonResponse(['error' => 'No data'], 400);
        $stmt = $db->prepare("INSERT INTO quality_check_forms (form_data) VALUES (?)");
        $stmt->execute([json_encode($data, JSON_UNESCAPED_UNICODE)]);
        $newId = $db->lastInsertId();
        $db->prepare("INSERT INTO status_log (form_type, form_id, status) VALUES ('quality_check', ?, 'pending')")->execute([$newId]);
        jsonResponse(['id' => $newId, 'message' => 'Saved'], 201);
        break;

    case 'PUT':
        if ($id === null) jsonResponse(['error' => 'ID required'], 400);
        $data = getInput();
        $stmt = $db->prepare("UPDATE quality_check_forms SET form_data=?, updated_at=CURRENT_TIMESTAMP WHERE id=?");
        $stmt->execute([json_encode($data, JSON_UNESCAPED_UNICODE), $id]);
        jsonResponse(['message' => 'Updated']);
        break;

    case 'PATCH':
        if ($id === null) jsonResponse(['error' => 'ID required'], 400);
        $patch = getInput();
        if (array_key_exists('locked', $patch)) {
            $locked = $patch['locked'] ? 'true' : 'false';
            $db->prepare("UPDATE quality_check_forms SET form_data = JSON_SET(form_data, '$.locked', CAST(? AS JSON)), updated_at=CURRENT_TIMESTAMP WHERE id=?")->execute([$locked, $id]);
            jsonResponse(['message' => 'Updated']);
        }
        if (array_key_exists('status', $patch)) {
            $valid = ['pending', 'in_progress', 'pending_rd', 'complete'];
            if (!in_array($patch['status'], $valid)) jsonResponse(['error' => 'Invalid status'], 400);
            $db->prepare("UPDATE quality_check_forms SET form_data = JSON_SET(form_data, '$.status', ?), updated_at=CURRENT_TIMESTAMP WHERE id=?")->execute([$patch['status'], $id]);
            $db->prepare("INSERT INTO status_log (form_type, form_id, status) VALUES ('quality_check', ?, ?)")->execute([$id, $patch['status']]);
            jsonResponse(['message' => 'Updated']);
        }
        jsonResponse(['error' => 'Invalid patch'], 400);
        break;

    case 'DELETE':
        if ($id === null) jsonResponse(['error' => 'ID required'], 400);
        $db->prepare("DELETE FROM quality_check_forms WHERE id=?")->execute([$id]);
        jsonResponse(['message' => 'Deleted']);
        break;

    default:
        jsonResponse(['error' => 'Method not allowed'], 405);
}
