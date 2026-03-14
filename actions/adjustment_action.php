<?php
session_start();
require_once '../config/db.php';
require_once '../includes/auth.php';
requireRole("admin","manager");
requireLogin();
$user = currentUser();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: /coreinventory/pages/adjustments.php'); exit; }

$reference   = trim($_POST['reference'] ?? '');
$warehouseId = intval($_POST['warehouse_id']);
$notes       = trim($_POST['notes'] ?? '');
$productIds  = $_POST['product_id'] ?? [];
$actualQtys  = $_POST['actual_qty'] ?? [];

if (empty($productIds)) { $_SESSION['error'] = 'Add at least one product.'; header('Location: /coreinventory/pages/adjustments.php'); exit; }

$conn->begin_transaction();
try {
    $stmt = $conn->prepare("INSERT INTO operations (type, status, reference, from_warehouse_id, notes, created_by) VALUES ('adjustment','done',?,?,?,?)");
    $stmt->bind_param("sisi", $reference, $warehouseId, $notes, $user['id']);
    $stmt->execute();
    $opId = $conn->insert_id;

    foreach ($productIds as $i => $pid) {
        $pid      = intval($pid);
        $actualQty = floatval($actualQtys[$i] ?? 0);
        if (!$pid) continue;

        // Get current stock
        $stockRow = $conn->query("SELECT quantity FROM stock WHERE product_id=$pid AND warehouse_id=$warehouseId")->fetch_assoc();
        $currentQty = $stockRow ? $stockRow['quantity'] : 0;
        $diff = $actualQty - $currentQty;

        // Save line item (store actual qty)
        $li = $conn->prepare("INSERT INTO operation_items (operation_id, product_id, quantity) VALUES (?,?,?)");
        $li->bind_param("iid", $opId, $pid, $actualQty);
        $li->execute();

        // Update stock to actual count
        if ($stockRow) {
            $conn->query("UPDATE stock SET quantity=$actualQty WHERE product_id=$pid AND warehouse_id=$warehouseId");
        } else {
            $conn->query("INSERT INTO stock (product_id, warehouse_id, quantity) VALUES ($pid, $warehouseId, $actualQty)");
        }

        // Log ledger
        $reason = 'Adjustment #'.$opId.': '.$notes;
        $l = $conn->prepare("INSERT INTO stock_ledger (product_id, warehouse_id, operation_id, change_qty, balance_after, reason, created_by) VALUES (?,?,?,?,?,?,?)");
        $l->bind_param("iiiddsi", $pid, $warehouseId, $opId, $diff, $actualQty, $reason, $user['id']);
        $l->execute();
    }

    $conn->query("UPDATE operations SET validated_at=NOW() WHERE id=$opId");
    $conn->commit();
    $_SESSION['success'] = 'Stock adjustment applied and logged successfully.';
} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error'] = 'Error: ' . $e->getMessage();
}
header('Location: /coreinventory/pages/adjustments.php');
