<?php
session_start();
require_once '../config/db.php';
require_once '../includes/auth.php';
requireLogin();
$pageTitle = 'Move History';

// Filters
$productFilter   = intval($_GET['product_id'] ?? 0);
$warehouseFilter = intval($_GET['warehouse_id'] ?? 0);
$typeFilter      = $_GET['type'] ?? '';

$sql = "SELECT sl.*, p.name as product_name, p.sku, p.unit, w.name as warehouse_name, u.name as user_name, o.type as op_type
        FROM stock_ledger sl
        JOIN products p ON sl.product_id = p.id
        JOIN warehouses w ON sl.warehouse_id = w.id
        LEFT JOIN users u ON sl.created_by = u.id
        LEFT JOIN operations o ON sl.operation_id = o.id
        WHERE 1=1";
if ($productFilter)   $sql .= " AND sl.product_id = $productFilter";
if ($warehouseFilter) $sql .= " AND sl.warehouse_id = $warehouseFilter";
if ($typeFilter)      $sql .= " AND o.type = '".mysqli_real_escape_string($conn,$typeFilter)."'";
$sql .= " ORDER BY sl.created_at DESC LIMIT 200";

$ledger     = $conn->query($sql);
$products   = $conn->query("SELECT id, name, sku FROM products ORDER BY name");
$warehouses = $conn->query("SELECT * FROM warehouses ORDER BY name");
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<div class="p-6 space-y-5">
    <div>
        <h1 class="text-2xl font-bold text-white">Move History</h1>
        <p class="text-slate-500 text-sm mt-0.5">Full stock ledger — every movement logged</p>
    </div>

    <!-- Filters -->
    <form method="GET" class="flex gap-3 flex-wrap">
        <select name="product_id" class="input-field" style="max-width:220px;">
            <option value="">All Products</option>
            <?php while ($p = $products->fetch_assoc()): ?>
            <option value="<?= $p['id'] ?>" <?= $productFilter==$p['id']?'selected':'' ?>><?= htmlspecialchars($p['name']) ?></option>
            <?php endwhile; ?>
        </select>
        <select name="warehouse_id" class="input-field" style="max-width:200px;">
            <option value="">All Warehouses</option>
            <?php while ($wh = $warehouses->fetch_assoc()): ?>
            <option value="<?= $wh['id'] ?>" <?= $warehouseFilter==$wh['id']?'selected':'' ?>><?= htmlspecialchars($wh['name']) ?></option>
            <?php endwhile; ?>
        </select>
        <select name="type" class="input-field" style="max-width:160px;">
            <option value="">All Types</option>
            <option value="receipt"    <?= $typeFilter==='receipt'?'selected':''    ?>>Receipt</option>
            <option value="delivery"   <?= $typeFilter==='delivery'?'selected':''   ?>>Delivery</option>
            <option value="transfer"   <?= $typeFilter==='transfer'?'selected':''   ?>>Transfer</option>
            <option value="adjustment" <?= $typeFilter==='adjustment'?'selected':'' ?>>Adjustment</option>
        </select>
        <button type="submit" class="btn-secondary">Filter</button>
        <a href="/coreinventory/pages/move_history.php" class="btn-secondary">Clear</a>
    </form>

    <div class="card overflow-hidden">
        <table>
            <thead>
                <tr>
                    <th>Date & Time</th>
                    <th>Product</th>
                    <th>Warehouse</th>
                    <th>Type</th>
                    <th>Change</th>
                    <th>Balance After</th>
                    <th>Reason</th>
                    <th>By</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $count = 0;
            while ($row = $ledger->fetch_assoc()):
                $count++;
                $changeColor = $row['change_qty'] >= 0 ? 'text-green-400' : 'text-red-400';
                $changePrefix = $row['change_qty'] >= 0 ? '+' : '';
                $typeColors = [
                    'receipt'    => 'text-purple-400',
                    'delivery'   => 'text-orange-400',
                    'transfer'   => 'text-blue-400',
                    'adjustment' => 'text-yellow-400',
                ];
                $typeColor = $typeColors[$row['op_type'] ?? ''] ?? 'text-slate-400';
            ?>
            <tr>
                <td class="text-slate-400 text-xs whitespace-nowrap"><?= date('M j, Y H:i', strtotime($row['created_at'])) ?></td>
                <td>
                    <p class="text-sm font-medium text-white"><?= htmlspecialchars($row['product_name']) ?></p>
                    <p class="text-xs text-slate-500 font-mono"><?= htmlspecialchars($row['sku']) ?></p>
                </td>
                <td class="text-slate-400 text-sm"><?= htmlspecialchars($row['warehouse_name']) ?></td>
                <td class="<?= $typeColor ?> text-xs font-medium capitalize"><?= htmlspecialchars($row['op_type'] ?? 'manual') ?></td>
                <td class="font-bold font-mono <?= $changeColor ?>"><?= $changePrefix.number_format($row['change_qty'],2) ?> <?= $row['unit'] ?></td>
                <td class="font-mono text-sm text-slate-300"><?= number_format($row['balance_after'],2) ?> <?= $row['unit'] ?></td>
                <td class="text-slate-500 text-xs max-w-xs truncate"><?= htmlspecialchars($row['reason']) ?></td>
                <td class="text-slate-500 text-xs"><?= htmlspecialchars($row['user_name'] ?? '—') ?></td>
            </tr>
            <?php endwhile; ?>
            <?php if ($count === 0): ?>
            <tr><td colspan="8" class="text-center text-slate-600 py-10">No stock movements recorded yet</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if ($count === 200): ?>
    <p class="text-xs text-slate-600 text-center">Showing latest 200 entries. Use filters to narrow down.</p>
    <?php endif; ?>
</div>
</main>
</body>
</html>
