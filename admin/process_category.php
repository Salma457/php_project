<?php
session_start();
require_once '../connetionDB/config.php';

// Check admin access
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../user/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    
    // Validate input
    if (empty($name)) {
        $_SESSION['category_error'] = "Category name is required";
        header("Location: add_category.php");
        exit();
    }
    
    // Check if category already exists
    $check_query = "SELECT id FROM categories WHERE name = ?";
    $stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($stmt, "s", $name);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    
    if (mysqli_stmt_num_rows($stmt) > 0) {
        $_SESSION['category_error'] = "Category '$name' already exists";
        header("Location: add_category.php");
        exit();
    }
    
    // Insert new category
    $insert_query = "INSERT INTO categories (name) VALUES (?)";
    $stmt = mysqli_prepare($conn, $insert_query);
    mysqli_stmt_bind_param($stmt, "s", $name);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['category_success'] = "Category '$name' added successfully!";
        header("Location: add_category.php");
        exit();
    } else {
        $_SESSION['category_error'] = "Error adding category: " . mysqli_error($conn);
        header("Location: add_category.php");
        exit();
    }
} else {
    header("Location: add_category.php");
    exit();
}
?>