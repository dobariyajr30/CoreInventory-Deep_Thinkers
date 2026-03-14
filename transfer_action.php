<?php
session_start();
require_once '../config/db.php';
require_once '../includes/auth.php';
if ($_POST["action"] === "validate") requireRole("admin","manager");
requireLogin();
$user = currentUser();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: /coreinventory/pages/transfers.php'); exit; }
$action = $_POST['action'] ?? '';

if ($action === 'create') {
    $reference   = trim($_POST['reference'] ?? '');
    $fromWh      = intval($_POST['from_warehouse_id']);
    $toWh        = intval($_POST['to_warehouse_id']);
    $notes       = trim($_POST['notes'] ?? '');
    $productIds  = $_POST['product_id'] ?? [];
    $quantities  = $_POST['quantity'] ?? [];

    if ($fromWh === $toWh) { $_SESSION['error'] = 'Source and destination warehouses must be different.'; header('Location: /coreinventory/pages/transfers.php'); exit; }
    if (empty($productIds)) { $_SESSION['error'] = 'Add at least one product.'; header('Location: /coreinventory/pages/transfers.php'); exit; }

    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("INSERT INTO operations (type, status, reference, from_warehouse_id, to_warehouse_id, notes, created_by) VALUES ('transfer','ready',?,?,?,?,?)");
        $stmt->bind_param("siisi", $reference, $fromWh, $toWh, $notes, $user['id']);
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
        $_SESSION['success'] = 'Transfer created. Click Validate to move stock.';
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = 'Error: ' . $e->getMessage();
    }
    header('Location: /coreinventory/pages/transfers.php'); exit;
}

if ($action === 'validate') {
    $opId = intval($_POST['operation_id']);
    $op   = $conn->query("SELECT * FROM operations WHERE id=$opId AND type='transfer'")->fetch_assoc();
    if (!$op || $op['status'] === 'done') { $_SESSION['error'] = 'Invalid or already validated.'; header('Location: /coreinventory/pages/transfers.php'); exit; }

    $items  = $conn->query("SELECT * FROM operation_items WHERE operation_id=$opId");
    $fromWh = $op['from_warehouse_id'];
    $toWh   = $op['to_warehouse_id'];

    $conn->begin_transaction();
    try {
        while ($item = $items->fetch_assoc()) {
            $pid = $item['product_id'];
            $qty = $item['quantity'];

            // Check source stock
            $srcRow = $conn->query("SELECT quantity FROM stock WHERE product_id=$pid AND warehouse_id=$fromWh")->fetch_assoc();
            $available = $srcRow['quantity'] ?? 0;
            if ($available < $qty) throw new Exception("Insufficient stock for product ID $pid in source warehouse. Available: $available");

            // Deduct from source
            $newSrc = $available - $qty;
            $conn->query("UPDATE stock SET quantity=$newSrc WHERE product_id=$pid AND warehouse_id=$fromWh");

            // Add to destination
            $dstRow = $conn->query("SELECT quantity FROM stock WHERE product_id=$pid AND warehouse_id=$toWh")->fetch_assoc();
            if ($dstRow) {
                $newDst = $dstRow['quantity'] + $qty;
                $conn->query("UPDATE stock SET quantity=$newDst WHERE product_id=$pid AND warehouse_id=$toWh");
            } else {
                $newDst = $qty;
                $conn->query("INSERT INTO stock (product_id, warehouse_id, quantity) VALUES ($pid, $toWh, $qty)");
            }

            $negQty  = -$qty;
            $reason  = 'Transfer #'.$opId.' — moved out';
            $reason2 = 'Transfer #'.$opId.' — moved in';

            $l = $conn->prepare("INSERT INTO stock_ledger (product_id, warehouse_id, operation_id, change_qty, balance_after, reason, created_by) VALUES (?,?,?,?,?,?,?)");
            $l->bind_param("iiiddsi", $pid, $fromWh, $opId, $negQty, $newSrc, $reason, $user['id']);
            $l->execute();
            $l->bind_param("iiiddsi", $pid, $toWh, $opId, $qty, $newDst, $reason2, $user['id']);
            $l->execute();
        }
        $conn->query("UPDATE operations SET status='done', validated_at=NOW() WHERE id=$opId");
        $conn->commit();
        $_SESSION['success'] = 'Transfer validated! Stock moved between warehouses.';
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = 'Error: ' . $e->getMessage();
    }
    header('Location: /coreinventory/pages/transfers.php'); exit;
}
header('Location: /coreinventory/pages/transfers.php');
