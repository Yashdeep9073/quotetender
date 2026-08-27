<?php
/**
 * CLI functional test harness for the Task Management module.
 * Exercises the real page handlers against the live database using a
 * simulated session (TASK_TEST_MODE prevents real redirects/exits).
 *
 * Usage (temporary file – delete after running):
 *   php inc/test-cli.php admin     # create/view/edit/comment/status/filters/ajax as DVEPL (Admin)
 *   php inc/test-cli.php employee  # RBAC checks as Arun (Costing role, no task permissions)
 *   php inc/test-cli.php delete    # delete + cascade checks as DVEPL, then cleanup
 */

define('TASK_TEST_MODE', true);
error_reporting(E_ALL);
ini_set('display_errors', '1');

$phase = isset($argv[1]) ? $argv[1] : 'admin';
$stateFile = sys_get_temp_dir() . '/task-test-state.json';

$passed = 0;
$failed = 0;

function check($label, $condition)
{
    global $passed, $failed;
    if ($condition) {
        echo "[PASS] $label\n";
        $passed++;
    } else {
        echo "[FAIL] $label\n";
        $failed++;
    }
}

function request($page, $method, $get = [], $post = [])
{
    global $db, $taskUserId, $taskUserName, $taskCanViewAll, $taskCanCreate, $taskCanEdit, $taskCanDelete;
    $_SERVER['REQUEST_METHOD'] = $method;
    $_GET = $get;
    $_POST = $post;
    // Pages use relative includes (../login/navbar.php), so run from the module dir
    $cwd = getcwd();
    chdir(__DIR__ . '/..');
    ob_start();
    include __DIR__ . '/../' . $page;
    $html = ob_get_clean();
    chdir($cwd);
    return $html;
}

function readState($stateFile)
{
    return file_exists($stateFile) ? json_decode(file_get_contents($stateFile), true) : null;
}

function writeState($stateFile, $state)
{
    file_put_contents($stateFile, json_encode($state));
}

session_start();

// Simulated identity per phase (must be set BEFORE init.php runs its auth check)
if ($phase === 'employee') {
    $_SESSION['login_user'] = 'Arun';
    $_SESSION['login_user_id'] = 11;
} else {
    $_SESSION['login_user'] = 'DVEPL';
    $_SESSION['login_user_id'] = 3;
}

require __DIR__ . '/../inc/init.php';

// ---------- helpers to talk to the DB ----------
function dbCount($db, $sql, $types, ...$params)
{
    $s = $db->prepare($sql);
    if ($types !== '' && count($params) > 0) {
        $s->bind_param($types, ...$params);
    }
    $s->execute();
    $row = $s->get_result()->fetch_assoc();
    return (int) array_values($row)[0];
}

