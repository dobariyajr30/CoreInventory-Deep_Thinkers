<?php
session_start();
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /coreinventory/login.php'); exit;
}

$action = $_POST['action'] ?? '';

// ── SEND OTP ─────────────────────────────────────────────
if ($action === 'send_otp') {
    $email = trim($_POST['email'] ?? '');

    // Check email exists in DB
    $stmt = $conn->prepare("SELECT id, name FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if (!$user) {
        $_SESSION['fp_error'] = 'No account found with this email address.';
        header('Location: /coreinventory/forgot_password.php'); exit;
    }

    // Generate 6-digit OTP
    $otp = strval(rand(100000, 999999));

    // Store in session
    $_SESSION['otp_code']    = $otp;
    $_SESSION['otp_email']   = $email;
    $_SESSION['otp_user_id'] = $user['id'];
    $_SESSION['otp_expiry']  = time() + 300; // 5 minutes
    unset($_SESSION['otp_verified']);

    header('Location: /coreinventory/verify_otp.php'); exit;
}

// ── VERIFY OTP ───────────────────────────────────────────
if ($action === 'verify_otp') {
    // Combine 6 individual boxes into one string
    $entered = trim(
        ($_POST['otp1'] ?? '') .
        ($_POST['otp2'] ?? '') .
        ($_POST['otp3'] ?? '') .
        ($_POST['otp4'] ?? '') .
        ($_POST['otp5'] ?? '') .
        ($_POST['otp6'] ?? '')
    );

    // Check expiry
    if (time() > ($_SESSION['otp_expiry'] ?? 0)) {
        $_SESSION['otp_error'] = 'OTP has expired. Please request a new one.';
        unset($_SESSION['otp_code'], $_SESSION['otp_expiry']);
        header('Location: /coreinventory/verify_otp.php'); exit;
    }

    // Check OTP match
    if ($entered !== $_SESSION['otp_code']) {
        $_SESSION['otp_error'] = 'Invalid OTP. Please try again.';
        header('Location: /coreinventory/verify_otp.php'); exit;
    }

    // OTP correct — mark as verified
    $_SESSION['otp_verified'] = true;
    unset($_SESSION['otp_code'], $_SESSION['otp_expiry']);

    header('Location: /coreinventory/reset_password.php'); exit;
}

// ── RESEND OTP ───────────────────────────────────────────
if ($action === 'resend_otp') {
    if (empty($_SESSION['otp_email'])) {
        header('Location: /coreinventory/forgot_password.php'); exit;
    }

    // Generate new OTP
    $otp = strval(rand(100000, 999999));
    $_SESSION['otp_code']   = $otp;
    $_SESSION['otp_expiry'] = time() + 300;
    unset($_SESSION['otp_verified']);

    $_SESSION['otp_success'] = 'A new OTP has been generated.';
    header('Location: /coreinventory/verify_otp.php'); exit;
}

// ── RESET PASSWORD ───────────────────────────────────────
if ($action === 'reset_password') {
    if (empty($_SESSION['otp_verified']) || empty($_SESSION['otp_email'])) {
        header('Location: /coreinventory/forgot_password.php'); exit;
    }

    $password         = $_POST['password']         ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (strlen($password) < 6) {
        $_SESSION['rp_error'] = 'Password must be at least 6 characters.';
        header('Location: /coreinventory/reset_password.php'); exit;
    }

    if ($password !== $confirm_password) {
        $_SESSION['rp_error'] = 'Passwords do not match.';
        header('Location: /coreinventory/reset_password.php'); exit;
    }

    $email = $_SESSION['otp_email'];
    $hash  = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
    $stmt->bind_param("ss", $hash, $email);
    $stmt->execute();

    // Clear all OTP session data
    unset($_SESSION['otp_code'], $_SESSION['otp_email'], $_SESSION['otp_user_id'], $_SESSION['otp_expiry'], $_SESSION['otp_verified']);

    $_SESSION['login_success'] = 'Password reset successfully! Please login with your new password.';
    header('Location: /coreinventory/login.php'); exit;
}

header('Location: /coreinventory/login.php');