<?php
require_once __DIR__ . "/_common.php";
requireApiAuth();
if (($_SERVER["REQUEST_METHOD"] ?? "GET") !== "GET") {
    apiError(405, "Method not allowed");
}
$status =
    isset($_GET["status"]) && $_GET["status"] !== ""
        ? (int) $_GET["status"]
        : 1;
$s = $db->prepare(
    "SELECT permission_id,permission_name,status,created_at FROM permissions WHERE status=? ORDER BY permission_name",
);
$s->bind_param("i", $status);
$s->execute();
apiSuccess($s->get_result()->fetch_all(MYSQLI_ASSOC));
