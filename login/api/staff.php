<?php
require_once __DIR__ . "/_common.php";
requireApiAuth();
$method = $_SERVER["REQUEST_METHOD"] ?? "GET";

function staffExists(mysqli $db, int $id): bool
{
    $stmt = $db->prepare("SELECT id FROM admin WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return (bool) $stmt->get_result()->fetch_assoc();
}
function roleExists(mysqli $db, int $id): bool
{
    $stmt = $db->prepare("SELECT role_id FROM roles WHERE role_id = ? LIMIT 1");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return (bool) $stmt->get_result()->fetch_assoc();
}

if ($method === "GET") {
    $id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;
    if ($id > 0) {
        $stmt = $db->prepare(
            "SELECT a.id,a.username,a.email,a.mobile,a.status,a.role_id,r.role_name,a.created_at,a.updated_at FROM admin a LEFT JOIN roles r ON r.role_id=a.role_id WHERE a.id=? LIMIT 1",
        );
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        if (!$row) {
            apiError(404, "Staff member not found");
        }
        $p = $db->prepare(
            "SELECT p.permission_id,p.permission_name FROM role_permissions rp INNER JOIN permissions p ON p.permission_id=rp.permission_id WHERE rp.role_id=? AND p.status=1 ORDER BY p.permission_name",
        );
        $rid = (int) ($row["role_id"] ?? 0);
        $p->bind_param("i", $rid);
        $p->execute();
        $row["permissions"] = $p->get_result()->fetch_all(MYSQLI_ASSOC);
        apiSuccess($row);
    }

    $page = clampInt($_GET["page"] ?? 1, 1, 1000000, 1);
    $limit = clampInt($_GET["limit"] ?? 20, 1, 200, 20);
    $offset = ($page - 1) * $limit;
    $search = trim((string) ($_GET["search"] ?? ""));
    $status =
        isset($_GET["status"]) && $_GET["status"] !== ""
            ? (int) $_GET["status"]
            : null;
    $roleId =
        isset($_GET["role_id"]) && $_GET["role_id"] !== ""
            ? (int) $_GET["role_id"]
            : null;
    $sortMap = [
        "id" => "a.id",
        "username" => "a.username",
        "email" => "a.email",
        "status" => "a.status",
        "created_at" => "a.created_at",
        "updated_at" => "a.updated_at",
    ];
    $sort = $sortMap[$_GET["sort"] ?? "id"] ?? "a.id";
    $order = normalizedOrder($_GET["order"] ?? "DESC");
    $where = [];
    $params = [];
    $types = "";
    if ($search !== "") {
        $where[] = "(a.username LIKE ? OR a.email LIKE ? OR a.mobile LIKE ?)";
        $t = "%" . $search . "%";
        array_push($params, $t, $t, $t);
        $types .= "sss";
    }
    if ($status !== null) {
        $where[] = "a.status=?";
        $params[] = $status;
        $types .= "i";
    }
    if ($roleId !== null) {
        $where[] = "a.role_id=?";
        $params[] = $roleId;
        $types .= "i";
    }
    $whereSql = $where ? "WHERE " . implode(" AND ", $where) : "";
    $c = $db->prepare("SELECT COUNT(*) total FROM admin a $whereSql");
    bindDynamicParams($c, $types, $params);
    $c->execute();
    $total = (int) $c->get_result()->fetch_assoc()["total"];
    $sql = "SELECT a.id,a.username,a.email,a.mobile,a.status,a.role_id,r.role_name,a.created_at,a.updated_at FROM admin a LEFT JOIN roles r ON r.role_id=a.role_id $whereSql ORDER BY $sort $order LIMIT ? OFFSET ?";
    $lp = array_merge($params, [$limit, $offset]);
    $lt = $types . "ii";
    $stmt = $db->prepare($sql);
    bindDynamicParams($stmt, $lt, $lp);
    $stmt->execute();
    apiSuccess([
        "items" => $stmt->get_result()->fetch_all(MYSQLI_ASSOC),
        "pagination" => [
            "page" => $page,
            "limit" => $limit,
            "total" => $total,
            "pages" => (int) ceil($total / $limit),
        ],
    ]);
}

if ($method === "POST") {
    $in = requestInput();
    $username = trim((string) ($in["username"] ?? ""));
    $email = trim((string) ($in["email"] ?? ""));
    $mobile = trim((string) ($in["mobile"] ?? ""));
    $password = (string) ($in["password"] ?? "");
    $roleId = (int) ($in["role_id"] ?? 0);
    $status = isset($in["status"]) ? (int) $in["status"] : 1;
    if (
        $username === "" ||
        $email === "" ||
        $mobile === "" ||
        $password === "" ||
        $roleId <= 0
    ) {
        apiError(
            400,
            "username, email, mobile, password and role_id are required",
        );
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        apiError(400, "Invalid email address");
    }
    if (!in_array($status, [0, 1], true)) {
        apiError(400, "status must be 0 or 1");
    }
    if (!roleExists($db, $roleId)) {
        apiError(400, "Invalid role_id");
    }
    $hash = legacyPasswordHash($password);
    $stmt = $db->prepare(
        "INSERT INTO admin (username,password,email,status,role_id,mobile,created_at,updated_at) VALUES (?,?,?,?,?,?,NOW(),NOW())",
    );
    $stmt->bind_param(
        "sssiis",
        $username,
        $hash,
        $email,
        $status,
        $roleId,
        $mobile,
    );
    if (!$stmt->execute()) {
        apiError(500, "Failed to create staff member");
    }
    apiSuccess(["id" => $stmt->insert_id], "Staff member created", 201);
}

if ($method === "PUT" || $method === "PATCH") {
    $in = requestInput();
    $id = isset($_GET["id"]) ? (int) $_GET["id"] : (int) ($in["id"] ?? 0);
    if ($id <= 0 || !staffExists($db, $id)) {
        apiError(404, "Staff member not found");
    }
    if (($in["action"] ?? "") === "status") {
        if (
            !isset($in["status"]) ||
            !in_array((int) $in["status"], [0, 1], true)
        ) {
            apiError(400, "status must be 0 or 1");
        }
        $status = (int) $in["status"];
        $stmt = $db->prepare(
            "UPDATE admin SET status=?,updated_at=NOW() WHERE id=?",
        );
        $stmt->bind_param("ii", $status, $id);
        $stmt->execute();
        apiSuccess(["id" => $id, "status" => $status], "Staff status updated");
    }
    $fields = [];
    $params = [];
    $types = "";
    foreach ([["username", "s"], ["mobile", "s"]] as [$key, $type]) {
        if (array_key_exists($key, $in)) {
            $v = trim((string) $in[$key]);
            if ($v === "") {
                apiError(400, "$key cannot be empty");
            }
            $fields[] = "$key=?";
            $params[] = $v;
            $types .= $type;
        }
    }
    if (array_key_exists("email", $in)) {
        $v = trim((string) $in["email"]);
        if (!filter_var($v, FILTER_VALIDATE_EMAIL)) {
            apiError(400, "Invalid email address");
        }
        $fields[] = "email=?";
        $params[] = $v;
        $types .= "s";
    }
    if (array_key_exists("role_id", $in)) {
        $v = (int) $in["role_id"];
        if ($v <= 0 || !roleExists($db, $v)) {
            apiError(400, "Invalid role_id");
        }
        $fields[] = "role_id=?";
        $params[] = $v;
        $types .= "i";
    }
    if (array_key_exists("status", $in)) {
        $v = (int) $in["status"];
        if (!in_array($v, [0, 1], true)) {
            apiError(400, "status must be 0 or 1");
        }
        $fields[] = "status=?";
        $params[] = $v;
        $types .= "i";
    }
    if (!empty($in["password"])) {
        $fields[] = "password=?";
        $params[] = legacyPasswordHash((string) $in["password"]);
        $types .= "s";
    }
    if (!$fields) {
        apiError(400, "No fields to update");
    }
    $sql =
        "UPDATE admin SET " .
        implode(", ", $fields) .
        ", updated_at=NOW() WHERE id=?";
    $params[] = $id;
    $types .= "i";
    $stmt = $db->prepare($sql);
    bindDynamicParams($stmt, $types, $params);
    $stmt->execute();
    apiSuccess(["id" => $id], "Staff member updated");
}
apiError(405, "Method not allowed");
