<?php
session_start();
require_once '../connetionDB/config.php';

// Check admin access
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../user/login.php");
    exit();
}

// Pagination settings
$items_per_page = 6;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) $current_page = 1;
$offset = ($current_page - 1) * $items_per_page;

// Search functionality
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$category_filter = isset($_GET['category']) ? (int)$_GET['category'] : 0;

// Fetch all users for dropdown
$users_query = "SELECT id, name, room_number FROM users WHERE role = 'user'";
$users_result = mysqli_query($conn, $users_query);

// Build products query with search and pagination
$products_query = "SELECT p.*, c.name as category_name 
                  FROM products p 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  WHERE p.available = TRUE";

if (!empty($search)) {
    $products_query .= " AND (p.name LIKE '%$search%' OR c.name LIKE '%$search%')";
}

if ($category_filter > 0) {
    $products_query .= " AND p.category_id = $category_filter";
}

// Get total count for pagination
$count_query = str_replace("SELECT p.*, c.name as category_name", "SELECT COUNT(*) as total", $products_query);
$count_result = mysqli_query($conn, $count_query);
$total_items = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_items / $items_per_page);

// Add pagination to products query
$products_query .= " LIMIT $offset, $items_per_page";
$products_result = mysqli_query($conn, $products_query);

// Fetch categories for filter dropdown
$categories_query = "SELECT * FROM categories";
$categories_result = mysqli_query($conn, $categories_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Order | Admin Panel</title>
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
            transition: transform 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .product-img {
            height: 150px;
            object-fit: cover;
            border-top-left-radius: 1rem;
            border-top-right-radius: 1rem;
        }
        .quantity-input {
            width: 50px;
            text-align: center;
        }
        .btn-primary {
            background: linear-gradient(135deg, #6c5ce7, #8e7dff);
            border: none;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #5a4fcf, #7b6aff);
        }
        #orderItems {
            max-height: 300px;
            overflow-y: auto;
            padding-right: 10px;
        }
        .note-input {
            font-size: 0.9rem;
        }
        .form-select, .form-control {
            border-radius: 0.5rem;
        }
        .custom-outline {
            color: #6c5ce7;
            border: 1px solid #6c5ce7;
            background-color: transparent;
        }
        .custom-outline:hover {
            background-color: #6c5ce7;
            color: white;
        }
        .search-box {
            position: relative;
        }
        .search-box .btn {
            position: absolute;
            right: 5px;
            top: 5px;
            background: transparent;
            border: none;
        }
        .page-item.active .page-link {
            background-color: #6c5ce7;
            border-color: #6c5ce7;
        }
        .page-link {
            color: #6c5ce7;
        }
    </style>
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="container my-5">
    <h2 class="text-center mb-5 fw-bold" style="color: #6c5ce7;">Create New Order</h2>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card p-3">
                <div class="card-header text-white text-center rounded" style="background-color: #6c5ce7;">
                    <h5>Select Products</h5>
                </div>
                <div class="card-body">
                    <!-- Search and Filter Form -->
                    <form method="GET" class="mb-4">
                        <div class="row g-3">
                            <div class="col-md-6 search-box">
                                <input type="text" name="search" class="form-control" placeholder="Search products..." 
                                       value="<?= htmlspecialchars($search) ?>">
                                <button type="submit" class="btn"><i class="fas fa-search"></i></button>
                            </div>
                            <div class="col-md-4">
                                <select name="category" class="form-select">
                                    <option value="0">All Categories</option>
                                    <?php while ($category = mysqli_fetch_assoc($categories_result)): ?>
                                        <option value="<?= $category['id'] ?>" 
                                            <?= $category_filter == $category['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($category['name']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">Filter</button>
                            </div>
                        </div>
                    </form>

                    <!-- Products Grid -->
                    <div class="row g-3">
                        <?php if (mysqli_num_rows($products_result) > 0): ?>
                            <?php while ($product = mysqli_fetch_assoc($products_result)): ?>
                                <div class="col-md-4">
                                    <div class="card h-100 product-item" data-id="<?= $product['id'] ?>" data-price="<?= $product['price'] ?>">
                                    <img src="<?php echo 'http://localhost/php_project/uploads/' . htmlspecialchars($product['image']); ?>" class="card-img-top product-img">

                                        <div class="card-body text-center">
                                            <h6 class="card-title"><?= htmlspecialchars($product['name']) ?></h6>
                                            <p class="card-text fw-bold" style="color: #6c5ce7;"><?= number_format($product['price'], 2) ?> EGP</p>
                                            <?php if (!empty($product['category_name'])): ?>
                                                <span class="badge bg-secondary mb-2"><?= htmlspecialchars($product['category_name']) ?></span>
                                            <?php endif; ?>
                                            <div class="d-flex justify-content-center align-items-center mb-2">
                                                <button class="btn btn-sm custom-outline minus-btn">-</button>
                                                <input type="number" class="form-control quantity-input mx-2" value="0" min="0">
                                                <button class="btn btn-sm custom-outline plus-btn">+</button>
                                            </div>
                                            <textarea class="form-control note-input" placeholder="Notes..." rows="1"></textarea>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="col-12 text-center py-4">
                                <i class="fas fa-box-open fa-3x mb-3" style="color: #6c5ce7;"></i>
                                <h5>No products found</h5>
                                <p>Try adjusting your search or filter criteria</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <nav class="mt-4">
                            <ul class="pagination justify-content-center">
                                <?php if ($current_page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?= $current_page-1 ?>&search=<?= urlencode($search) ?>&category=<?= $category_filter ?>" aria-label="Previous">
                                            <span aria-hidden="true">&laquo;</span>
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?= $i == $current_page ? 'active' : '' ?>">
                                        <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&category=<?= $category_filter ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($current_page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?= $current_page+1 ?>&search=<?= urlencode($search) ?>&category=<?= $category_filter ?>" aria-label="Next">
                                            <span aria-hidden="true">&raquo;</span>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card p-3">
                <div class="card-header text-white text-center rounded" style="background-color: #6c5ce7;">                    
                    <h5>Order Summary</h5>
                </div>
                <div class="card-body">
                    <form id="orderForm" action="process_order.php" method="POST">
                        <div class="form-group mb-3">
                            <label for="userSelect" class="fw-bold">Select User</label>
                            <select class="form-select" id="userSelect" name="user_id" required>
                                <option value="">-- Choose User --</option>
                                <?php mysqli_data_seek($users_result, 0); // Reset pointer ?>
                                <?php while ($user = mysqli_fetch_assoc($users_result)): ?>
                                <option value="<?= $user['id'] ?>" data-room="<?= $user['room_number'] ?>">
                                    <?= htmlspecialchars($user['name']) ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="form-group mb-3">
                            <label for="roomSelect" class="fw-bold">Room Number</label>
                            <input type="text" class="form-control" id="roomSelect" readonly placeholder="Auto-filled">
                        </div>

                        <div id="orderItems" class="mb-3">
                            <p class="text-muted text-center">No items selected yet</p>
                        </div>

                        <div class="d-flex justify-content-between fw-bold">
                            <span>Total:</span>
                            <span id="totalAmount">0.00 EGP</span>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 mt-4">Confirm Order</button>
                    </form>
                    
                    <?php if (isset($_SESSION['error_message'])): ?>
                        <div class="alert alert-danger mt-3"><?= $_SESSION['error_message'] ?></div>
                        <?php unset($_SESSION['error_message']); ?>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['success_message'])): ?>
                        <div class="alert alert-success mt-3"><?= $_SESSION['success_message'] ?></div>
                        <?php unset($_SESSION['success_message']); ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JS Links -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/manual_order.js"></script>

</body>
</html>