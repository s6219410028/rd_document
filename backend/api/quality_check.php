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
            jsonResponse($row);
        } else {
            $stmt = $db->query("SELECT id, JSON_UNQUOTE(JSON_EXTRACT(form_data,'$.analysis_number')) as analysis_number, JSON_UNQUOTE(JSON_EXTRACT(form_data,'$.product_name')) as product_name, JSON_UNQUOTE(JSON_EXTRACT(form_data,'$.lot_no')) as lot_no, JSON_UNQUOTE(JSON_EXTRACT(form_data,'$.send_date')) as send_date, JSON_UNQUOTE(JSON_EXTRACT(form_data,'$.result')) as result, JSON_UNQUOTE(JSON_EXTRACT(form_data,'$.requester')) as requester, JSON_EXTRACT(form_data,'$.is_raw_material') as is_raw_material, JSON_EXTRACT(form_data,'$.is_pharmaceutical') as is_pharmaceutical, JSON_EXTRACT(form_data,'$.params') as params, JSON_EXTRACT(form_data,'$.custom_params') as custom_params, (SELECT COUNT(*) FROM uploads WHERE form_type='quality_check' AND form_id=quality_check_forms.id) as upload_count, created_at FROM quality_check_forms ORDER BY created_at DESC");
            jsonResponse($stmt->fetchAll());
        }
        break;

    case 'POST':
        $data = getInput();
        if (empty($data)) jsonResponse(['error' => 'No data'], 400);
        $stmt = $db->prepare("INSERT INTO quality_check_forms (form_data) VALUES (?)");
        $stmt->execute([json_encode($data, JSON_UNESCAPED_UNICODE)]);
        jsonResponse(['id' => $db->lastInsertId(), 'message' => 'Saved'], 201);
        break;

    case 'PUT':
        if ($id === null) jsonResponse(['error' => 'ID required'], 400);
        $data = getInput();
        $stmt = $db->prepare("UPDATE quality_check_forms SET form_data=?, updated_at=CURRENT_TIMESTAMP WHERE id=?");
        $stmt->execute([json_encode($data, JSON_UNESCAPED_UNICODE), $id]);
        jsonResponse(['message' => 'Updated']);
        break;

    case 'DELETE':
        if ($id === null) jsonResponse(['error' => 'ID required'], 400);
        $db->prepare("DELETE FROM quality_check_forms WHERE id=?")->execute([$id]);
        jsonResponse(['message' => 'Deleted']);
        break;

    default:
        jsonResponse(['error' => 'Method not allowed'], 405);
}
