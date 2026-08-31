<?php
/**
 * Task Management – shared bootstrap.
 *
 * Reuses the existing application session, database connection and the existing
 * RBAC mechanism (admin.role_id -> roles -> role_permissions -> permissions),
 * the same mechanism used by login/navbar.php. No parallel auth system.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["login_user"])) {
    header("location: ../index.php");
    exit;
}

require_once __DIR__ . '/../../db/config.php';

if (!$db) {
    die('Database connection failed. Please check your configuration.');
}

$taskUserName = $_SESSION['login_user'];

// Resolve the current admin user server-side. The client can never
// choose the creator/actor identity.
$stmtUser = $db->prepare("SELECT id, username, role_id FROM admin WHERE username = ? AND status = 1");
$stmtUser->bind_param('s', $taskUserName);
$stmtUser->execute();
$taskUserRow = $stmtUser->get_result()->fetch_assoc();
if (!$taskUserRow) {
    session_unset();
    session_destroy();
    header("location: ../index.php");
    exit;
}
$taskUserId = (int) $taskUserRow['id'];

// ---------- RBAC (same tables/convention as navbar.php) ----------
$taskPrivileges = [];
$taskRoleName   = '';
$taskIsAdminRole = false;
$taskRoleId = $taskUserRow['role_id'];
if ($taskRoleId !== null) {
    $stmtRole = $db->prepare("SELECT role_name FROM roles WHERE role_id = ?");
    $stmtRole->bind_param('i', $taskRoleId);
    $stmtRole->execute();
    $roleRow = $stmtRole->get_result()->fetch_assoc();
    if ($roleRow) {
        $taskRoleName = $roleRow['role_name'];
        $taskIsAdminRole = (strtolower($taskRoleName) === 'admin');
        $stmtPriv = $db->prepare(
            "SELECT p.permission_name
               FROM permissions p
               JOIN role_permissions rp ON p.permission_id = rp.permission_id
              WHERE rp.role_id = ?"
        );
        $stmtPriv->bind_param('i', $taskRoleId);
        $stmtPriv->execute();
        $rows = $stmtPriv->get_result()->fetch_all(MYSQLI_ASSOC);
        $taskPrivileges = array_column($rows, 'permission_name');
    }
}

$taskHasPermission = function ($permission) use ($taskIsAdminRole, $taskPrivileges) {
    if ($taskIsAdminRole) {
        return true;
    }
    return in_array($permission, $taskPrivileges, true);
};

// Module-level capability flags
$taskCanViewAll = (bool) $taskHasPermission('Task Management');
$taskCanCreate  = (bool) $taskHasPermission('Add Task');
$taskCanEdit    = (bool) $taskHasPermission('Edit Task');
$taskCanDelete  = (bool) $taskHasPermission('Delete Task');

// ---------- Helpers ----------

/**
 * Redirect (optionally with a flash message shown on the next page).
 * In CLI test mode (TASK_TEST_MODE defined) this returns instead of exiting,
 * so page handlers can be exercised from a test harness.
 */
function task_redirect($url, $flashType = null, $flashMessage = null)
{
    if ($flashType !== null && $flashMessage !== null) {
        $_SESSION['task_flash'] = ['type' => $flashType, 'message' => $flashMessage];
    }
    if (defined('TASK_TEST_MODE') && TASK_TEST_MODE) {
        return;
    }
    header('Location: ' . $url);
    exit;
}

/** Validate a positive integer (IDs from $_GET/$_POST). Returns int or false. */
function task_get_int($value)
{
    $v = filter_var($value, FILTER_VALIDATE_INT);
    if ($v === false || $v === null) {
        return false;
    }
    return (int) $v;
}

/** Whitelists – never accept arbitrary strings from POST. */
function task_priorities()
{
    return ['Low', 'Medium', 'High', 'Urgent'];
}

function task_statuses()
{
    return ['Pending', 'In Progress', 'Completed', 'Cancelled'];
}

function task_types()
{
    return ['General', 'Tender/Query'];
}

