<?php
session_start();
require_once '../connetionDB/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

// Validate and sanitize input
$user_id = filter_var($_POST['user_id'], FILTER_VALIDATE_INT);
$room = filter_var($_POST['room'], FILTER_SANITIZE_STRING);
$items = json_decode($_POST['items'], true);
$total_price = filter_var($_POST['total_price'], FILTER_VALIDATE_FLOAT);

// Validate input
if (!$user_id || !$room || !$items || !$total_price) {
    echo json_encode(['success' => false, 'message' => 'Invalid input data']);
    exit();
}

// Start transaction
mysqli_begin_transaction($conn);

try {
    // 1. Create the main order record
    $order_query = "INSERT INTO orders (user_id, total_price, status) VALUES (?, ?, 'processing')";
    $stmt = mysqli_prepare($conn, $order_query);
    mysqli_stmt_bind_param($stmt, 'id', $user_id, $total_price);
    mysqli_stmt_execute($stmt);
    $order_id = mysqli_insert_id($conn);
    
    // 2. Add all order items
    $item_query = "INSERT INTO order_items (order_id, product_id, quantity, note) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $item_query);
    
    foreach ($items as $item) {
        $product_id = filter_var($item['product_id'], FILTER_VALIDATE_INT);
        $quantity = filter_var($item['quantity'], FILTER_VALIDATE_INT);
        $note = filter_var($item['note'], FILTER_SANITIZE_STRING);
        
        if (!$product_id || !$quantity) {
            throw new Exception('Invalid item data');
        }
        
        mysqli_stmt_bind_param($stmt, 'iiis', $order_id, $product_id, $quantity, $note);
        mysqli_stmt_execute($stmt);
    }
    
    // 3. Update user's room number
    $update_user = "UPDATE users SET room_number = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $update_user);
    mysqli_stmt_bind_param($stmt, 'si', $room, $user_id);
    mysqli_stmt_execute($stmt);
    
    // Commit transaction if all queries succeeded
    mysqli_commit($conn);
    
    echo json_encode([
        'success' => true,
        'message' => 'Order placed successfully',
        'order_id' => $order_id
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    mysqli_rollback($conn);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>