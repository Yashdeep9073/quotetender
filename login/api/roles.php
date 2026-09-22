<?php
require_once __DIR__ . "/_common.php";
requireApiAuth();
$method = $_SERVER["REQUEST_METHOD"] ?? "GET";
function roleRowExists(mysqli $db, int $id): bool
{
    $s = $db->prepare("SELECT role_id FROM roles WHERE role_id=? LIMIT 1");
    $s->bind_param("i", $id);
    $s->execute();
    return (bool) $s->get_result()->fetch_assoc();
}
if ($method === "GET") {
    $id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;
    if ($id > 0) {
        $s = $db->prepare(
            "SELECT role_id,role_name,role_status,created_at,updated_at FROM roles WHERE role_id=? LIMIT 1",
        );
        $s->bind_param("i", $id);
        $s->execute();
        $r = $s->get_result()->fetch_assoc();
        if (!$r) {
            apiError(404, "Role not found");
        }
        $p = $db->prepare(
            "SELECT p.permission_id,p.permission_name,p.status FROM role_permissions rp INNER JOIN permissions p ON p.permission_id=rp.permission_id WHERE rp.role_id=? ORDER BY p.permission_name",
        );
        $p->bind_param("i", $id);
        $p->execute();
        $r["permissions"] = $p->get_result()->fetch_all(MYSQLI_ASSOC);
        apiSuccess($r);
    }
    $status =
        isset($_GET["status"]) && $_GET["status"] !== ""
            ? (int) $_GET["status"]
            : null;
    $search = trim((string) ($_GET["search"] ?? ""));
    $w = [];
    $p = [];
    $t = "";
    if ($status !== null) {
        $w[] = "r.role_status=?";
        $p[] = $status;
        $t .= "i";
    }
    if ($search !== "") {
        $w[] = "r.role_name LIKE ?";
        $p[] = "%" . $search . "%";
        $t .= "s";
    }
    $ws = $w ? "WHERE " . implode(" AND ", $w) : "";
    $s = $db->prepare(
        "SELECT r.role_id,r.role_name,r.role_status,r.created_at,r.updated_at,COUNT(rp.role_permission_id) permission_count FROM roles r LEFT JOIN role_permissions rp ON rp.role_id=r.role_id $ws GROUP BY r.role_id,r.role_name,r.role_status,r.created_at,r.updated_at ORDER BY r.role_name",
    );
    bindDynamicParams($s, $t, $p);
    $s->execute();
    apiSuccess($s->get_result()->fetch_all(MYSQLI_ASSOC));
}
if ($method === "POST") {
    $in = requestInput();
    $name = trim((string) ($in["role_name"] ?? ""));
    $status = isset($in["role_status"]) ? (int) $in["role_status"] : 1;
    $ids = $in["permission_ids"] ?? [];
    if ($name === "") {
        apiError(400, "role_name is required");
    }
    if (!in_array($status, [0, 1], true)) {
        apiError(400, "role_status must be 0 or 1");
    }
    if (!is_array($ids)) {
        apiError(400, "permission_ids must be an array");
    }
    $db->begin_transaction();
    try {
        $s = $db->prepare(
            "INSERT INTO roles (role_name,role_status,created_at,updated_at) VALUES (?,?,NOW(),NOW())",
        );
        $s->bind_param("si", $name, $status);
        $s->execute();
        $rid = $s->insert_id;
        $check = $db->prepare(
            "SELECT permission_id FROM permissions WHERE permission_id=? LIMIT 1",
        );
        $ins = $db->prepare(
            "INSERT INTO role_permissions (role_id,permission_id,created_at) VALUES (?,?,NOW())",
        );
        foreach (
            array_values(array_unique(array_map("intval", $ids)))
            as $pid
        ) {
            if ($pid <= 0) {
                continue;
            }
            $check->bind_param("i", $pid);
            $check->execute();
            if (!$check->get_result()->fetch_assoc()) {
                throw new RuntimeException("Invalid permission_id: $pid");
            }
            $ins->bind_param("ii", $rid, $pid);
            $ins->execute();
        }
        $db->commit();
        apiSuccess(["role_id" => $rid], "Role created", 201);
    } catch (Throwable $e) {
        $db->rollback();
        apiError(400, $e->getMessage());
    }
}
if ($method === "PUT" || $method === "PATCH") {
    $in = requestInput();
    $id = isset($_GET["id"])
        ? (int) $_GET["id"]
        : (int) ($in["role_id"] ?? ($in["id"] ?? 0));
    if ($id <= 0 || !roleRowExists($db, $id)) {
        apiError(404, "Role not found");
    }
    $db->begin_transaction();
    try {
        $fields = [];
        $params = [];
        $types = "";
        if (array_key_exists("role_name", $in)) {
            $v = trim((string) $in["role_name"]);
            if ($v === "") {
                throw new RuntimeException("role_name cannot be empty");
            }
            $fields[] = "role_name=?";
            $params[] = $v;
            $types .= "s";
        }
        if (array_key_exists("role_status", $in)) {
            $v = (int) $in["role_status"];
            if (!in_array($v, [0, 1], true)) {
                throw new RuntimeException("role_status must be 0 or 1");
            }
            $fields[] = "role_status=?";
            $params[] = $v;
            $types .= "i";
        }
        if ($fields) {
            $sql =
                "UPDATE roles SET " .
                implode(", ", $fields) .
                ", updated_at=NOW() WHERE role_id=?";
            $params[] = $id;
            $types .= "i";
            $s = $db->prepare($sql);
            bindDynamicParams($s, $types, $params);
            $s->execute();
        }
        if (array_key_exists("permission_ids", $in)) {
            if (!is_array($in["permission_ids"])) {
                throw new RuntimeException("permission_ids must be an array");
            }
            $d = $db->prepare("DELETE FROM role_permissions WHERE role_id=?");
            $d->bind_param("i", $id);
            $d->execute();
            $check = $db->prepare(
                "SELECT permission_id FROM permissions WHERE permission_id=? LIMIT 1",
            );
            $ins = $db->prepare(
                "INSERT INTO role_permissions (role_id,permission_id,created_at) VALUES (?,?,NOW())",
            );
            foreach (
                array_values(
                    array_unique(array_map("intval", $in["permission_ids"])),
                )
                as $pid
            ) {
                if ($pid <= 0) {
                    continue;
                }
                $check->bind_param("i", $pid);
                $check->execute();
                if (!$check->get_result()->fetch_assoc()) {
                    throw new RuntimeException("Invalid permission_id: $pid");
                }
                $ins->bind_param("ii", $id, $pid);
                $ins->execute();
            }
        }
        if (!$fields && !array_key_exists("permission_ids", $in)) {
            throw new RuntimeException("No fields to update");
        }
        $db->commit();
        apiSuccess(["role_id" => $id], "Role updated");
    } catch (Throwable $e) {
        $db->rollback();
        apiError(400, $e->getMessage());
    }
}
apiError(405, "Method not allowed");
