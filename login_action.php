<?php
session_start();
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: /coreinventory/login.php'); exit; }

$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

$stmt = $conn->prepare("SELECT id, name, email, password, role FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if ($user && password_verify($password, $user['password'])) {
    $_SESSION['user_id']   = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_role'] = $user['role'];
    header('Location: /coreinventory/pages/dashboard.php');
} else {
    $_SESSION['login_error'] = 'Invalid email or password.';
    header('Location: /coreinventory/login.php');
}
exit;
