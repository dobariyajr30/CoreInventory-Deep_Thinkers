<?php
session_start();
require_once '../config/db.php';
require_once '../includes/auth.php';
requireLogin();
$pageTitle = 'Dashboard';

// KPIs
$totalProducts = $conn->query("SELECT COUNT(*) as c FROM products")->fetch_assoc()['c'];
$totalStock    = $conn->query("SELECT COALESCE(SUM(quantity),0) as c FROM stock")->fetch_assoc()['c'];
$lowStock      = $conn->query("SELECT COUNT(DISTINCT p.id) as c FROM products p JOIN stock s ON p.id=s.product_id WHERE s.quantity <= p.reorder_level AND s.quantity > 0")->fetch_assoc()['c'];
$outOfStock    = $conn->query("SELECT COUNT(DISTINCT p.id) as c FROM products p LEFT JOIN stock s ON p.id=s.product_id WHERE COALESCE(s.quantity,0)=0")->fetch_assoc()['c'];
$pendingReceipts   = $conn->query("SELECT COUNT(*) as c FROM operations WHERE type='receipt' AND status IN ('draft','waiting','ready')")->fetch_assoc()['c'];
$pendingDeliveries = $conn->query("SELECT COUNT(*) as c FROM operations WHERE type='delivery' AND status IN ('draft','waiting','ready')")->fetch_assoc()['c'];

// Recent operations
$recentOps = $conn->query("SELECT o.*, w1.name as from_wh, w2.name as to_wh FROM operations o LEFT JOIN warehouses w1 ON o.from_warehouse_id=w1.id LEFT JOIN warehouses w2 ON o.to_warehouse_id=w2.id ORDER BY o.created_at DESC LIMIT 8");

// Low stock products
$lowStockProducts = $conn->query("SELECT p.name, p.sku, p.reorder_level, p.unit, COALESCE(SUM(s.quantity),0) as qty FROM products p LEFT JOIN stock s ON p.id=s.product_id GROUP BY p.id HAVING qty <= p.reorder_level ORDER BY qty ASC LIMIT 5");
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<div class="p-6 space-y-6">
    <!-- Page Title -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Dashboard</h1>
            <p class="text-slate-500 text-sm mt-0.5"><?= date('l, F j, Y') ?></p>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
        <?php
        $kpis = [
            ['Total Products',    $totalProducts,    '#22a36a', '<path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>'],
            ['Total Stock Units', number_format($totalStock), '#60a5fa', '<polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>'],
            ['Low Stock',         $lowStock,         '#facc15', '<path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>'],
            ['Out of Stock',      $outOfStock,       '#f87171', '<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>'],
            ['Pending Receipts',  $pendingReceipts,  '#a78bfa', '<polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"/>'],
            ['Pending Deliveries',$pendingDeliveries,'#fb923c', '<polyline points="8 16 12 12 16 16"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"/>'],
        ];
        foreach ($kpis as [$label, $value, $color, $svgPath]): ?>
        <div class="card p-4">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs text-slate-500"><?= $label ?></p>
                <div class="w-7 h-7 rounded-lg flex items-center justify-center" style="background:<?= $color ?>22;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="<?= $color ?>" stroke-width="2"><?= $svgPath ?></svg>
                </div>
            </div>
            <p class="text-2xl font-bold" style="color:<?= $color ?>"><?= $value ?></p>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent Operations -->
        <div class="card lg:col-span-2">
            <div class="flex items-center justify-between p-5 border-b border-dark-700" style="border-color:#1c1f24;">
                <h2 class="font-semibold text-white">Recent Operations</h2>
                <a href="/coreinventory/pages/move_history.php" class="text-xs text-green-400 hover:underline">View all</a>
            </div>
            <div class="overflow-x-auto">
                <table>
                    <thead><tr><th>Reference</th><th>Type</th><th>Status</th><th>Date</th></tr></thead>
                    <tbody>
                    <?php while ($op = $recentOps->fetch_assoc()): ?>
                        <tr>
                            <td class="font-mono text-xs text-slate-300"><?= htmlspecialchars($op['reference'] ?: '#'.$op['id']) ?></td>
                            <td><span class="capitalize text-slate-300"><?= $op['type'] ?></span></td>
                            <td><span class="badge-<?= $op['status'] ?> px-2 py-0.5 rounded-full text-xs font-medium capitalize"><?= $op['status'] ?></span></td>
                            <td class="text-slate-500 text-xs"><?= date('M j, H:i', strtotime($op['created_at'])) ?></td>
                        </tr>
                    <?php endwhile; ?>
                    <?php if ($recentOps->num_rows === 0): ?><tr><td colspan="4" class="text-center text-slate-600 py-8">No operations yet</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Low Stock Alert -->
        <div class="card">
            <div class="p-5 border-b" style="border-color:#1c1f24;">
                <h2 class="font-semibold text-white flex items-center gap-2">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#facc15" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    Low Stock Alerts
                </h2>
            </div>
            <div class="p-4 space-y-3">
                <?php while ($item = $lowStockProducts->fetch_assoc()): ?>
                <div class="flex items-center justify-between p-3 rounded-lg" style="background:#1c1f24;">
                    <div>
                        <p class="text-sm font-medium text-white"><?= htmlspecialchars($item['name']) ?></p>
                        <p class="text-xs text-slate-500 font-mono"><?= $item['sku'] ?></p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold <?= $item['qty'] == 0 ? 'text-red-400' : 'text-yellow-400' ?>"><?= $item['qty'] ?> <?= $item['unit'] ?></p>
                        <p class="text-xs text-slate-600">min: <?= $item['reorder_level'] ?></p>
                    </div>
                </div>
                <?php endwhile; ?>
                <?php if ($lowStock == 0 && $outOfStock == 0): ?>
                <div class="text-center py-8">
                    <p class="text-green-400 font-medium text-sm">✓ All stock levels healthy</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

</main>
</body>
</html>
