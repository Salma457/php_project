<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$order_id = $_POST['order_id'];
$user_id = $_SESSION['user_id'];

// Check if order belongs to user and is in processing status
$check_query = "SELECT id FROM orders WHERE id = ? AND user_id = ? AND status = 'processing'";
$stmt = mysqli_prepare($conn, $check_query);
mysqli_stmt_bind_param($stmt, 'ii', $order_id, $user_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);

if (mysqli_stmt_num_rows($stmt) == 0) {
    echo json_encode(['success' => false, 'message' => 'Order cannot be cancelled']);
    exit();
}

// Delete order (cascade will delete order_items)
$delete_query = "DELETE FROM orders WHERE id = ?";
$stmt = mysqli_prepare($conn, $delete_query);
mysqli_stmt_bind_param($stmt, 'i', $order_id);
$success = mysqli_stmt_execute($stmt);

if ($success) {
    echo json_encode(['success' => true, 'message' => 'Order cancelled successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to cancel order']);
}
?>