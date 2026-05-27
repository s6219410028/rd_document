<?php
$db = getDB();

if ($method !== 'GET') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

$year  = isset($_GET['year'])  ? $_GET['year']  : date('y');
$month = isset($_GET['month']) ? $_GET['month'] : date('m');

// Find highest sequence for the given year+month combination across both form tables.
$stmt = $db->prepare("
    SELECT MAX(CAST(
        SUBSTRING(an, LOCATE('-', an) + 1, LOCATE('/', an) - LOCATE('-', an) - 1)
    AS UNSIGNED)) AS max_seq
    FROM (
        SELECT JSON_UNQUOTE(JSON_EXTRACT(form_data,'$.analysis_number')) AS an FROM quality_check_forms
        UNION ALL
        SELECT JSON_UNQUOTE(JSON_EXTRACT(form_data,'$.analysis_number')) AS an FROM dissolution_forms
    ) sub
    WHERE an LIKE ? AND an LIKE ?
");
$stmt->execute(["RD{$year}-%", "%/{$month}"]);
$row    = $stmt->fetch();
$maxSeq = (int)($row['max_seq'] ?? 0);
$next   = $maxSeq + 1;

jsonResponse([
    'year'       => $year,
    'month'      => $month,
    'next_seq'   => $next,
    'run_number' => sprintf('RD%s-%03d/%s', $year, $next, $month),
]);
