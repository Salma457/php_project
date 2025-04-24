<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];
$room = $_POST['room'];
$items = json_decode($_POST['items'], true);
$total_price = $_POST['total_price'];

// Validate input
if (empty($room) {
    echo json_encode(['success' => false, 'message' => 'Room number is required']);
    exit();
}

if (empty($items)) {
    echo json_encode(['success' => false, 'message' => 'No items in order']);
    exit();
}

// Start transaction
mysqli_begin_transaction($conn);

try {
    // Create order
    $order_query = "INSERT INTO orders (user_id, total_price, status) VALUES (?, ?, 'processing')";
    $stmt = mysqli_prepare($conn, $order_query);
    mysqli_stmt_bind_param($stmt, 'id', $user_id, $total_price);
    mysqli_stmt_execute($stmt);
    $order_id = mysqli_insert_id($conn);
    
    // Add order items
    $item_query = "INSERT INTO order_items (order_id, product_id, quantity, note) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $item_query);
    
    foreach ($items as $item) {
        mysqli_stmt_bind_param($stmt, 'iiis', $order_id, $item['product_id'], $item['quantity'], $item['note']);
        mysqli_stmt_execute($stmt);
    }
    
    // Update user's room number if changed
    $update_user = "UPDATE users SET room_number = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $update_user);
    mysqli_stmt_bind_param($stmt, 'si', $room, $user_id);
    mysqli_stmt_execute($stmt);
    
    // Commit transaction
    mysqli_commit($conn);
    
    echo json_encode(['success' => true, 'message' => 'Order placed successfully']);
} catch (Exception $e) {
    // Rollback transaction on error
    mysqli_rollback($conn);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>