<?php

$companyId = isset($_GET['company_id']) ? $_GET['company_id'] : null;

if (!$companyId) {
    echo json_encode(array());
    die();
}
$periodEnding = isset($_GET['period_ending']) ? $_GET['period_ending'] : null;
if (!$periodEnding) {
    echo json_encode(array());
    die();
}
$employeeIds = (isset($_GET['employees']) ? explode(",", $_GET['employees']) : []);
if (!count($employeeIds)) {
    echo json_encode(array());
    die();
}

$str = file_get_contents('./resources/timesheet.json');
$result = json_decode($str, true);
$result['guru_company_id'] = $companyId;
$result['period_ending'] = $periodEnding;

function getRandomTimesheet($timesheets, $excludes) {
    $totalRecords = count($timesheets);
    do {
        $index = rand(0, $totalRecords);
    } while (in_array($index, $excludes));
    return [
        'index' => $index,
        'timesheet' => $timesheets[$index]
    ];
    
}
$indexes = [];
$dataset = ['employees' => []];
foreach ($employeeIds as $employeeId) {
    $timesheet = getRandomTimesheet($result['employees'], $indexes);
    $indexes[] = $timesheet['index'];
    $ts = $timesheet['timesheet'];
    $ts['guru_id'] = $employeeId;
    $dataset['employees'][] = $ts;
}

$result['employees'] = $dataset['employees'];
header('Content-Type: application/json');
echo json_encode($result);
die();