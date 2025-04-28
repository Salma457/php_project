<?php
session_start();
require_once '../connetionDB/config.php';

// Fetch products
$query = "SELECT products.*, categories.name AS category_name 
          FROM products 
          LEFT JOIN categories ON products.category_id = categories.id";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f5f6fa;
        }
        .product-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
            background: white;
        }
        .product-card:hover {
            transform: translateY(-5px);
        }
        .product-image {
            height: 200px;
            object-fit: cover;
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
        }
        .badge-available {
            background-color: #00b894;
        }
        .badge-unavailable {
            background-color: #d63031;
        }
        .btnadd {
            background-color:rgb(16, 112, 124) !important;
            color: white;
            border-radius: 15px;
            padding: 10px 20px;
            margin-bottom: 20px !important;
            text-decoration: none;
        }
    </style>
</head>
<body>
<?php include '../admin/includes/navbar.php'; ?>
    <div class="container py-5">
        <h1 class="text-center mb-4">Products List</h1>

        <a href="    ../admin/add_product.php" class="btn btnadd" >
            <i class="fas fa-plus-circle me-2" styles="margin-bottom: 50px;"></i>create product
           
   
</a>
        <div class="row g-4">
            <?php while ($product = mysqli_fetch_assoc($result)): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card product-card">
                        <?php if (!empty($product['image'])): ?>
                            <img src="../uploads/<?php echo htmlspecialchars($product['image']); ?>" class="card-img-top product-image" alt="Product Image">
                        <?php else: ?>
                            <img src="https://via.placeholder.com/400x200?text=No+Image" class="card-img-top product-image" alt="No Image">
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                            <p class="card-text mb-1"><strong>Price:</strong> $<?php echo number_format($product['price'], 2); ?></p>
                            <p class="card-text mb-1"><strong>Category:</strong> <?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?></p>
                            <span class="badge <?php echo $product['available'] ? 'badge-available' : 'badge-unavailable'; ?>">
                                <?php echo $product['available'] ? 'Available' : 'Unavailable'; ?>
                            </span>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
