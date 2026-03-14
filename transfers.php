<?php
session_start();
require_once '../config/db.php';
require_once '../includes/auth.php';
requireLogin();
$pageTitle = 'Transfers';
$success = $_SESSION['success'] ?? ''; unset($_SESSION['success']);
$error   = $_SESSION['error']   ?? ''; unset($_SESSION['error']);

$transfers  = $conn->query("SELECT o.*, w1.name as from_wh, w2.name as to_wh, COUNT(oi.id) as item_count FROM operations o LEFT JOIN warehouses w1 ON o.from_warehouse_id=w1.id LEFT JOIN warehouses w2 ON o.to_warehouse_id=w2.id LEFT JOIN operation_items oi ON o.id=oi.operation_id WHERE o.type='transfer' GROUP BY o.id ORDER BY o.created_at DESC");
$products   = $conn->query("SELECT p.id, p.name, p.sku, p.unit, COALESCE(SUM(s.quantity),0) as stock FROM products p LEFT JOIN stock s ON p.id=s.product_id GROUP BY p.id ORDER BY p.name");
$warehouses = $conn->query("SELECT * FROM warehouses ORDER BY name");
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<div class="p-6 space-y-5">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Internal Transfers</h1>
            <p class="text-slate-500 text-sm mt-0.5">Move stock between warehouses or locations</p>
        </div>
        <button onclick="document.getElementById('addModal').classList.remove('hidden')" class="btn-primary flex items-center gap-2">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            New Transfer
        </button>
    </div>

    <?php if ($success): ?><div class="p-3 rounded-lg text-sm" style="background:#052e16; color:#4ade80; border:1px solid #166534;"><?= htmlspecialchars($success) ?></div><?php endif; ?>
    <?php if ($error):   ?><div class="p-3 rounded-lg text-sm" style="background:#1f0a0a; color:#f87171; border:1px solid #7f1d1d;"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <div class="card overflow-hidden">
        <table>
            <thead><tr><th>Reference</th><th>From</th><th>To</th><th>Items</th><th>Status</th><th>Date</th><th>Action</th></tr></thead>
            <tbody>
            <?php while ($t = $transfers->fetch_assoc()): ?>
            <tr>
                <td class="font-mono text-xs text-slate-300"><?= htmlspecialchars($t['reference'] ?: 'TRF-'.$t['id']) ?></td>
                <td class="text-slate-300"><?= htmlspecialchars($t['from_wh'] ?? '—') ?></td>
                <td class="text-slate-300"><?= htmlspecialchars($t['to_wh'] ?? '—') ?></td>
                <td class="text-slate-400"><?= $t['item_count'] ?> items</td>
                <td><span class="badge-<?= $t['status'] ?> px-2 py-0.5 rounded-full text-xs font-medium capitalize"><?= $t['status'] ?></span></td>
                <td class="text-slate-500 text-xs"><?= date('M j, Y', strtotime($t['created_at'])) ?></td>
                <td>
                    <?php if (hasRole('admin','manager') && $t['status'] !== 'done' && $t['status'] !== 'canceled'): ?>
                    <form method="POST" action="/coreinventory/actions/transfer_action.php" style="display:inline;">
                        <input type="hidden" name="action" value="validate">
                        <input type="hidden" name="operation_id" value="<?= $t['id'] ?>">
                        <button type="submit" class="btn-primary text-xs px-3 py-1.5">Validate</button>
                    </form>
                    <?php else: ?><span class="text-xs text-slate-600">—</span><?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- New Transfer Modal -->
<div id="addModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,.7);">
    <div class="card w-full max-w-2xl p-6 max-h-screen overflow-y-auto">
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-lg font-bold text-white">New Internal Transfer</h2>
            <button onclick="document.getElementById('addModal').classList.add('hidden')" class="text-slate-500 hover:text-white">✕</button>
        </div>
        <form action="/coreinventory/actions/transfer_action.php" method="POST" class="space-y-4">
            <input type="hidden" name="action" value="create">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-slate-400 mb-1.5">Reference #</label>
                    <input type="text" name="reference" class="input-field" placeholder="e.g. TRF-2024-001">
                </div>
                <div></div>
                <div>
                    <label class="block text-sm text-slate-400 mb-1.5">From Warehouse *</label>
                    <select name="from_warehouse_id" class="input-field" required>
                        <?php $warehouses->data_seek(0); while ($wh = $warehouses->fetch_assoc()): ?>
                        <option value="<?= $wh['id'] ?>"><?= htmlspecialchars($wh['name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-slate-400 mb-1.5">To Warehouse *</label>
                    <select name="to_warehouse_id" class="input-field" required>
                        <?php $warehouses->data_seek(0); while ($wh = $warehouses->fetch_assoc()): ?>
                        <option value="<?= $wh['id'] ?>"><?= htmlspecialchars($wh['name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-sm text-slate-400 mb-2">Products *</label>
                <div id="lineItems" class="space-y-2">
                    <div class="flex gap-2 items-center">
                        <select name="product_id[]" class="input-field flex-1" required>
                            <option value="">— Select Product —</option>
                            <?php $products->data_seek(0); while ($p = $products->fetch_assoc()): ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?> (<?= $p['sku'] ?>) — <?= $p['stock'] ?> avail.</option>
                            <?php endwhile; ?>
                        </select>
                        <input type="number" name="quantity[]" class="input-field" style="max-width:110px;" placeholder="Qty" min="0.01" step="0.01" required>
                        <button type="button" onclick="this.parentElement.remove()" class="text-red-400 hover:text-red-300 px-2">✕</button>
                    </div>
                </div>
                <button type="button" onclick="addLine()" class="mt-2 text-sm text-green-400 hover:underline">+ Add product</button>
            </div>
            <div>
                <label class="block text-sm text-slate-400 mb-1.5">Notes</label>
                <textarea name="notes" class="input-field" rows="2"></textarea>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary flex-1">Create Transfer</button>
                <button type="button" onclick="document.getElementById('addModal').classList.add('hidden')" class="btn-secondary flex-1">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
const productOptions = `<?php $products->data_seek(0); while ($p = $products->fetch_assoc()) echo '<option value="'.$p['id'].'">'.htmlspecialchars($p['name']).' ('.$p['sku'].') — '.$p['stock'].' avail.</option>'; ?>`;
function addLine() {
    const div = document.createElement('div');
    div.className = 'flex gap-2 items-center';
    div.innerHTML = `<select name="product_id[]" class="input-field flex-1" required><option value="">— Select Product —</option>${productOptions}</select><input type="number" name="quantity[]" class="input-field" style="max-width:110px;" placeholder="Qty" min="0.01" step="0.01" required><button type="button" onclick="this.parentElement.remove()" class="text-red-400 hover:text-red-300 px-2">✕</button>`;
    document.getElementById('lineItems').appendChild(div);
}
</script>
</main>
</body>
</html>
