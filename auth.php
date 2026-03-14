<?php
function requireLogin() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (!isset($_SESSION['user_id'])) {
        header('Location: /coreinventory/login.php');
        exit;
    }
}

function currentUser() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    return [
        'id'   => $_SESSION['user_id'] ?? null,
        'name' => $_SESSION['user_name'] ?? 'User',
        'role' => $_SESSION['user_role'] ?? 'staff',
    ];
}

// Check if current user has one of the allowed roles
function hasRole(...$roles) {
    $user = currentUser();
    return in_array($user['role'], $roles);
}

// Redirect with error if user doesn't have permission
function requireRole(...$roles) {
    if (!hasRole(...$roles)) {
        $_SESSION['error'] = 'You do not have permission to perform this action.';
        header('Location: /coreinventory/pages/dashboard.php');
        exit;
    }
}

// Return a "blocked" banner HTML for restricted sections
function accessDeniedBanner() {
    return '<div class="p-4 rounded-lg text-sm flex items-center gap-3" style="background:#1f0a0a; color:#f87171; border:1px solid #7f1d1d;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
        You do not have permission to perform this action. Contact your Admin.
    </div>';
}
?>
