<?php
// Temporary UI smoke test for the Tender/Query selector (deleted after run).
define('TASK_TEST_MODE', true);
$_SERVER['DOCUMENT_ROOT'] = realpath(__DIR__ . '/..');

session_start();
$_SESSION['login_user'] = 'DVEPL';

require_once __DIR__ . '/../login/db/config.php';

$checks = [];
$check = function ($label, $ok) use (&$checks) { $checks[$label] = $ok; };

// --- State 1: fresh form (GET) ---
$_SERVER['REQUEST_METHOD'] = 'GET';
ob_start();
require __DIR__ . '/../login/task-management/create.php';
$html1 = ob_get_clean();
$check('fresh: tender-picker wrapper', strpos($html1, 'class="tender-picker"') !== false);
$check('fresh: search icon', strpos($html1, 'icon-search tender-search-icon') !== false);
$check('fresh: helper text', strpos($html1, 'Search by Tender ID or Reference Code') !== false);
$check('fresh: summary container', strpos($html1, 'id="tender-summary"') !== false);
$check('fresh: no initial renderSummary call', strpos($html1, 'renderSummary({') === false);
$check('fresh: chip template', strpos($html1, 'function formatTenderSelection') !== false);
$check('fresh: language overrides', strpos($html1, 'inputTooShort') !== false && strpos($html1, 'noResults') !== false);
$check('fresh: scoped CSS', strpos($html1, '.tender-picker .select2-dropdown') !== false);

// --- State 2: validation error with a tender preselected (POST) ---
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = [
    'title' => '', // empty title -> validation error -> form re-display
    'description' => '',
    'task_type' => 'Tender/Query',
    'tender_request_id' => '2464',
    'assigned_to' => '11',
    'priority' => 'Medium',
    'status' => 'Pending',
    'start_date' => '2026-09-01',
    'due_date' => '2026-09-05',
];
ob_start();
require __DIR__ . '/../login/task-management/create.php';
$html2 = ob_get_clean();
$check('error: preselected option kept', strpos($html2, 'value="2464" selected') !== false);
$check('error: initial renderSummary JSON', strpos($html2, 'renderSummary(') !== false && strpos($html2, 'tenderID') !== false);
$check('error: tender group visible', strpos($html2, 'id="tender-select-group" style=""') !== false);

// --- State 3: AJAX endpoint returns structured fields ---
$_GET = ['q' => 'MES'];
ob_start();
require __DIR__ . '/../login/task-management/ajax-tenders.php';
$json = ob_get_clean();
$data = json_decode($json, true);
$check('ajax: valid JSON with results', is_array($data) && isset($data['results']));
$first = isset($data['results'][0]) ? $data['results'][0] : [];
$check('ajax: structured fields', isset($first['tenderID']) && isset($first['reference_code']) && isset($first['status']) && isset($first['member_name']) && isset($first['created_date']));

foreach ($checks as $label => $ok) {
    echo ($ok ? 'OK   ' : 'FAIL ') . $label . "\n";
}

// Dump the inline script of the fresh render for a JS syntax check
if (preg_match('#<script>\s*(.*?)\s*</script>\s*</body>#s', $html1, $m)) {
    file_put_contents(__DIR__ . '/tender_selector_inline.js', $m[1]);
    echo "JS extracted\n";
}
