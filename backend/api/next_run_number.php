<?php
$db = getDB();

if ($method !== 'GET') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

$year  = date('y');   // 2-digit current year
$month = date('m');   // 2-digit current month

// Find highest sequence number across BOTH form types for the current year.
// analysis_number format: RD{YY}-{XX}/{MM}  — extract XX between '-' and '/'.
$stmt = $db->prepare("
    SELECT MAX(CAST(
        SUBSTRING(an, LOCATE('-', an) + 1, LOCATE('/', an) - LOCATE('-', an) - 1)
    AS UNSIGNED)) AS max_seq
    FROM (
        SELECT JSON_UNQUOTE(JSON_EXTRACT(form_data,'$.analysis_number')) AS an FROM quality_check_forms
        UNION ALL
        SELECT JSON_UNQUOTE(JSON_EXTRACT(form_data,'$.analysis_number')) AS an FROM dissolution_forms
    ) sub
    WHERE an LIKE ? AND an LIKE '%/%'
");
$stmt->execute(["RD{$year}-%"]);
$row    = $stmt->fetch();
$maxSeq = $row['max_seq'] ?? 0;
$next   = (int)$maxSeq + 1;

jsonResponse([
    'year'       => $year,
    'month'      => $month,
    'next_seq'   => $next,
    'run_number' => sprintf('RD%s-%02d/%s', $year, $next, $month),
]);
