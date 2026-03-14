<?php
session_start();
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: /coreinventory/register.php'); exit; }

$name     = trim($_POST['name'] ?? '');
$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$role     = in_array($_POST['role'], ['admin','manager','staff']) ? $_POST['role'] : 'staff';

if (!$name || !$email || !$password) {
    $_SESSION['reg_error'] = 'All fields are required.';
    header('Location: /coreinventory/register.php'); exit;
}

// Check duplicate
$check = $conn->prepare("SELECT id FROM users WHERE email = ?");
$check->bind_param("s", $email);
$check->execute();
if ($check->get_result()->num_rows > 0) {
    $_SESSION['reg_error'] = 'Email already registered.';
    header('Location: /coreinventory/register.php'); exit;
}

$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?,?,?,?)");
$stmt->bind_param("ssss", $name, $email, $hash, $role);
$stmt->execute();

$_SESSION['user_id']   = $conn->insert_id;
$_SESSION['user_name'] = $name;
$_SESSION['user_role'] = $role;
header('Location: /coreinventory/pages/dashboard.php');
exit;
