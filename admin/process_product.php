<?php
session_start();
require_once '../connetionDB/config.php';

// Check admin access
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../user/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate inputs
    $name = trim($_POST['name'] ?? '');
    $price = $_POST['price'] ?? 0;
    $category_id = $_POST['category_id'] ?? 0;
    $available = isset($_POST['available']) ? 1 : 0;
    
    if (empty($name)) {
        $_SESSION['product_error'] = "Product name is required";
        header("Location: add_product.php");
        exit();
    }
    
    if ($price <= 0) {
        $_SESSION['product_error'] = "Price must be greater than 0";
        header("Location: add_product.php");
        exit();
    }
    
    if (empty($category_id)) {
        $_SESSION['product_error'] = "Category is required";
        header("Location: add_product.php");
        exit();
    }
    
    // Handle file upload
    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';  // Correct path to uploads folder
        $file_name = basename($_FILES['image']['name']);  // Get the original file name
        $file_name = preg_replace("/[^a-zA-Z0-9\._-]/", "", $file_name);  // Clean up the file name
        $target_path = $upload_dir . '/' . $file_name;

        // Check if image file is an actual image
        $check = getimagesize($_FILES['image']['tmp_name']);
        if ($check === false) {
            $_SESSION['product_error'] = "File is not an image";
            header("Location: add_product.php");
            exit();
        }
        
        
        
        // Allow certain file formats
        $imageFileType = strtolower(pathinfo($target_path, PATHINFO_EXTENSION));
        if (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
            $_SESSION['product_error'] = "Only JPG, JPEG, PNG & GIF files are allowed";
            header("Location: add_product.php");
            exit();
        }
        
        // Move the uploaded file to the target path
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
            $image_path = $file_name;  // Store only the file name for DB
        } else {
            $_SESSION['product_error'] = "Error uploading image";
            header("Location: add_product.php");
            exit();
        }
    }
    
    // Insert product into database
    $query = "INSERT INTO products (name, price, category_id, image, available) 
              VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "sdiss", $name, $price, $category_id, $image_path, $available);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['product_success'] = "Product added successfully!";
        header("Location: add_product.php");
        exit();
    } else {
        $_SESSION['product_error'] = "Error adding product: " . mysqli_error($conn);
        header("Location: add_product.php");
        exit();
    }
} else {
    header("Location: add_product.php");
    exit();
}

?>