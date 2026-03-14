<?php
session_start();
// Redirect if no OTP session exists
if (empty($_SESSION['otp_code']) || empty($_SESSION['otp_email'])) {
    header('Location: /coreinventory/forgot_password.php'); exit;
}
$error   = $_SESSION['otp_error']   ?? ''; unset($_SESSION['otp_error']);
$success = $_SESSION['otp_success'] ?? ''; unset($_SESSION['otp_success']);
$email   = $_SESSION['otp_email'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CoreInventory – Verify OTP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Space Grotesk', sans-serif; background: #0d0f12; color: #e2e8f0; }
        .input-field { background:#1c1f24; border:1px solid #2e333c; color:#e2e8f0; border-radius:8px; padding:10px 14px; width:100%; outline:none; transition: border-color .2s; }
        .input-field:focus { border-color:#22a36a; }
        .btn-primary { background:#22a36a; color:#fff; padding:11px 18px; border-radius:8px; font-weight:600; width:100%; transition: background .2s; cursor:pointer; border:none; font-size:15px; }
        .btn-primary:hover { background:#178455; }
        .btn-secondary { background:#252930; color:#e2e8f0; padding:11px 18px; border-radius:8px; font-weight:600; width:100%; transition: background .2s; cursor:pointer; border:none; font-size:15px; }
        .btn-secondary:hover { background:#2e333c; }
        /* OTP input boxes */
        .otp-box { background:#1c1f24; border:1px solid #2e333c; color:#e2e8f0; border-radius:8px; width:52px; height:56px; text-align:center; font-size:22px; font-weight:700; outline:none; transition: border-color .2s; }
        .otp-box:focus { border-color:#22a36a; }
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
            <h1 class="text-xl font-bold text-white mb-1">Enter OTP</h1>
            <p class="text-slate-500 text-sm mb-1">OTP sent to <span class="text-green-400"><?= htmlspecialchars($email) ?></span></p>
            <p class="text-slate-600 text-xs mb-6">OTP expires in <span id="timer" class="text-yellow-400 font-semibold">05:00</span></p>

            <!-- OTP Display Box (for demo — shows OTP on screen) -->
            <div class="mb-5 p-4 rounded-xl flex items-center gap-3" style="background:#0c1a2e; border:1px solid #1e3a5f;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#60a5fa" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                <div>
                    <p class="text-xs text-slate-500 mb-0.5">Your OTP (demo mode)</p>
                    <p class="text-2xl font-bold tracking-[0.3em] text-blue-400" id="otpDisplay"><?= $_SESSION['otp_code'] ?></p>
                </div>
            </div>

            <?php if ($error): ?>
            <div class="mb-4 p-3 rounded-lg text-sm flex items-center gap-2" style="background:#1f0a0a;color:#f87171;border:1px solid #7f1d1d;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

            <form action="/coreinventory/actions/otp_action.php" method="POST" class="space-y-5">
                <input type="hidden" name="action" value="verify_otp">

                <!-- 6 OTP digit boxes -->
                <div>
                    <label class="block text-sm text-slate-400 mb-3">Enter 6-digit OTP</label>
                    <div class="flex gap-2 justify-between" id="otpInputs">
                        <input class="otp-box" type="text" maxlength="1" name="otp1" id="otp1" inputmode="numeric" required>
                        <input class="otp-box" type="text" maxlength="1" name="otp2" id="otp2" inputmode="numeric" required>
                        <input class="otp-box" type="text" maxlength="1" name="otp3" id="otp3" inputmode="numeric" required>
                        <input class="otp-box" type="text" maxlength="1" name="otp4" id="otp4" inputmode="numeric" required>
                        <input class="otp-box" type="text" maxlength="1" name="otp5" id="otp5" inputmode="numeric" required>
                        <input class="otp-box" type="text" maxlength="1" name="otp6" id="otp6" inputmode="numeric" required>
                    </div>
                </div>

                <button type="submit" class="btn-primary">Verify OTP</button>
            </form>

            <form action="/coreinventory/actions/otp_action.php" method="POST" class="mt-3">
                <input type="hidden" name="action" value="resend_otp">
                <button type="submit" class="btn-secondary">Resend OTP</button>
            </form>

            <p class="text-center text-sm text-slate-500 mt-5">
                <a href="/coreinventory/forgot_password.php" class="text-green-400 hover:underline">← Change email</a>
            </p>
        </div>
    </div>

    <script>
        // Auto-focus next box
        const boxes = document.querySelectorAll('.otp-box');
        boxes.forEach((box, i) => {
            box.addEventListener('input', () => {
                if (box.value.length === 1 && i < boxes.length - 1) {
                    boxes[i + 1].focus();
                }
            });
            box.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && box.value === '' && i > 0) {
                    boxes[i - 1].focus();
                }
            });
        });

        // Countdown timer 5 minutes
        let seconds = 300;
        const timerEl = document.getElementById('timer');
        const interval = setInterval(() => {
            seconds--;
            const m = String(Math.floor(seconds / 60)).padStart(2, '0');
            const s = String(seconds % 60).padStart(2, '0');
            timerEl.textContent = `${m}:${s}`;
            if (seconds <= 0) {
                clearInterval(interval);
                timerEl.textContent = 'Expired';
                timerEl.style.color = '#f87171';
            }
        }, 1000);
    </script>
</body>
</html>