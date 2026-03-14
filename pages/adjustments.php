<?php
session_start();
require_once '../config/db.php';
require_once '../includes/auth.php';
requireLogin();
$pageTitle = 'Adjustments';
$success = $_SESSION['success'] ?? ''; unset($_SESSION['success']);
$error   = $_SESSION['error']   ?? ''; unset($_SESSION['error']);

$adjustments = $conn->query("SELECT o.*, w.name as warehouse_name, COUNT(oi.id) as item_count FROM operations o LEFT JOIN warehouses w ON o.from_warehouse_id=w.id LEFT JOIN operation_items oi ON o.id=oi.operation_id WHERE o.type='adjustment' GROUP BY o.id ORDER BY o.created_at DESC");
$products    = $conn->query("SELECT p.id, p.name, p.sku, p.unit FROM products p ORDER BY p.name");
$warehouses  = $conn->query("SELECT * FROM warehouses ORDER BY name");
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<div class="p-6 space-y-5">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Stock Adjustments</h1>
            <p class="text-slate-500 text-sm mt-0.5">Fix mismatches between recorded and physical stock</p>
        </div>
        <button onclick="document.getElementById('addModal').classList.remove('hidden')" class="btn-primary flex items-center gap-2">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            New Adjustment
        </button>
    </div>

    <?php if ($success): ?><div class="p-3 rounded-lg text-sm" style="background:#052e16; color:#4ade80; border:1px solid #166534;"><?= htmlspecialchars($success) ?></div><?php endif; ?>
    <?php if ($error):   ?><div class="p-3 rounded-lg text-sm" style="background:#1f0a0a; color:#f87171; border:1px solid #7f1d1d;"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <div class="card overflow-hidden">
        <table>
            <thead><tr><th>Reference</th><th>Warehouse</th><th>Items</th><th>Reason</th><th>Status</th><th>Date</th></tr></thead>
            <tbody>
            <?php while ($a = $adjustments->fetch_assoc()): ?>
            <tr>
                <td class="font-mono text-xs text-slate-300"><?= htmlspecialchars($a['reference'] ?: 'ADJ-'.$a['id']) ?></td>
                <td class="text-slate-300"><?= htmlspecialchars($a['warehouse_name'] ?? '—') ?></td>
                <td class="text-slate-400"><?= $a['item_count'] ?> items</td>
                <td class="text-slate-400 text-sm"><?= htmlspecialchars($a['notes'] ?: '—') ?></td>
                <td><span class="badge-<?= $a['status'] ?> px-2 py-0.5 rounded-full text-xs font-medium capitalize"><?= $a['status'] ?></span></td>
                <td class="text-slate-500 text-xs"><?= date('M j, Y', strtotime($a['created_at'])) ?></td>
            </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- New Adjustment Modal -->
<div id="addModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,.7);">
    <div class="card w-full max-w-2xl p-6 max-h-screen overflow-y-auto">
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-lg font-bold text-white">New Stock Adjustment</h2>
            <button onclick="document.getElementById('addModal').classList.add('hidden')" class="text-slate-500 hover:text-white">✕</button>
        </div>
        <p class="text-sm text-slate-500 mb-4">Enter the <strong class="text-slate-300">actual counted quantity</strong>. The system will calculate and log the difference automatically.</p>
        <form action="/coreinventory/actions/adjustment_action.php" method="POST" class="space-y-4">
            <input type="hidden" name="action" value="create">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-slate-400 mb-1.5">Reference #</label>
                    <input type="text" name="reference" class="input-field" placeholder="e.g. ADJ-2024-001">
                </div>
                <div>
                    <label class="block text-sm text-slate-400 mb-1.5">Warehouse *</label>
                    <select name="warehouse_id" class="input-field" required>
                        <?php while ($wh = $warehouses->fetch_assoc()): ?>
                        <option value="<?= $wh['id'] ?>"><?= htmlspecialchars($wh['name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-sm text-slate-400 mb-2">Products & Actual Quantities *</label>
                <div id="lineItems" class="space-y-2">
                    <div class="flex gap-2 items-center">
                        <select name="product_id[]" class="input-field flex-1" required>
                            <option value="">— Select Product —</option>
                            <?php $products->data_seek(0); while ($p = $products->fetch_assoc()): ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?> (<?= $p['sku'] ?>)</option>
                            <?php endwhile; ?>
                        </select>
                        <input type="number" name="actual_qty[]" class="input-field" style="max-width:130px;" placeholder="Actual qty" min="0" step="0.01" required>
                        <button type="button" onclick="this.parentElement.remove()" class="text-red-400 hover:text-red-300 px-2">✕</button>
                    </div>
                </div>
                <button type="button" onclick="addLine()" class="mt-2 text-sm text-green-400 hover:underline">+ Add product</button>
            </div>
            <div>
                <label class="block text-sm text-slate-400 mb-1.5">Reason / Notes *</label>
                <textarea name="notes" class="input-field" rows="2" placeholder="e.g. Physical count, Damaged goods, Theft..." required></textarea>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary flex-1">Apply Adjustment</button>
                <button type="button" onclick="document.getElementById('addModal').classList.add('hidden')" class="btn-secondary flex-1">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
const productOptions = `<?php $products->data_seek(0); while ($p = $products->fetch_assoc()) echo '<option value="'.$p['id'].'">'.htmlspecialchars($p['name']).' ('.$p['sku'].')</option>'; ?>`;
function addLine() {
    const div = document.createElement('div');
    div.className = 'flex gap-2 items-center';
    div.innerHTML = `<select name="product_id[]" class="input-field flex-1" required><option value="">— Select Product —</option>${productOptions}</select><input type="number" name="actual_qty[]" class="input-field" style="max-width:130px;" placeholder="Actual qty" min="0" step="0.01" required><button type="button" onclick="this.parentElement.remove()" class="text-red-400 hover:text-red-300 px-2">✕</button>`;
    document.getElementById('lineItems').appendChild(div);
}
</script>
</main>
</body>
</html>