// =====================================================================
// PHASE: admin
// =====================================================================
if ($phase === 'admin') {
    // Clean any leftovers from previous runs
    $db->query("DELETE FROM tasks WHERE title LIKE 'TEST %'");

    // Find a real tender row to link (the MES_2026_11223377 example, id 1827)
    $stmtT = $db->prepare("SELECT id, tenderID, reference_code, status FROM user_tender_requests WHERE id = 1827 AND delete_tender = '0'");
    $stmtT->execute();
    $tenderRow = $stmtT->get_result()->fetch_assoc();
    if (!$tenderRow) {
        $stmtT2 = $db->prepare("SELECT id, tenderID, reference_code, status FROM user_tender_requests WHERE delete_tender = '0' ORDER BY id DESC LIMIT 1");
        $stmtT2->execute();
        $tenderRow = $stmtT2->get_result()->fetch_assoc();
    }
    check('Tender row found for linking', (bool) $tenderRow);
    $tenderId = (int) $tenderRow['id'];
    echo "     Using tender id={$tenderId} tenderID={$tenderRow['tenderID']} ref={$tenderRow['reference_code']}\n";

    // 1. CREATE (Tender/Query task)
    request('create.php', 'POST', [], [
        'title'             => 'TEST Review MES tender documents',
        'description'       => 'Review the tender docs and prepare a summary.',
        'task_type'         => 'Tender/Query',
        'tender_request_id' => (string) $tenderId,
        'assigned_to'       => '11', // Arun
        'priority'          => 'High',
        'status'            => 'Pending',
        'start_date'        => '2026-08-27',
        'due_date'          => '2026-09-10',
    ]);
    $created = null;
    $stmtNew = $db->prepare("SELECT * FROM tasks WHERE title = 'TEST Review MES tender documents' ORDER BY id DESC LIMIT 1");
    $stmtNew->execute();
    $created = $stmtNew->get_result()->fetch_assoc();
    check('CREATE inserts task', (bool) $created);
    $taskId = $created ? (int) $created['id'] : 0;
    if ($created) {
        check('CREATE sets created_by from session (3)', (int) $created['created_by'] === 3);
        check('CREATE stores task_type', $created['task_type'] === 'Tender/Query');
        check('CREATE stores tender_request_id', (int) $created['tender_request_id'] === $tenderId);
        check('CREATE stores assigned_to', (int) $created['assigned_to'] === 11);
        check('CREATE history "Task created" exists',
            dbCount($db, "SELECT COUNT(*) c FROM task_history WHERE task_id = ? AND action = 'Task created'", 'i', $taskId) === 1);
    }

    // 2. CREATE invalid: non-existent employee -> rejected
    $before = dbCount($db, "SELECT COUNT(*) c FROM tasks", '');
    request('create.php', 'POST', [], [
        'title'             => 'TEST INVALID SHOULD NOT SAVE',
        'task_type'         => 'General',
        'tender_request_id' => '',
        'assigned_to'       => '999999',
        'priority'          => 'Medium',
        'status'            => 'Pending',
        'start_date'        => '',
        'due_date'          => '',
    ]);
    check('CREATE rejects non-existent employee', dbCount($db, "SELECT COUNT(*) c FROM tasks", '') === $before);

    // 3. CREATE invalid priority -> rejected
    request('create.php', 'POST', [], [
        'title'             => 'TEST INVALID PRIORITY',
        'task_type'         => 'General',
        'tender_request_id' => '',
        'assigned_to'       => '11',
        'priority'          => 'SuperUrgent',
        'status'            => 'Pending',
        'start_date'        => '',
        'due_date'          => '',
    ]);
    check('CREATE rejects invalid priority', dbCount($db, "SELECT COUNT(*) c FROM tasks", '') === $before);

    // 4. CREATE invalid dates -> rejected
    request('create.php', 'POST', [], [
        'title'             => 'TEST INVALID DATES',
        'task_type'         => 'General',
        'tender_request_id' => '',
        'assigned_to'       => '11',
        'priority'          => 'Medium',
        'status'            => 'Pending',
        'start_date'        => '2026-09-20',
        'due_date'          => '2026-09-01',
    ]);
    check('CREATE rejects due<start dates', dbCount($db, "SELECT COUNT(*) c FROM tasks", '') === $before);

    // 5. CREATE General task (no tender)
    request('create.php', 'POST', [], [
        'title'             => 'TEST Prepare monthly report',
        'description'       => 'General task for Arun.',
        'task_type'         => 'General',
        'tender_request_id' => '',
        'assigned_to'       => '11',
        'priority'          => 'Medium',
        'status'            => 'Pending',
        'start_date'        => '',
        'due_date'          => '2026-09-15',
    ]);
    $stmtGen = $db->prepare("SELECT * FROM tasks WHERE title = 'TEST Prepare monthly report' ORDER BY id DESC LIMIT 1");
    $stmtGen->execute();
    $generalTask = $stmtGen->get_result()->fetch_assoc();
    check('CREATE general task (tender_request_id NULL)', $generalTask && $generalTask['tender_request_id'] === null);
    $generalTaskId = $generalTask ? (int) $generalTask['id'] : 0;

    // 6. VIEW
    $html = request('view.php', 'GET', ['id' => (string) $taskId], []);
    check('VIEW renders task title', strpos($html, 'TEST Review MES tender documents') !== false);
    check('VIEW renders related tender id', strpos($html, (string) $tenderRow['tenderID']) !== false);
    check('VIEW renders task created-by username (DVEPL)', strpos($html, 'DVEPL') !== false);
    check('VIEW renders assigned username (Arun)', strpos($html, 'Arun') !== false);
    check('VIEW has no placeholder history text', strpos($html, 'History tracking will be implemented later') === false);
    check('VIEW has no fake data', strpos($html, 'Website UI Redesign') === false && strpos($html, 'Rahul') === false);

    // 7. EDIT (change several fields)
    request('edit.php', 'POST', ['id' => (string) $taskId], [
        'title'             => 'TEST Review MES tender documents (edited)',
        'description'       => 'Updated description.',
        'task_type'         => 'Tender/Query',
        'tender_request_id' => (string) $tenderId,
        'assigned_to'       => '37', // Prerna
        'priority'          => 'Urgent',
        'status'            => 'In Progress',
        'start_date'        => '2026-08-27',
        'due_date'          => '2026-09-12',
    ]);
    $edited = null;
    $stmtUpd = $db->prepare("SELECT * FROM tasks WHERE id = ?");
    $stmtUpd->bind_param('i', $taskId);
    $stmtUpd->execute();
    $edited = $stmtUpd->get_result()->fetch_assoc();
    check('EDIT updates title', $edited['title'] === 'TEST Review MES tender documents (edited)');
    check('EDIT updates priority', $edited['priority'] === 'Urgent');
    check('EDIT updates status', $edited['status'] === 'In Progress');
    check('EDIT updates assignee', (int) $edited['assigned_to'] === 37);
    check('EDIT updates due date', $edited['due_date'] === '2026-09-12');
    check('EDIT preserves created_by (server-set)', (int) $edited['created_by'] === 3);
    $hist = dbCount($db, "SELECT COUNT(*) c FROM task_history WHERE task_id = ? AND action = 'Title changed'", 'i', $taskId);
    check('EDIT logs "Title changed"', $hist >= 1);
    $hist = dbCount($db, "SELECT COUNT(*) c FROM task_history WHERE task_id = ? AND action = 'Assigned to changed'", 'i', $taskId);
    check('EDIT logs "Assigned to changed"', $hist >= 1);
    $hist = dbCount($db, "SELECT COUNT(*) c FROM task_history WHERE task_id = ? AND action = 'Priority changed'", 'i', $taskId);
    check('EDIT logs "Priority changed"', $hist >= 1);
    $hist = dbCount($db, "SELECT COUNT(*) c FROM task_history WHERE task_id = ? AND action = 'Status changed'", 'i', $taskId);
    check('EDIT logs "Status changed"', $hist >= 1);
    $hist = dbCount($db, "SELECT COUNT(*) c FROM task_history WHERE task_id = ? AND action = 'Due date changed'", 'i', $taskId);
    check('EDIT logs "Due date changed"', $hist >= 1);

    // 8. COMMENT (as admin)
    request('view.php', 'POST', ['id' => (string) $taskId], ['action' => 'comment', 'comment' => 'TEST comment: documents look fine.']);
    check('COMMENT inserted',
        dbCount($db, "SELECT COUNT(*) c FROM task_comments WHERE task_id = ? AND comment = 'TEST comment: documents look fine.'", 'i', $taskId) === 1);
    $html = request('view.php', 'GET', ['id' => (string) $taskId], []);
    check('VIEW renders comment', strpos($html, 'TEST comment: documents look fine.') !== false);
    check('VIEW renders history section', strpos($html, 'Activity / History') !== false);

    // 9. STATUS update via view: arbitrary string rejected, whitelist works
    request('view.php', 'POST', ['id' => (string) $taskId], ['action' => 'status', 'status' => 'Hacked']);
    check('STATUS rejects arbitrary string',
        dbCount($db, "SELECT COUNT(*) c FROM tasks WHERE id = ? AND status = 'In Progress'", 'i', $taskId) === 1);
    request('view.php', 'POST', ['id' => (string) $taskId], ['action' => 'status', 'status' => 'Completed']);
    check('STATUS whitelist update works',
        dbCount($db, "SELECT COUNT(*) c FROM tasks WHERE id = ? AND status = 'Completed'", 'i', $taskId) === 1);

    // 10. Filters on index
    $html = request('index.php', 'GET', ['q' => 'monthly report'], []);
    check('INDEX filter search finds general task', strpos($html, 'TEST Prepare monthly report') !== false);
    $html = request('index.php', 'GET', ['status' => 'Completed'], []);
    check('INDEX filter status', strpos($html, 'TEST Review MES tender documents (edited)') !== false);
    $html = request('index.php', 'GET', ['tender_ref' => (string) $tenderRow['tenderID']], []);
    check('INDEX filter tender ref', strpos($html, 'TEST Review MES tender documents (edited)') !== false);
    $html = request('index.php', 'GET', ['employee' => '37'], []);
    check('INDEX filter employee', strpos($html, 'TEST Review MES tender documents (edited)') !== false);
    $html = request('index.php', 'GET', ['task_type' => 'Tender/Query'], []);
    check('INDEX filter task_type', strpos($html, 'TEST Review MES tender documents (edited)') !== false);

    // 11. AJAX tender search
    $html = request('ajax-tenders.php', 'GET', ['q' => '11223377'], []);
    $json = json_decode($html, true);
    check('AJAX tender search returns result', isset($json['results']) && count($json['results']) > 0);
    check('AJAX result contains tender id', isset($json['results'][0]) && strpos($json['results'][0]['text'], $tenderRow['tenderID']) !== false);

    // 12. DELETE via GET must be rejected (task survives)
    request('delete.php', 'GET', ['id' => (string) $taskId], []);
    check('DELETE GET is rejected', dbCount($db, "SELECT COUNT(*) c FROM tasks WHERE id = ?", 'i', $taskId) === 1);

    writeState($stateFile, ['taskId' => $taskId, 'generalTaskId' => $generalTaskId, 'tenderId' => $tenderId]);
}

