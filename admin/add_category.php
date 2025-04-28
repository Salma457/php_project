<?php
session_start();
require_once '../connetionDB/config.php';

// Check admin access
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../user/login.php");
    exit();
}

// Fetch existing categories
$categories_query = "SELECT * FROM categories ORDER BY name ASC";
$categories_result = mysqli_query($conn, $categories_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Category | Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background-color: #f5f7fa;
            animation: fadeIn 1s ease-in-out;
        }

        /* Animation for form */
        .form-container {
            opacity: 0;
            transform: translateY(20px);
            animation: slideIn 1s forwards;
        }

        @keyframes slideIn {
            0% {
                opacity: 0;
                transform: translateY(20px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .card {
            border-radius: 1.2rem;
            box-shadow: 0 6px 12px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card:hover {
            transform: translateY(-12px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
        }
        .btn-primary {
            background: linear-gradient(135deg, #6c5ce7, #8e7dff);
            border: none;
            transition: background 0.3s ease;
            font-size: 1rem;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #5a4fcf, #7b6aff);
        }
        .form-control {
            border-radius: 0.75rem;
            transition: border-color 0.3s ease;
        }
        .form-control:focus {
            border-color: #6c5ce7;
            box-shadow: 0 0 0 0.2rem rgba(108, 92, 231, 0.25);
        }
        .category-badge {
            background-color: #e9ecef;
            color: #495057;
            font-size: 0.9rem;
        }
        .action-btns .btn {
            padding: 0.25rem 0.75rem;
            font-size: 0.9rem;
            transition: background-color 0.3s ease;
        }
        .action-btns .btn:hover {
            background-color: #e3f2fd;
        }
        .alert {
            animation: slideIn 0.5s ease-out;
        }
        .list-group-item {
            transition: background-color 0.3s ease, transform 0.2s ease;
            padding: 1rem;
        }
        .list-group-item:hover {
            background-color: #f1f3f5;
            transform: translateX(5px);
        }

        @keyframes fadeIn {
            0% { opacity: 0; }
            100% { opacity: 1; }
        }

        @keyframes slideIn {
            0% { opacity: 0; transform: translateY(20px); }
            100% { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header text-white text-center rounded" style="background-color: #6c5ce7;">
                    <h4><i class="fas fa-tags me-2"></i> Manage Categories</h4>
                </div>
                
                <div class="card-body p-4 form-container"> <!-- Apply animation here -->
                    <?php if (isset($_SESSION['category_error'])): ?>
                        <div class="alert alert-danger">
                            <?= $_SESSION['category_error'] ?>
                            <?php unset($_SESSION['category_error']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['category_success'])): ?>
                        <div class="alert alert-success">
                            <?= $_SESSION['category_success'] ?>
                            <?php unset($_SESSION['category_success']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Add Category Form -->
                    <form id="categoryForm" action="process_category.php" method="POST" class="mb-4">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-8">
                                <label for="categoryName" class="form-label fw-bold">New Category Name</label>
                                <input type="text" class="form-control" id="categoryName" name="name" required>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-plus-circle me-2"></i> Add Category
                                </button>
                            </div>
                        </div>
                    </form>
                    
                    <!-- Existing Categories List -->
                    <div class="mt-4">
                        <h5 class="mb-3 fw-bold">Existing Categories</h5>
                        
                        <?php if (mysqli_num_rows($categories_result) > 0): ?>
                            <div class="list-group">
                                <?php while ($category = mysqli_fetch_assoc($categories_result)): ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <span class="fw-bold"><?= htmlspecialchars($category['name']) ?></span>
                                        <div class="action-btns">
                                            <a href="edit_category.php?id=<?= $category['id'] ?>" class="btn btn-sm btn-outline-primary me-1">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="delete_category.php?id=<?= $category['id'] ?>" class="btn btn-sm btn-outline-danger" 
                                               onclick="return confirm('Are you sure you want to delete this category?')">
                                                <i class="fas fa-trash-alt"></i>
                                            </a>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info text-center">
                                <i class="fas fa-info-circle me-2"></i> No categories found. Add your first category above.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Custom JS -->
<script src="../assets/js/add_category.js"></script>

</body>
</html>
