<?php
session_start();
require_once '../connetionDB/config.php';

// Check admin access
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../user/login.php");
    exit();
}

// Fetch all categories
$categories_query = "SELECT * FROM categories";
$categories_result = mysqli_query($conn, $categories_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product | Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background-color: #f5f7fa;
        }
        .card {
            border-radius: 1rem;
            box-shadow: 0 4px 8px rgba(0,0,0,0.05);
        }
        .btn-primary {
            background: linear-gradient(135deg, #6c5ce7, #8e7dff);
            border: none;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #5a4fcf, #7b6aff);
        }
        .form-control, .form-select {
            border-radius: 0.5rem;
        }
        .preview-image {
            max-width: 200px;
            max-height: 200px;
            border-radius: 0.5rem;
            display: none;
            margin-top: 10px;
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
                    <h4><i class="fas fa-plus-circle me-2"></i> Add New Product</h4>
                </div>
                <div class="card-body p-4">
                    <?php if (isset($_SESSION['product_error'])): ?>
                        <div class="alert alert-danger">
                            <?= $_SESSION['product_error'] ?>
                            <?php unset($_SESSION['product_error']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['product_success'])): ?>
                        <div class="alert alert-success">
                            <?= $_SESSION['product_success'] ?>
                            <?php unset($_SESSION['product_success']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <form id="productForm" action="process_product.php" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="productName" class="form-label fw-bold">Product Name</label>
                            <input type="text" class="form-control" id="productName" name="name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="productPrice" class="form-label fw-bold">Price (EGP)</label>
                            <input type="number" class="form-control" id="productPrice" name="price" step="0.01" min="0" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="productCategory" class="form-label fw-bold">Category</label>
                            <div class="d-flex">
                                <select class="form-select me-2" id="productCategory" name="category_id" required>
                                    <option value="">Select Category</option>
                                    <?php while ($category = mysqli_fetch_assoc($categories_result)): ?>
                                        <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                                    <?php endwhile; ?>
                                </select>
                                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                                    <i class="fas fa-plus"></i> Add New
                                </button>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="productImage" class="form-label fw-bold">Product Image</label>
                            <input type="file" class="form-control" id="productImage" name="image" accept="image/*">
                            <img id="imagePreview" src="#" alt="Preview" class="preview-image">
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="productAvailable" name="available" checked>
                            <label class="form-check-label" for="productAvailable">Available</label>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary py-2">
                                <i class="fas fa-save me-2"></i> Save Product
                            </button>
                            <button type="reset" class="btn btn-outline-secondary">
                                <i class="fas fa-undo me-2"></i> Reset Form
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addCategoryModalLabel">Add New Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="categoryForm">
                    <div class="mb-3">
                        <label for="categoryName" class="form-label">Category Name</label>
                        <input type="text" class="form-control" id="categoryName" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveCategoryBtn">Save Category</button>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Custom JS -->
<script src="../assets/js/add_product.js"></script>

</body>
</html>