// =====================================================================
// PHASE: employee (Arun – role 26 Costing, no task permissions)
// =====================================================================
if ($phase === 'employee') {
    $state = readState($stateFile);
    if (!$state) {
        echo "Run 'php inc/test-cli.php admin' first.\n";
        exit(1);
    }
    $taskId = (int) $state['taskId'];
    $generalTaskId = (int) $state['generalTaskId'];

    // Index shows "My Tasks" only
    $html = request('index.php', 'GET', [], []);
    check('EMPLOYEE sees "My Tasks" heading', strpos($html, 'My Tasks') !== false);
    check('EMPLOYEE does not see "Create Task" button', strpos($html, 'Create Task') === false);
    check('EMPLOYEE sees own general task', strpos($html, 'TEST Prepare monthly report') !== false);
    check('EMPLOYEE does not see task assigned to Prerna', strpos($html, 'TEST Review MES tender documents (edited)') === false);

    // Employee cannot create
    $before = dbCount($db, "SELECT COUNT(*) c FROM tasks", '');
    request('create.php', 'POST', [], [
        'title'             => 'TEST EMPLOYEE CREATE MUST FAIL',
        'task_type'         => 'General',
        'tender_request_id' => '',
        'assigned_to'       => '11',
        'priority'          => 'Medium',
        'status'            => 'Pending',
        'start_date'        => '',
        'due_date'          => '',
    ]);
    check('EMPLOYEE create blocked (RBAC)', dbCount($db, "SELECT COUNT(*) c FROM tasks", '') === $before);

    // Employee cannot edit (even own task)
    request('edit.php', 'POST', ['id' => (string) $generalTaskId], [
        'title'             => 'TEST EMPLOYEE EDIT MUST FAIL',
        'task_type'         => 'General',
        'tender_request_id' => '',
        'assigned_to'       => '11',
        'priority'          => 'Medium',
        'status'            => 'Pending',
        'start_date'        => '',
        'due_date'          => '2026-09-15',
    ]);
    check('EMPLOYEE edit blocked (RBAC)',
        dbCount($db, "SELECT COUNT(*) c FROM tasks WHERE id = ? AND title = 'TEST Prepare monthly report'", 'i', $generalTaskId) === 1);

    // Employee CAN update status of own task
    request('view.php', 'POST', ['id' => (string) $generalTaskId], ['action' => 'status', 'status' => 'In Progress']);
    check('EMPLOYEE updates own task status',
        dbCount($db, "SELECT COUNT(*) c FROM tasks WHERE id = ? AND status = 'In Progress'", 'i', $generalTaskId) === 1);

    // Employee CANNOT update status of someone else's task (assigned to Prerna)
    request('view.php', 'POST', ['id' => (string) $taskId], ['action' => 'status', 'status' => 'Pending']);
    check('EMPLOYEE cannot update others status (IDOR)',
        dbCount($db, "SELECT COUNT(*) c FROM tasks WHERE id = ? AND status = 'Completed'", 'i', $taskId) === 1);

    // Employee cannot view someone else's task
    $html = request('view.php', 'GET', ['id' => (string) $taskId], []);
    check('EMPLOYEE blocked from viewing others task', strpos($html, 'TEST Review MES tender documents (edited)') === false);

    // Employee can comment on own task
    request('view.php', 'POST', ['id' => (string) $generalTaskId], ['action' => 'comment', 'comment' => 'TEST employee comment.']);
    check('EMPLOYEE comments on own task',
        dbCount($db, "SELECT COUNT(*) c FROM task_comments WHERE task_id = ? AND comment = 'TEST employee comment.'", 'i', $generalTaskId) === 1);

    // Employee cannot comment on others' task
    request('view.php', 'POST', ['id' => (string) $taskId], ['action' => 'comment', 'comment' => 'TEST should not be added.']);
    check('EMPLOYEE cannot comment on others task',
        dbCount($db, "SELECT COUNT(*) c FROM task_comments WHERE task_id = ? AND comment = 'TEST should not be added.'", 'i', $taskId) === 0);

    // Employee cannot delete
    request('delete.php', 'POST', [], ['id' => (string) $generalTaskId]);
    check('EMPLOYEE delete blocked (RBAC)',
        dbCount($db, "SELECT COUNT(*) c FROM tasks WHERE id = ?", 'i', $generalTaskId) === 1);
}

