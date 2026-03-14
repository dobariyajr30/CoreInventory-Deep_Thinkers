<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: /coreinventory/pages/dashboard.php');
    exit;
}
$error = $_SESSION['login_error'] ?? '';
unset($_SESSION['login_error']);
$success = $_SESSION['login_success'] ?? '';
unset($_SESSION['login_success']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CoreInventory – Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Space Grotesk', sans-serif; background: #0d0f12; color: #e2e8f0; }
        .input-field { background:#1c1f24; border:1px solid #2e333c; color:#e2e8f0; border-radius:8px; padding:10px 14px; width:100%; outline:none; transition: border-color .2s; }
        .input-field:focus { border-color:#22a36a; }
        .btn-primary { background:#22a36a; color:#fff; padding:11px 18px; border-radius:8px; font-weight:600; width:100%; transition: background .2s; cursor:pointer; border:none; font-size:15px; }
        .btn-primary:hover { background:#178455; }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen">
    <div class="w-full max-w-md px-4">
        <!-- Logo -->
        <div class="flex items-center gap-3 mb-10 justify-center">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:#22a36a;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
            </div>
            <span class="font-bold text-white text-xl tracking-tight">CoreInventory</span>
        </div>

        <div style="background:#141719; border:1px solid #252930; border-radius:16px; padding:32px;">
            <h1 class="text-xl font-bold text-white mb-1">Welcome back</h1>
            <p class="text-slate-500 text-sm mb-6">Sign in to your inventory dashboard</p>
            <?php if ($success): ?>
<div class="mb-4 p-3 rounded-lg text-sm" style="background:#052e16;color:#4ade80;border:1px solid #166534;">
    <?= htmlspecialchars($success) ?>
</div>
<?php endif; ?>
            <?php if ($error): ?>
                <div class="mb-4 p-3 rounded-lg text-sm" style="background:#1f0a0a; color:#f87171; border:1px solid #7f1d1d;">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form action="/coreinventory/actions/login_action.php" method="POST" class="space-y-4">
                <div>
                    <label class="block text-sm text-slate-400 mb-1.5">Email Address</label>
                    <input type="email" name="email" class="input-field" placeholder="you@company.com" required>
                </div>
                <div>
                    <label class="block text-sm text-slate-400 mb-1.5">Password</label>
                    <input type="password" name="password" class="input-field" placeholder="••••••••" required>
                </div>
                <button type="submit" class="btn-primary mt-2">Sign In</button>
            </form>

            <p class="text-center text-sm text-slate-500 mt-5">
                Don't have an account? <a href="/coreinventory/register.php" class="text-green-400 hover:underline">Register</a>
            </p>
        </div>
        <p class="text-center text-sm text-slate-500 mt-4">
    <a href="/coreinventory/forgot_password.php" class="text-yellow-400 hover:underline">Forgot password?</a>
</p>

<p class="text-center text-xs text-slate-700 mt-6">Default: admin@coreinventory.com / password</p>
        <p class="text-center text-xs text-slate-700 mt-6">Default: admin@coreinventory.com / password</p>
    </div>
</body>
</html>
