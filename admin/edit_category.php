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

// Fetch category details
$category = null;
if ($category_id > 0) {
    $query = "SELECT * FROM categories WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $category_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $category = mysqli_fetch_assoc($result);
}

if (!$category) {
    $_SESSION['category_error'] = "Category not found";
    header("Location: add_category.php");
    exit();
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    
    // Validate input
    if (empty($name)) {
        $_SESSION['category_error'] = "Category name is required";
        header("Location: edit_category.php?id=$category_id");
        exit();
    }
    
    // Check if category already exists (excluding current one)
    $check_query = "SELECT id FROM categories WHERE name = ? AND id != ?";
    $stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($stmt, "si", $name, $category_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    
    if (mysqli_stmt_num_rows($stmt) > 0) {
        $_SESSION['category_error'] = "Category '$name' already exists";
        header("Location: edit_category.php?id=$category_id");
        exit();
    }
    
    // Update category
    $update_query = "UPDATE categories SET name = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($stmt, "si", $name, $category_id);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['category_success'] = "Category updated successfully!";
        header("Location: add_category.php");
        exit();
    } else {
        $_SESSION['category_error'] = "Error updating category: " . mysqli_error($conn);
        header("Location: edit_category.php?id=$category_id");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Category | Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Base styles for the page */
        body {
            font-family: 'Cairo', sans-serif;
            background-color: #f8f9fa;
        }

        .card {
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card-header {
            background: linear-gradient(45deg, #6c5ce7, #a29bfe);
            color: white;
            font-weight: bold;
        }

        .form-control {
            transition: all 0.3s ease;
        }

        .form-control:focus {
            box-shadow: 0 0 10px rgba(0, 0, 255, 0.5);
            border-color: #6c5ce7;
        }

        .btn-primary, .btn-outline-secondary {
            transition: all 0.3s ease;
        }

        .btn-primary:hover, .btn-outline-secondary:hover {
            background-color: #6c5ce7;
            border-color: #6c5ce7;
            color: white;
        }

        .alert {
            animation: slideIn 0.5s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header text-center">
                    <h4><i class="fas fa-edit me-2"></i> Edit Category</h4>
                </div>
                
                <div class="card-body p-4">
                    <?php if (isset($_SESSION['category_error'])): ?>
                        <div class="alert alert-danger">
                            <?= $_SESSION['category_error'] ?>
                            <?php unset($_SESSION['category_error']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label for="categoryName" class="form-label fw-bold">Category Name</label>
                            <input type="text" class="form-control" id="categoryName" name="name" 
                                   value="<?= htmlspecialchars($category['name']) ?>" required>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i> Save Changes
                            </button>
                            <a href="add_category.php" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i> Back to Categories
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
