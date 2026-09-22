<?php
require_once __DIR__ . "/_common.php";
requireApiAuth();
$method = $_SERVER["REQUEST_METHOD"] ?? "GET";
function customerExists(mysqli $db, int $id): bool
{
    $s = $db->prepare(
        "SELECT member_id FROM members WHERE member_id=? LIMIT 1",
    );
    $s->bind_param("i", $id);
    $s->execute();
    return (bool) $s->get_result()->fetch_assoc();
}
if ($method === "GET") {
    $id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;
    if ($id > 0) {
        $s = $db->prepare(
            "SELECT member_id,name,firm_name,mobile,email_id AS email,city_state,state_code,created_date,status,expiry_time,max_request,pending_request FROM members WHERE member_id=? LIMIT 1",
        );
        $s->bind_param("i", $id);
        $s->execute();
        $r = $s->get_result()->fetch_assoc();
        if (!$r) {
            apiError(404, "Customer not found");
        }
        apiSuccess($r);
    }
    $page = clampInt($_GET["page"] ?? 1, 1, 1000000, 1);
    $limit = clampInt($_GET["limit"] ?? 20, 1, 200, 20);
    $offset = ($page - 1) * $limit;
    $search = trim((string) ($_GET["search"] ?? ""));
    $status =
        isset($_GET["status"]) && $_GET["status"] !== ""
            ? (string) $_GET["status"]
            : null;
    $state = trim((string) ($_GET["state_code"] ?? ""));
    $city = trim((string) ($_GET["city_state"] ?? ""));
    $sortMap = [
        "member_id" => "member_id",
        "name" => "name",
        "firm_name" => "firm_name",
        "status" => "status",
        "created_date" => "created_date",
    ];
    $sort = $sortMap[$_GET["sort"] ?? "member_id"] ?? "member_id";
    $order = normalizedOrder($_GET["order"] ?? "DESC");
    $w = [];
    $p = [];
    $t = "";
    if ($search !== "") {
        $w[] =
            "(name LIKE ? OR firm_name LIKE ? OR email_id LIKE ? OR mobile LIKE ?)";
        $x = "%" . $search . "%";
        array_push($p, $x, $x, $x, $x);
        $t .= "ssss";
    }
    if ($status !== null) {
        $w[] = "status=?";
        $p[] = $status;
        $t .= "s";
    }
    if ($state !== "") {
        $w[] = "state_code=?";
        $p[] = $state;
        $t .= "s";
    }
    if ($city !== "") {
        $w[] = "city_state=?";
        $p[] = $city;
        $t .= "s";
    }
    $ws = $w ? "WHERE " . implode(" AND ", $w) : "";
    $c = $db->prepare("SELECT COUNT(*) total FROM members $ws");
    bindDynamicParams($c, $t, $p);
    $c->execute();
    $total = (int) $c->get_result()->fetch_assoc()["total"];
    $sql = "SELECT member_id,name,firm_name,mobile,email_id AS email,city_state,state_code,created_date,status,max_request,pending_request FROM members $ws ORDER BY $sort $order LIMIT ? OFFSET ?";
    $lp = array_merge($p, [$limit, $offset]);
    $lt = $t . "ii";
    $s = $db->prepare($sql);
    bindDynamicParams($s, $lt, $lp);
    $s->execute();
    apiSuccess([
        "items" => $s->get_result()->fetch_all(MYSQLI_ASSOC),
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
    $name = trim((string) ($in["name"] ?? ""));
    $firm = trim((string) ($in["firm_name"] ?? ""));
    $mobile = trim((string) ($in["mobile"] ?? ""));
    $email = trim((string) ($in["email"] ?? ($in["email_id"] ?? "")));
    $city = trim((string) ($in["city_state"] ?? ""));
    $state = trim((string) ($in["state_code"] ?? ""));
    $password = (string) ($in["password"] ?? "");
    $status = (string) ($in["status"] ?? "0");
    $max = (string) ($in["max_request"] ?? "0");
    $pending = (string) ($in["pending_request"] ?? "0");
    if (
        $name === "" ||
        $firm === "" ||
        $mobile === "" ||
        $city === "" ||
        $state === "" ||
        $password === ""
    ) {
        apiError(
            400,
            "name, firm_name, mobile, city_state, state_code and password are required",
        );
    }
    if ($email !== "" && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        apiError(400, "Invalid email address");
    }
    $token = bin2hex(random_bytes(32));
    $created = date("Y-m-d H:i:s");
    $hash = legacyPasswordHash($password);
    $s = $db->prepare(
        "INSERT INTO members (name,firm_name,mobile,email_id,city_state,state_code,password,created_date,status,activation_token,expiry_time,max_request,pending_request) VALUES (?,?,?,?,?,?,?,?,?,?,NULL,?,?)",
    );
    $s->bind_param(
        "ssssssssssss",
        $name,
        $firm,
        $mobile,
        $email,
        $city,
        $state,
        $hash,
        $created,
        $status,
        $token,
        $max,
        $pending,
    );
    if (!$s->execute()) {
        apiError(500, "Failed to create customer");
    }
    apiSuccess(["member_id" => $s->insert_id], "Customer created", 201);
}
if ($method === "PUT" || $method === "PATCH") {
    $in = requestInput();
    $id = isset($_GET["id"])
        ? (int) $_GET["id"]
        : (int) ($in["member_id"] ?? ($in["id"] ?? 0));
    if ($id <= 0 || !customerExists($db, $id)) {
        apiError(404, "Customer not found");
    }
    if (($in["action"] ?? "") === "status") {
        if (!array_key_exists("status", $in)) {
            apiError(400, "status is required");
        }
        $st = (string) $in["status"];
        $s = $db->prepare("UPDATE members SET status=? WHERE member_id=?");
        $s->bind_param("si", $st, $id);
        $s->execute();
        apiSuccess(
            ["member_id" => $id, "status" => $st],
            "Customer status updated",
        );
    }
    $map = [
        "name",
        "firm_name",
        "mobile",
        "city_state",
        "state_code",
        "status",
        "max_request",
        "pending_request",
    ];
    $fields = [];
    $params = [];
    $types = "";
    foreach ($map as $k) {
        if (array_key_exists($k, $in)) {
            $fields[] = "$k=?";
            $params[] = trim((string) $in[$k]);
            $types .= "s";
        }
    }
    if (array_key_exists("email", $in) || array_key_exists("email_id", $in)) {
        $v = trim((string) ($in["email"] ?? $in["email_id"]));
        if ($v !== "" && !filter_var($v, FILTER_VALIDATE_EMAIL)) {
            apiError(400, "Invalid email address");
        }
        $fields[] = "email_id=?";
        $params[] = $v;
        $types .= "s";
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
        "UPDATE members SET " . implode(", ", $fields) . " WHERE member_id=?";
    $params[] = $id;
    $types .= "i";
    $s = $db->prepare($sql);
    bindDynamicParams($s, $types, $params);
    $s->execute();
    apiSuccess(["member_id" => $id], "Customer updated");
}
apiError(405, "Method not allowed");