// =====================================================================
// PHASE: delete (DVEPL) + cleanup
// =====================================================================
if ($phase === 'delete') {
    $state = readState($stateFile);
    if (!$state) {
        echo "Run 'php inc/test-cli.php admin' first.\n";
        exit(1);
    }
    $taskId = (int) $state['taskId'];
    $generalTaskId = (int) $state['generalTaskId'];

    // POST delete -> cascade comments/history
    request('delete.php', 'POST', [], ['id' => (string) $taskId]);
    check('DELETE removes task', dbCount($db, "SELECT COUNT(*) c FROM tasks WHERE id = ?", 'i', $taskId) === 0);
    $cascade = dbCount(
        $db,
        "SELECT (SELECT COUNT(*) FROM task_comments WHERE task_id = ?) + (SELECT COUNT(*) FROM task_history WHERE task_id = ?) c",
        'ii',
        $taskId,
        $taskId
    );
    check('DELETE cascades comments/history', $cascade === 0);

    // Non-existent id -> graceful error, nothing breaks
    request('delete.php', 'POST', [], ['id' => '9999999']);
    check('DELETE unknown id handled', true);

    // Cleanup remaining test data
    $db->query("DELETE FROM tasks WHERE title LIKE 'TEST %'");
    @unlink($stateFile);
    check('CLEANUP removed test tasks',
        dbCount($db, "SELECT COUNT(*) c FROM tasks WHERE title LIKE 'TEST %'", '') === 0);
}

echo "\n-----------------------------------\n";
echo "PASSED: $passed  FAILED: $failed\n";
echo $failed === 0 ? "ALL TESTS PASSED\n" : "SOME TESTS FAILED\n";
exit($failed === 0 ? 0 : 1);
