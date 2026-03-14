<?php
session_start();
$error   = $_SESSION['fp_error'] ?? ''; unset($_SESSION['fp_error']);
$success = $_SESSION['fp_success'] ?? ''; unset($_SESSION['fp_success']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CoreInventory – Forgot Password</title>
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
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
            </div>
            <span class="font-bold text-white text-xl tracking-tight">CoreInventory</span>
        </div>

        <div style="background:#141719; border:1px solid #252930; border-radius:16px; padding:32px;">
            <h1 class="text-xl font-bold text-white mb-1">Forgot Password</h1>
            <p class="text-slate-500 text-sm mb-6">Enter your registered email to receive an OTP</p>

            <?php if ($error): ?>
            <div class="mb-4 p-3 rounded-lg text-sm" style="background:#1f0a0a;color:#f87171;border:1px solid #7f1d1d;">
                <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

            <?php if ($success): ?>
            <div class="mb-4 p-3 rounded-lg text-sm" style="background:#052e16;color:#4ade80;border:1px solid #166534;">
                <?= htmlspecialchars($success) ?>
            </div>
            <?php endif; ?>

            <form action="/coreinventory/actions/otp_action.php" method="POST" class="space-y-4">
                <input type="hidden" name="action" value="send_otp">
                <div>
                    <label class="block text-sm text-slate-400 mb-1.5">Email Address</label>
                    <input type="email" name="email" class="input-field" placeholder="you@company.com" required>
                </div>
                <button type="submit" class="btn-primary">Send OTP</button>
            </form>

            <p class="text-center text-sm text-slate-500 mt-5">
                Remembered it? <a href="/coreinventory/login.php" class="text-green-400 hover:underline">Back to Login</a>
            </p>
        </div>
    </div>
</body>
</html>