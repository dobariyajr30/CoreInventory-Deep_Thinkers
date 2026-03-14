<?php
session_start();
require_once '../config/db.php';
require_once '../includes/auth.php';
requireLogin();
$pageTitle = 'Products';

$success = $_SESSION['success'] ?? ''; unset($_SESSION['success']);
$error   = $_SESSION['error']   ?? ''; unset($_SESSION['error']);

$search = $_GET['search'] ?? '';
$catFilter = $_GET['category'] ?? '';

$sql = "SELECT p.*, c.name as category_name, COALESCE(SUM(s.quantity),0) as total_stock
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN stock s ON p.id = s.product_id
        WHERE 1=1";
if ($search) $sql .= " AND (p.name LIKE '%".mysqli_real_escape_string($conn,$search)."%' OR p.sku LIKE '%".mysqli_real_escape_string($conn,$search)."%')";
if ($catFilter) $sql .= " AND p.category_id = ".intval($catFilter);
$sql .= " GROUP BY p.id ORDER BY p.created_at DESC";
$products = $conn->query($sql);

$categories = $conn->query("SELECT * FROM categories ORDER BY name");
$warehouses = $conn->query("SELECT * FROM warehouses ORDER BY name");
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<div class="p-6 space-y-5">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Products</h1>
            <p class="text-slate-500 text-sm mt-0.5">Manage your product catalog</p>
        </div>
        <?php if(hasRole('admin','manager')): ?>
        <button onclick="document.getElementById('addModal').classList.remove('hidden')" class="btn-primary flex items-center gap-2">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add Product
        </button>
        <?php endif; ?>
    </div>

    <?php if ($success): ?><div class="p-3 rounded-lg text-sm" style="background:#052e16; color:#4ade80; border:1px solid #166534;"><?= htmlspecialchars($success) ?></div><?php endif; ?>
    <?php if ($error):   ?><div class="p-3 rounded-lg text-sm" style="background:#1f0a0a; color:#f87171; border:1px solid #7f1d1d;"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <!-- Filters -->
    <form method="GET" class="flex gap-3 flex-wrap">
        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search name or SKU..." class="input-field" style="max-width:280px;">
        <select name="category" class="input-field" style="max-width:200px;">
            <option value="">All Categories</option>
            <?php $categories->data_seek(0); while ($cat = $categories->fetch_assoc()): ?>
            <option value="<?= $cat['id'] ?>" <?= $catFilter==$cat['id']?'selected':'' ?>><?= htmlspecialchars($cat['name']) ?></option>
            <?php endwhile; ?>
        </select>
        <button type="submit" class="btn-secondary">Filter</button>
        <a href="/coreinventory/pages/products.php" class="btn-secondary">Clear</a>
    </form>

    <!-- Table -->
    <div class="card overflow-hidden">
        <table>
            <thead><tr><th>Product</th><th>SKU</th><th>Category</th><th>Unit</th><th>Total Stock</th><th>Reorder Level</th><th>Status</th></tr></thead>
            <tbody>
            <?php while ($p = $products->fetch_assoc()):
                $status = $p['total_stock'] == 0 ? ['Out of Stock','text-red-400'] : ($p['total_stock'] <= $p['reorder_level'] ? ['Low Stock','text-yellow-400'] : ['In Stock','text-green-400']);
            ?>
            <tr>
                <td class="font-medium text-white"><?= htmlspecialchars($p['name']) ?></td>
                <td class="font-mono text-xs text-slate-400"><?= htmlspecialchars($p['sku']) ?></td>
                <td class="text-slate-400"><?= htmlspecialchars($p['category_name'] ?? '—') ?></td>
                <td class="text-slate-400"><?= htmlspecialchars($p['unit']) ?></td>
                <td class="font-semibold <?= $status[1] ?>"><?= number_format($p['total_stock']) ?></td>
                <td class="text-slate-500"><?= $p['reorder_level'] ?></td>
                <td><span class="text-xs font-medium <?= $status[1] ?>"><?= $status[0] ?></span></td>
            </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Product Modal -->
<div id="addModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,.7);">
    <div class="card w-full max-w-lg p-6">
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-lg font-bold text-white">Add New Product</h2>
            <button onclick="document.getElementById('addModal').classList.add('hidden')" class="text-slate-500 hover:text-white">✕</button>
        </div>
        <form action="/coreinventory/actions/product_action.php" method="POST" class="space-y-4">
            <input type="hidden" name="action" value="create">
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-sm text-slate-400 mb-1.5">Product Name *</label>
                    <input type="text" name="name" class="input-field" required>
                </div>
                <div>
                    <label class="block text-sm text-slate-400 mb-1.5">SKU / Code *</label>
                    <input type="text" name="sku" class="input-field" required>
                </div>
                <div>
                    <label class="block text-sm text-slate-400 mb-1.5">Unit of Measure</label>
                    <input type="text" name="unit" class="input-field" placeholder="pcs, kg, ltr...">
                </div>
                <div>
                    <label class="block text-sm text-slate-400 mb-1.5">Category</label>
                    <select name="category_id" class="input-field">
                        <option value="">— Select —</option>
                        <?php $categories->data_seek(0); while ($cat = $categories->fetch_assoc()): ?>
                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-slate-400 mb-1.5">Reorder Level</label>
                    <input type="number" name="reorder_level" class="input-field" value="10" min="0">
                </div>
                <div>
                    <label class="block text-sm text-slate-400 mb-1.5">Initial Stock (optional)</label>
                    <input type="number" name="initial_stock" class="input-field" value="0" min="0" step="0.01">
                </div>
                <div>
                    <label class="block text-sm text-slate-400 mb-1.5">Initial Warehouse</label>
                    <select name="warehouse_id" class="input-field">
                        <?php while ($wh = $warehouses->fetch_assoc()): ?>
                        <option value="<?= $wh['id'] ?>"><?= htmlspecialchars($wh['name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary flex-1">Create Product</button>
                <button type="button" onclick="document.getElementById('addModal').classList.add('hidden')" class="btn-secondary flex-1">Cancel</button>
            </div>
        </form>
    </div>
</div>

</main>
</body>
</html>