/** Validate Y-m-d date. Returns the date string, null for empty, false for invalid. */
function task_valid_date($value)
{
    if ($value === null || $value === '') {
        return null;
    }
    if (!is_string($value)) {
        return false;
    }
    $d = DateTime::createFromFormat('Y-m-d', $value);
    $errors = DateTime::getLastErrors();
    if (!$d || ($errors && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))) {
        return false;
    }
    return $d->format('Y-m-d');
}

/** Load a single task joined with usernames and tender/query info. */
function task_load($db, $taskId)
{
    $sql = "SELECT t.*,
                   assigned.username   AS assigned_username,
                   creator.username    AS created_username,
                   utr.tenderID        AS tender_id_number,
                   utr.reference_code  AS tender_reference_code,
                   utr.status          AS tender_status,
                   utr.created_at      AS tender_created_at,
                   m.name              AS tender_member_name,
                   m.firm_name         AS tender_member_firm
              FROM tasks t
              LEFT JOIN admin assigned ON assigned.id = t.assigned_to
              LEFT JOIN admin creator  ON creator.id  = t.created_by
              LEFT JOIN user_tender_requests utr ON utr.id = t.tender_request_id
              LEFT JOIN members m ON m.member_id = utr.member_id
             WHERE t.id = ?";
    $stmt = $db->prepare($sql);
    $stmt->bind_param('i', $taskId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

/** Row-level access: managers/admins see everything, an employee only own tasks. */
function task_can_view_row($task, $taskUserId, $taskCanViewAll)
{
    if (!$task) {
        return false;
    }
    if ($taskCanViewAll) {
        return true;
    }
    return (int) $task['assigned_to'] === (int) $taskUserId;
}

/** Record an entry in task_history. */
function task_log_history($db, $taskId, $userId, $action, $oldValue = null, $newValue = null)
{
    $stmt = $db->prepare(
        "INSERT INTO task_history (task_id, user_id, action, old_value, new_value) VALUES (?, ?, ?, ?, ?)"
    );
    $stmt->bind_param('iisss', $taskId, $userId, $action, $oldValue, $newValue);
    $stmt->execute();
    return $stmt->affected_rows > 0;
}

/** Render (and consume) the flash message stored by task_redirect(). */
function task_render_flash()
{
    if (!empty($_SESSION['task_flash'])) {
        $flash = $_SESSION['task_flash'];
        unset($_SESSION['task_flash']);
        $type  = $flash['type'] === 'success' ? 'success' : 'danger';
        $icon  = $flash['type'] === 'success' ? 'check' : 'alert-triangle';
        echo '<div class="alert alert-' . $type . ' alert-dismissible fade show" role="alert" style="font-size:15px;">'
            . '<strong><i class="feather icon-' . $icon . '"></i></strong> '
            . htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8')
            . '<button type="button" class="close" data-dismiss="alert" aria-label="Close">'
            . '<span aria-hidden="true">&times;</span></button></div>';
    }
}

/** Output escaping helper (shared by all module pages). */
function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/** Format a database date/datetime value for display. */
function fmt($dbValue, $withTime = false)
{
    if ($dbValue === null || $dbValue === '') {
        return '—';
    }
    $ts = strtotime($dbValue);
    return $ts ? date($withTime ? 'd M Y H:i' : 'd M Y', $ts) : e($dbValue);
}

/** Status badge colour map for the existing Bootstrap classes. */
function task_status_badge($status)
{
    $map = [
        'Pending'     => 'badge-secondary',
        'In Progress' => 'badge-warning',
        'Completed'   => 'badge-success',
        'Cancelled'   => 'badge-danger',
    ];
    return isset($map[$status]) ? $map[$status] : 'badge-secondary';
}

function task_priority_badge($priority)
{
    $map = [
        'Low'    => 'badge-secondary',
        'Medium' => 'badge-info',
        'High'   => 'badge-warning',
        'Urgent' => 'badge-danger',
    ];
    return isset($map[$priority]) ? $map[$priority] : 'badge-secondary';
}
