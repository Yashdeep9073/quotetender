<?php
header("Content-Type: application/json; charset=utf-8");
require_once __DIR__ . "/../db/config.php";
$envPath = realpath(__DIR__ . "/../../env.php");
if ($envPath && file_exists($envPath)) {
    require_once $envPath;
}

function apiResponse(int $httpCode, array $payload): void
{
    http_response_code($httpCode);
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit();
}
function apiSuccess(
    $data = null,
    string $message = "Success",
    int $httpCode = 200,
): void {
    $payload = ["status" => $httpCode, "message" => $message];
    if ($data !== null) {
        $payload["data"] = $data;
    }
    apiResponse($httpCode, $payload);
}
function apiError(int $httpCode, string $message): void
{
    apiResponse($httpCode, ["status" => $httpCode, "error" => $message]);
}
function requestInput(): array
{
    $contentType = $_SERVER["CONTENT_TYPE"] ?? "";
    if (stripos($contentType, "application/json") !== false) {
        $raw = file_get_contents("php://input");
        if ($raw === false || trim($raw) === "") {
            return [];
        }
        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            apiError(400, "Invalid JSON body");
        }
        return $decoded;
    }
    if (!empty($_POST)) {
        return $_POST;
    }
    $raw = file_get_contents("php://input");
    if ($raw) {
        parse_str($raw, $parsed);
        if (is_array($parsed)) {
            return $parsed;
        }
    }
    return [];
}
function requireApiAuth(): void
{
    $token = $_GET["token"] ?? ($_SERVER["HTTP_X_API_TOKEN"] ?? "");
    $timestamp = $_GET["ts"] ?? ($_SERVER["HTTP_X_API_TIMESTAMP"] ?? "");
    $signature = $_GET["sig"] ?? ($_SERVER["HTTP_X_API_SIGNATURE"] ?? "");
    $expectedToken = (string) getenv("TOKEN");
    $secretKey = (string) getenv("SECRET_KEY");
    if ($expectedToken === "" || $secretKey === "") {
        apiError(500, "API authentication is not configured");
    }
    if ($token === "" || $timestamp === "" || $signature === "") {
        apiError(401, "Unauthorized");
    }
    if (
        !ctype_digit((string) $timestamp) ||
        abs(time() - (int) $timestamp) > 300
    ) {
        apiError(401, "Expired request");
    }
    if (!hash_equals($expectedToken, (string) $token)) {
        apiError(401, "Unauthorized");
    }
    $expectedSignature = hash_hmac(
        "sha256",
        (string) $token . (string) $timestamp,
        $secretKey,
    );
    if (!hash_equals($expectedSignature, (string) $signature)) {
        apiError(401, "Unauthorized");
    }
}
function clampInt($value, int $min, int $max, int $default): int
{
    if (!is_numeric($value)) {
        return $default;
    }
    return max($min, min($max, (int) $value));
}
function normalizedOrder(?string $order): string
{
    return strtoupper((string) $order) === "ASC" ? "ASC" : "DESC";
}
function legacyPasswordHash(string $password): string
{
    return md5($password);
}
function bindDynamicParams(
    mysqli_stmt $stmt,
    string $types,
    array $params,
): void {
    if ($types !== "") {
        $stmt->bind_param($types, ...$params);
    }
}
