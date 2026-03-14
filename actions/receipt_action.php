<?php
session_start();
require_once '../config/db.php';
require_once '../includes/auth.php';
requireLogin();
$user = currentUser();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /coreinventory/pages/receipts.php'); exit;
}

$action = $_POST['action'] ?? '';

// ── CREATE ──────────────────────────────────────────────
if ($action === 'create') {
    $reference   = trim($_POST['reference'] ?? '');
    $supplier    = trim($_POST['supplier'] ?? '');
    $warehouseId = intval($_POST['warehouse_id'] ?? 0);
    $notes       = trim($_POST['notes'] ?? '');
    $productIds  = $_POST['product_id'] ?? [];
    $quantities  = $_POST['quantity'] ?? [];

    if (!$warehouseId) {
        $_SESSION['error'] = 'Please select a destination warehouse.';
        header('Location: /coreinventory/pages/receipts.php'); exit;
    }

    if (empty(array_filter($productIds))) {
        $_SESSION['error'] = 'Add at least one product.';
        header('Location: /coreinventory/pages/receipts.php'); exit;
    }

    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("INSERT INTO operations (type, status, reference, supplier_customer, to_warehouse_id, notes, created_by) VALUES ('receipt','ready',?,?,?,?,?)");
        $stmt->bind_param("ssisi", $reference, $supplier, $warehouseId, $notes, $user['id']);
        $stmt->execute();
        $opId = $conn->insert_id;

        foreach ($productIds as $i => $pid) {
            $qty = floatval($quantities[$i] ?? 0);
            if (!$pid || $qty <= 0) continue;
            $li = $conn->prepare("INSERT INTO operation_items (operation_id, product_id, quantity) VALUES (?,?,?)");
            $li->bind_param("iid", $opId, $pid, $qty);
            $li->execute();
        }
        $conn->commit();
        $_SESSION['success'] = 'Receipt created. Click Validate to update stock.';
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = 'Error: ' . $e->getMessage();
    }
    header('Location: /coreinventory/pages/receipts.php'); exit;
}

// ── VALIDATE ─────────────────────────────────────────────
if ($action === 'validate') {
    if (!hasRole('admin', 'manager')) {
        $_SESSION['error'] = 'You do not have permission to validate receipts.';
        header('Location: /coreinventory/pages/receipts.php'); exit;
    }

    $opId = intval($_POST['operation_id'] ?? 0);
    $op   = $conn->query("SELECT * FROM operations WHERE id=$opId AND type='receipt'")->fetch_assoc();

    if (!$op) {
        $_SESSION['error'] = 'Receipt not found.';
        header('Location: /coreinventory/pages/receipts.php'); exit;
    }

    if ($op['status'] === 'done') {
        $_SESSION['error'] = 'This receipt has already been validated.';
        header('Location: /coreinventory/pages/receipts.php'); exit;
    }

    $wid = $op['to_warehouse_id'];
    if (!$wid) {
        $_SESSION['error'] = 'Warehouse not set on this receipt. Please delete it and create a new one.';
        header('Location: /coreinventory/pages/receipts.php'); exit;
    }

    $items = $conn->query("SELECT * FROM operation_items WHERE operation_id=$opId");

    if ($items->num_rows === 0) {
        $_SESSION['error'] = 'No items found on this receipt.';
        header('Location: /coreinventory/pages/receipts.php'); exit;
    }

    $conn->begin_transaction();
    try {
        while ($item = $items->fetch_assoc()) {
            $pid = $item['product_id'];
            $qty = $item['quantity'];

            // Upsert stock
            $s = $conn->prepare("INSERT INTO stock (product_id, warehouse_id, quantity) VALUES (?,?,?) ON DUPLICATE KEY UPDATE quantity = quantity + ?");
            $s->bind_param("iidd", $pid, $wid, $qty, $qty);
            $s->execute();

            // Get new balance
            $bal = $conn->query("SELECT quantity FROM stock WHERE product_id=$pid AND warehouse_id=$wid")->fetch_assoc()['quantity'];

            // Log to ledger
            $reason = 'Receipt #' . $opId . ' validated';
            $l = $conn->prepare("INSERT INTO stock_ledger (product_id, warehouse_id, operation_id, change_qty, balance_after, reason, created_by) VALUES (?,?,?,?,?,?,?)");
            $l->bind_param("iiiddsi", $pid, $wid, $opId, $qty, $bal, $reason, $user['id']);
            $l->execute();
        }

        $conn->query("UPDATE operations SET status='done', validated_at=NOW() WHERE id=$opId");
        $conn->commit();
        $_SESSION['success'] = 'Receipt validated! Stock updated successfully.';
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = 'Error: ' . $e->getMessage();
    }
    header('Location: /coreinventory/pages/receipts.php'); exit;
}

// fallback
header('Location: /coreinventory/pages/receipts.php');