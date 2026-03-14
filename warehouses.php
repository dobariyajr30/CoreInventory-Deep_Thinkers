<?php
session_start();
require_once '../config/db.php';
require_once '../includes/auth.php';
requireLogin();
requireRole('admin');
$pageTitle = 'Warehouses';

$success = $_SESSION['success'] ?? ''; unset($_SESSION['success']);
$error   = $_SESSION['error']   ?? ''; unset($_SESSION['error']);

// Handle Add
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'create') {
        $name     = trim($_POST['name'] ?? '');
        $location = trim($_POST['location'] ?? '');
        if (!$name) {
            $_SESSION['error'] = 'Warehouse name is required.';
        } else {
            $stmt = $conn->prepare("INSERT INTO warehouses (name, location) VALUES (?,?)");
            $stmt->bind_param("ss", $name, $location);
            $stmt->execute();
            $_SESSION['success'] = "Warehouse '$name' created successfully.";
        }
        header('Location: /coreinventory/pages/warehouses.php'); exit;
    }

    if ($_POST['action'] === 'delete') {
        $id = intval($_POST['warehouse_id']);
        // Check if warehouse has stock
        $hasStock = $conn->query("SELECT COUNT(*) as c FROM stock WHERE warehouse_id=$id AND quantity > 0")->fetch_assoc()['c'];
        if ($hasStock > 0) {
            $_SESSION['error'] = 'Cannot delete warehouse — it still has stock. Transfer stock out first.';
        } else {
            $conn->query("DELETE FROM warehouses WHERE id=$id");
            $_SESSION['success'] = 'Warehouse deleted.';
        }
        header('Location: /coreinventory/pages/warehouses.php'); exit;
    }
}

$warehouses = $conn->query("
    SELECT w.*,
        COUNT(DISTINCT s.product_id) as product_count,
        COALESCE(SUM(s.quantity), 0) as total_stock
    FROM warehouses w
    LEFT JOIN stock s ON w.id = s.warehouse_id AND s.quantity > 0
    GROUP BY w.id
    ORDER BY w.created_at ASC
");
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<div class="p-6 space-y-5">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Warehouses</h1>
            <p class="text-slate-500 text-sm mt-0.5">Manage warehouse locations</p>
        </div>
        <button onclick="document.getElementById('addModal').classList.remove('hidden')" class="btn-primary flex items-center gap-2">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add Warehouse
        </button>
    </div>

    <?php if ($success): ?>
    <div class="p-3 rounded-lg text-sm" style="background:#052e16;color:#4ade80;border:1px solid #166534;"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
    <div class="p-3 rounded-lg text-sm" style="background:#1f0a0a;color:#f87171;border:1px solid #7f1d1d;"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- Warehouse Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <?php while ($wh = $warehouses->fetch_assoc()): ?>
        <div class="card p-5">
            <div class="flex items-start justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background:#22a36a22;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#22a36a" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                    </div>
                    <div>
                        <p class="font-semibold text-white"><?= htmlspecialchars($wh['name']) ?></p>
                        <p class="text-xs text-slate-500"><?= htmlspecialchars($wh['location'] ?: 'No location set') ?></p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 mb-4">
                <div class="p-3 rounded-lg" style="background:#1c1f24;">
                    <p class="text-xs text-slate-500 mb-1">Products</p>
                    <p class="text-xl font-bold text-blue-400"><?= $wh['product_count'] ?></p>
                </div>
                <div class="p-3 rounded-lg" style="background:#1c1f24;">
                    <p class="text-xs text-slate-500 mb-1">Total Units</p>
                    <p class="text-xl font-bold text-green-400"><?= number_format($wh['total_stock']) ?></p>
                </div>
            </div>

            <form method="POST" onsubmit="return confirm('Delete this warehouse? This cannot be undone.')">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="warehouse_id" value="<?= $wh['id'] ?>">
                <button type="submit" class="btn-danger w-full text-sm py-2">Delete Warehouse</button>
            </form>
        </div>
        <?php endwhile; ?>
    </div>

</div>

<!-- Add Warehouse Modal -->
<div id="addModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,.7);">
    <div class="card w-full max-w-md p-6">
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-lg font-bold text-white">Add New Warehouse</h2>
            <button onclick="document.getElementById('addModal').classList.add('hidden')" class="text-slate-500 hover:text-white">✕</button>
        </div>
        <form method="POST" class="space-y-4">
            <input type="hidden" name="action" value="create">
            <div>
                <label class="block text-sm text-slate-400 mb-1.5">Warehouse Name *</label>
                <input type="text" name="name" class="input-field" placeholder="e.g. Main Warehouse" required>
            </div>
            <div>
                <label class="block text-sm text-slate-400 mb-1.5">Location</label>
                <input type="text" name="location" class="input-field" placeholder="e.g. Building A, Ground Floor">
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary flex-1">Create Warehouse</button>
                <button type="button" onclick="document.getElementById('addModal').classList.add('hidden')" class="btn-secondary flex-1">Cancel</button>
            </div>
        </form>
    </div>
</div>

</main>
</body>
</html>