<?php
session_start();
require_once '../config/db.php';
require_once '../includes/auth.php';
if ($_POST["action"] === "validate") requireRole("admin","manager");
requireLogin();
$user = currentUser();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: /coreinventory/pages/deliveries.php'); exit; }
$action = $_POST['action'] ?? '';

if ($action === 'create') {
    $reference   = trim($_POST['reference'] ?? '');
    $customer    = trim($_POST['customer'] ?? '');
    $warehouseId = intval($_POST['warehouse_id']);
    $notes       = trim($_POST['notes'] ?? '');
    $productIds  = $_POST['product_id'] ?? [];
    $quantities  = $_POST['quantity'] ?? [];

    if (empty($productIds)) { $_SESSION['error'] = 'Add at least one product.'; header('Location: /coreinventory/pages/deliveries.php'); exit; }

    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("INSERT INTO operations (type, status, reference, supplier_customer, from_warehouse_id, notes, created_by) VALUES ('delivery','ready',?,?,?,?,?)");
        $stmt->bind_param("ssisi", $reference, $customer, $warehouseId, $notes, $user['id']);
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
        $_SESSION['success'] = 'Delivery order created. Click Validate to update stock.';
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = 'Error: ' . $e->getMessage();
    }
    header('Location: /coreinventory/pages/deliveries.php'); exit;
}

if ($action === 'validate') {
    $opId = intval($_POST['operation_id']);
    $op   = $conn->query("SELECT * FROM operations WHERE id=$opId AND type='delivery'")->fetch_assoc();
    if (!$op || $op['status'] === 'done') { $_SESSION['error'] = 'Invalid or already validated.'; header('Location: /coreinventory/pages/deliveries.php'); exit; }

    $items = $conn->query("SELECT * FROM operation_items WHERE operation_id=$opId");
    $conn->begin_transaction();
    try {
        while ($item = $items->fetch_assoc()) {
            $pid = $item['product_id'];
            $qty = $item['quantity'];
            $wid = $op['from_warehouse_id'];

            // Check available stock
            $stockRow = $conn->query("SELECT quantity FROM stock WHERE product_id=$pid AND warehouse_id=$wid")->fetch_assoc();
            $available = $stockRow['quantity'] ?? 0;
            if ($available < $qty) {
                throw new Exception("Insufficient stock for product ID $pid. Available: $available, Requested: $qty");
            }

            $newQty = $available - $qty;
            $conn->query("UPDATE stock SET quantity=$newQty WHERE product_id=$pid AND warehouse_id=$wid");

            $negQty = -$qty;
            $reason = 'Delivery #'.$opId.' validated';
            $l = $conn->prepare("INSERT INTO stock_ledger (product_id, warehouse_id, operation_id, change_qty, balance_after, reason, created_by) VALUES (?,?,?,?,?,?,?)");
            $l->bind_param("iiiddsi", $pid, $wid, $opId, $negQty, $newQty, $reason, $user['id']);
            $l->execute();
        }
        $conn->query("UPDATE operations SET status='done', validated_at=NOW() WHERE id=$opId");
        $conn->commit();
        $_SESSION['success'] = 'Delivery validated! Stock deducted.';
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = 'Error: ' . $e->getMessage();
    }
    header('Location: /coreinventory/pages/deliveries.php'); exit;
}
header('Location: /coreinventory/pages/deliveries.php');
