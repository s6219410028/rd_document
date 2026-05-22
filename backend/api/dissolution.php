<?php
$db = getDB();

switch ($method) {
    case 'GET':
        if ($id !== null) {
            $stmt = $db->prepare("SELECT * FROM dissolution_forms WHERE id = ?");
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            if (!$row) jsonResponse(['error' => 'Not found'], 404);
            $row['form_data'] = json_decode($row['form_data'], true);
            jsonResponse($row);
        } else {
            $stmt = $db->query("SELECT id, JSON_UNQUOTE(JSON_EXTRACT(form_data,'$.analysis_number')) as analysis_number, JSON_UNQUOTE(JSON_EXTRACT(form_data,'$.our_products[0].product_name')) as product_name, JSON_UNQUOTE(JSON_EXTRACT(form_data,'$.our_products[0].lot_no')) as lot_no, JSON_UNQUOTE(JSON_EXTRACT(form_data,'$.send_date')) as send_date, JSON_UNQUOTE(JSON_EXTRACT(form_data,'$.f2_result')) as f2_result, JSON_UNQUOTE(JSON_EXTRACT(form_data,'$.sender')) as sender, JSON_UNQUOTE(JSON_EXTRACT(form_data,'$.observed_by')) as observed_by, COALESCE(JSON_LENGTH(JSON_EXTRACT(form_data,'$.our_products')),1) as our_products_count, COALESCE(JSON_LENGTH(JSON_EXTRACT(form_data,'$.original_products')),1) as original_products_count, created_at FROM dissolution_forms ORDER BY created_at DESC");
            jsonResponse($stmt->fetchAll());
        }
        break;

    case 'POST':
        $data = getInput();
        if (empty($data)) jsonResponse(['error' => 'No data'], 400);
        $stmt = $db->prepare("INSERT INTO dissolution_forms (form_data) VALUES (?)");
        $stmt->execute([json_encode($data, JSON_UNESCAPED_UNICODE)]);
        jsonResponse(['id' => $db->lastInsertId(), 'message' => 'Saved'], 201);
        break;

    case 'PUT':
        if ($id === null) jsonResponse(['error' => 'ID required'], 400);
        $data = getInput();
        $stmt = $db->prepare("UPDATE dissolution_forms SET form_data=?, updated_at=CURRENT_TIMESTAMP WHERE id=?");
        $stmt->execute([json_encode($data, JSON_UNESCAPED_UNICODE), $id]);
        jsonResponse(['message' => 'Updated']);
        break;

    case 'DELETE':
        if ($id === null) jsonResponse(['error' => 'ID required'], 400);
        $db->prepare("DELETE FROM dissolution_forms WHERE id=?")->execute([$id]);
        jsonResponse(['message' => 'Deleted']);
        break;

    default:
        jsonResponse(['error' => 'Method not allowed'], 405);
}
