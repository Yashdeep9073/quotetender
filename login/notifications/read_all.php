<?php
session_start();
require_once __DIR__ . '/../db/config.php';

if (!isset($_SESSION["login_user_id"])) {
    header("Location: ../index.php");
    exit();
}

$userId = (int)$_SESSION['login_user_id'];
$stmt = $db->prepare("UPDATE notifications SET is_read = 1, read_at = NOW() WHERE user_id = ? AND is_read = 0");
$stmt->bind_param('i', $userId);
$stmt->execute();

$redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../dashboard.php';
header("Location: " . $redirect);
exit();
