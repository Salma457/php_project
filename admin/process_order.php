<?php
session_start();
require_once '../connetionDB/config.php';

// Check if user is admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../user/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = mysqli_real_escape_string($conn, $_POST['user_id']);
    $items = json_decode($_POST['items'], true);
    
    // Validate inputs
    if (empty($user_id) || empty($items)) {
        $_SESSION['error_message'] = "Please select a user and at least one product";
        header("Location: manual_order.php");
        exit();
    }
    
    // Calculate total price
    $total_price = 0;
    foreach ($items as $item) {
        $total_price += $item['price'] * $item['quantity'];
    }
    
    // Start transaction
    mysqli_begin_transaction($conn);
    
    try {
        // Insert order
        $order_query = "INSERT INTO orders (user_id, total_price, status) 
                        VALUES ('$user_id', '$total_price', 'processing')";
        mysqli_query($conn, $order_query);
        $order_id = mysqli_insert_id($conn);
        
        // Insert order items
        foreach ($items as $item) {
            $product_id = mysqli_real_escape_string($conn, $item['product_id']);
            $quantity = mysqli_real_escape_string($conn, $item['quantity']);
            $note = mysqli_real_escape_string($conn, $item['note']);
            
            $item_query = "INSERT INTO order_items (order_id, product_id, quantity, note) 
                           VALUES ('$order_id', '$product_id', '$quantity', '$note')";
            mysqli_query($conn, $item_query);
        }
        
        // Commit transaction
        mysqli_commit($conn);
        
        $_SESSION['success_message'] = "Order created successfully!";
        header("Location: manual_order.php");
        exit();
    } catch (Exception $e) {
        // Rollback transaction on error
        mysqli_rollback($conn);
        $_SESSION['error_message'] = "Error creating order: " . $e->getMessage();
        header("Location: manual_order.php");
        exit();
    }
} else {
    header("Location: manual_order.php");
    exit();
}
?>