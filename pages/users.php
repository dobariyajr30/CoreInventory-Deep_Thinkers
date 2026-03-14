<?php
session_start();
require_once '../config/db.php';
require_once '../includes/auth.php';
requireLogin();
requireRole('admin');
$pageTitle = 'Manage Users';

$success = $_SESSION['success'] ?? ''; unset($_SESSION['success']);
$error   = $_SESSION['error']   ?? ''; unset($_SESSION['error']);

// Handle role change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_role'])) {
    $uid  = intval($_POST['user_id']);
    $role = in_array($_POST['role'], ['admin','manager','staff']) ? $_POST['role'] : 'staff';
    $conn->query("UPDATE users SET role='$role' WHERE id=$uid");
    $_SESSION['success'] = 'User role updated.';
    header('Location: /coreinventory/pages/users.php'); exit;
}

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $uid = intval($_POST['user_id']);
    $me  = currentUser();
    if ($uid == $me['id']) { $_SESSION['error'] = 'You cannot delete yourself.'; }
    else { $conn->query("DELETE FROM users WHERE id=$uid"); $_SESSION['success'] = 'User deleted.'; }
    header('Location: /coreinventory/pages/users.php'); exit;
}

$users = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<div class="p-6 space-y-5">
    <div>
        <h1 class="text-2xl font-bold text-white">Manage Users</h1>
        <p class="text-slate-500 text-sm mt-0.5">Control who has access and what they can do</p>
    </div>

    <?php if ($success): ?><div class="p-3 rounded-lg text-sm" style="background:#052e16;color:#4ade80;border:1px solid #166534;"><?=htmlspecialchars($success)?></div><?php endif; ?>
    <?php if ($error):   ?><div class="p-3 rounded-lg text-sm" style="background:#1f0a0a;color:#f87171;border:1px solid #7f1d1d;"><?=htmlspecialchars($error)?></div><?php endif; ?>

    <!-- Role legend -->
    <div class="flex gap-4 flex-wrap">
        <?php foreach ([['admin','#22a36a','Full access — products, validate, warehouses, users'],['manager','#60a5fa','Can validate operations & adjustments, cannot manage users'],['staff','#94a3b8','View & create operations only — cannot validate or adjust']] as [$r,$c,$desc]): ?>
        <div class="card px-4 py-3 flex items-center gap-3" style="min-width:220px;">
            <span class="text-xs font-bold px-2 py-0.5 rounded-full" style="background:<?=$c?>22;color:<?=$c?>;"><?=strtoupper($r)?></span>
            <p class="text-xs text-slate-400"><?=$desc?></p>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="card overflow-hidden">
        <table>
            <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Joined</th><th>Actions</th></tr></thead>
            <tbody>
            <?php $me = currentUser(); while ($u = $users->fetch_assoc()): ?>
            <tr>
                <td class="font-medium text-white flex items-center gap-2">
                    <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0" style="background:#22a36a22;color:#22a36a;"><?=strtoupper(substr($u['name'],0,1))?></div>
                    <?=htmlspecialchars($u['name'])?>
                    <?php if ($u['id']==$me['id']): ?><span class="text-xs text-slate-600">(you)</span><?php endif; ?>
                </td>
                <td class="text-slate-400 text-sm"><?=htmlspecialchars($u['email'])?></td>
                <td>
                    <?php if ($u['id'] != $me['id']): ?>
                    <form method="POST" style="display:inline-flex;gap:6px;align-items:center;">
                        <input type="hidden" name="user_id" value="<?=$u['id']?>">
                        <input type="hidden" name="change_role" value="1">
                        <select name="role" class="input-field text-xs py-1" style="max-width:110px;" onchange="this.form.submit()">
                            <option value="admin"   <?=$u['role']==='admin'  ?'selected':''?>>Admin</option>
                            <option value="manager" <?=$u['role']==='manager'?'selected':''?>>Manager</option>
                            <option value="staff"   <?=$u['role']==='staff'  ?'selected':''?>>Staff</option>
                        </select>
                    </form>
                    <?php else: ?>
                    <?php $rc=['admin'=>'#22a36a','manager'=>'#60a5fa','staff'=>'#94a3b8'][$u['role']]??'#94a3b8'; ?>
                    <span class="text-xs font-bold px-2 py-0.5 rounded-full" style="background:<?=$rc?>22;color:<?=$rc?>;"><?=strtoupper($u['role'])?></span>
                    <?php endif; ?>
                </td>
                <td class="text-slate-500 text-xs"><?=date('M j, Y',strtotime($u['created_at']))?></td>
                <td>
                    <?php if ($u['id'] != $me['id']): ?>
                    <form method="POST" onsubmit="return confirm('Delete this user?')">
                        <input type="hidden" name="user_id" value="<?=$u['id']?>">
                        <input type="hidden" name="delete_user" value="1">
                        <button type="submit" class="btn-danger text-xs px-3 py-1.5">Delete</button>
                    </form>
                    <?php else: ?>
                    <span class="text-xs text-slate-600">—</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
</main>
</body>
</html>
