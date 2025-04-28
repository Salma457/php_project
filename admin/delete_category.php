<?php
session_start();
require_once '../connetionDB/config.php';

// Check admin access
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../user/login.php");
    exit();
}

// Get category ID from URL
$category_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($category_id > 0) {
    // Check if category is used in any products
    $check_query = "SELECT COUNT(*) as count FROM products WHERE category_id = ?";
    $stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($stmt, "i", $category_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    
    if ($row['count'] > 0) {
        $_SESSION['category_error'] = "Cannot delete category - it is being used by products";
        header("Location: add_category.php");
        exit();
    }
    
    // Delete category
    $delete_query = "DELETE FROM categories WHERE id = ?";
    $stmt = mysqli_prepare($conn, $delete_query);
    mysqli_stmt_bind_param($stmt, "i", $category_id);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['category_success'] = "Category deleted successfully!";
    } else {
        $_SESSION['category_error'] = "Error deleting category: " . mysqli_error($conn);
    }
}

header("Location: add_category.php");
exit();
?>
