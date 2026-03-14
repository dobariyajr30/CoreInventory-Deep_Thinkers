<?php
session_start();
require_once '../config/db.php';
require_once '../includes/auth.php';
requireLogin();
$user = currentUser();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: /coreinventory/pages/products.php'); exit; }

$action = $_POST['action'] ?? '';

if ($action === 'create') {
    $name         = trim($_POST['name'] ?? '');
    $sku          = trim($_POST['sku'] ?? '');
    $unit         = trim($_POST['unit'] ?? 'pcs');
    $category_id  = intval($_POST['category_id'] ?? 0) ?: null;
    $reorder      = intval($_POST['reorder_level'] ?? 10);
    $initStock    = floatval($_POST['initial_stock'] ?? 0);
    $warehouseId  = intval($_POST['warehouse_id'] ?? 1);

    if (!$name || !$sku) { $_SESSION['error'] = 'Name and SKU are required.'; header('Location: /coreinventory/pages/products.php'); exit; }

    $stmt = $conn->prepare("INSERT INTO products (name, sku, category_id, unit, reorder_level) VALUES (?,?,?,?,?)");
    $stmt->bind_param("ssisi", $name, $sku, $category_id, $unit, $reorder);
    if (!$stmt->execute()) {
        $_SESSION['error'] = 'SKU already exists.';
        header('Location: /coreinventory/pages/products.php'); exit;
    }
    $productId = $conn->insert_id;

    if ($initStock > 0) {
        // Insert stock
        $s = $conn->prepare("INSERT INTO stock (product_id, warehouse_id, quantity) VALUES (?,?,?) ON DUPLICATE KEY UPDATE quantity = quantity + ?");
        $s->bind_param("iidd", $productId, $warehouseId, $initStock, $initStock);
        $s->execute();

        // Get balance
        $bal = $conn->query("SELECT quantity FROM stock WHERE product_id=$productId AND warehouse_id=$warehouseId")->fetch_assoc()['quantity'];

        // Log ledger
        $reason = 'Initial stock on product creation';
        $l = $conn->prepare("INSERT INTO stock_ledger (product_id, warehouse_id, change_qty, balance_after, reason, created_by) VALUES (?,?,?,?,?,?)");
        $l->bind_param("iiddsi", $productId, $warehouseId, $initStock, $bal, $reason, $user['id']);
        $l->execute();
    }

    $_SESSION['success'] = "Product '$name' created successfully.";
    header('Location: /coreinventory/pages/products.php');
}
exit;
