<?php
session_start();
require_once '../connetionDB/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get user details
$user_id = $_SESSION['user_id'];
$user_query = "SELECT * FROM users WHERE id = $user_id";
$user_result = mysqli_query($conn, $user_query);
$user = mysqli_fetch_assoc($user_result);

// Pagination settings
$items_per_page = 6; // Number of products per page
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) $current_page = 1;
$offset = ($current_page - 1) * $items_per_page;

// Get total number of products
$total_products_query = "SELECT COUNT(*) as total FROM products WHERE available = TRUE";
$total_products_result = mysqli_query($conn, $total_products_query);
$total_products = mysqli_fetch_assoc($total_products_result)['total'];
$total_pages = ceil($total_products / $items_per_page);

// Get all available products with pagination
$products_query = "SELECT p.*, c.name as category_name 
                  FROM products p 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  WHERE p.available = TRUE
                  LIMIT $offset, $items_per_page";
$products_result = mysqli_query($conn, $products_query);
$products = mysqli_fetch_all($products_result, MYSQLI_ASSOC);

// Get categories for filtering
$categories_query = "SELECT * FROM categories";
$categories_result = mysqli_query($conn, $categories_query);
$categories = mysqli_fetch_all($categories_result, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cafeteria - Home</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #6c5ce7;
            --secondary-color: #a29bfe;
            --accent-color: #fd79a8;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
        }
        
        body {
            background-color: #f5f6fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar {
            background-color: var(--primary-color);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .navbar-brand {
            font-weight: 700;
            color: white !important;
        }
        
        .nav-link {
            color: rgba(255, 255, 255, 0.8) !important;
        }
        
        .nav-link:hover, .nav-link.active {
            color: white !important;
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
        }
        
        .product-img {
            height: 180px;
            object-fit: cover;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
        }
        
        .badge-category {
            background-color: var(--secondary-color);
            color: white;
        }
        
        .order-summary {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 20px;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: #5649d2;
            border-color: #5649d2;
        }
        
        .quantity-btn {
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background-color: var(--light-color);
            color: var(--dark-color);
            border: none;
        }
        
        .quantity-input {
            width: 50px;
            text-align: center;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        
        .latest-orders {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .pagination {
    margin-top: 20px;
}

.page-item.active .page-link {
    background-color: var(--primary-color);
    border-color: var(--primary-color);
}

.page-link {
    color: var(--primary-color);
}

.page-link:hover {
    color: var(--primary-color);
    background-color: var(--secondary-color);
    border-color: var(--secondary-color);
}
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="user_home.php">Cafeteria</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="user_home.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="my_orders.php">My Orders</a>
                    </li>
                </ul>
                <div class="d-flex align-items-center">
                    <span class="text-white me-3">Welcome, <?php echo htmlspecialchars($user['name']); ?></span>
                    <a href="logout.php" class="btn btn-outline-light">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mb-5">
        <div class="row">
            <!-- Main Content Area -->
            <div class="col-lg-8">
                <!-- Category Filter -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Filter by Category</h5>
                        <div class="d-flex flex-wrap">
                            <button class="btn btn-sm btn-outline-secondary me-2 mb-2 filter-btn" data-category="all">All</button>
                            <?php foreach ($categories as $category): ?>
                                <button class="btn btn-sm btn-outline-secondary me-2 mb-2 filter-btn" data-category="<?php echo $category['id']; ?>">
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Products Grid -->
                <div class="row" id="products-container">
                <?php foreach ($products as $product): ?>
<div class="col-md-4 mb-4 product-item" 
     data-category="<?php echo $product['category_id'] ?? '0'; ?>"
     data-product-id="<?php echo $product['id']; ?>"
     data-product-price="<?php echo number_format($product['price'], 2, '.', ''); ?>">
    <div class="card h-100">
        <img src="<?php echo 'http://localhost/php_project/' . htmlspecialchars($product['image']); ?>" class="card-img-top product-img">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start">
                <h5 class="card-title mb-1"><?php echo htmlspecialchars($product['name']); ?></h5>
                <span class="badge bg-primary price-display">
                    <?php echo number_format($product['price'], 2); ?> EGP
                </span>
            </div>
            <?php if (!empty($product['category_name'])): ?>
                <span class="badge badge-category mb-2"><?php echo htmlspecialchars($product['category_name']); ?></span>
            <?php endif; ?>
            <div class="d-flex align-items-center mt-3">
                <button class="quantity-btn minus-btn" data-product="<?php echo $product['id']; ?>">
                    <i class="fas fa-minus"></i>
                </button>
                <input type="number" class="quantity-input mx-2" id="quantity-<?php echo $product['id']; ?>" value="0" min="0">
                <button class="quantity-btn plus-btn" data-product="<?php echo $product['id']; ?>">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
            <div class="mt-2">
                <textarea class="form-control form-control-sm note-input" id="note-<?php echo $product['id']; ?>" rows="2" placeholder="Add note (e.g., extra sugar)"></textarea>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>
                </div>
            </div>

            
         <!-- Order Summary Sidebar -->
<div class="col-lg-4">
    <div class="order-summary p-4 mb-4">
        <h4 class="mb-4 d-flex justify-content-between align-items-center">
            <span>Order Summary</span>
            <span class="badge bg-primary" id="item-count">0 items</span>
        </h4>
        
        <!-- Selected Items List -->
        <div id="selected-items" class="mb-3" style="max-height: 300px; overflow-y: auto;">
            <div class="text-center py-3 text-muted">
                <i class="fas fa-shopping-basket fa-2x mb-2"></i>
                <p>Your basket is empty</p>
            </div>
        </div>
        
        <hr>
        
        <!-- Total Price -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Total Amount:</h5>
            <h4 class="mb-0 text-primary" id="total-price">0.00 EGP</h4>
        </div>
        
        <!-- Room Selection -->
        <div class="mb-3">
            <label for="room-select" class="form-label fw-bold">Select Room</label>
            <select class="form-select form-select-lg" id="room-select" required>
                <option value="" disabled selected>Choose your room</option>
                <?php 
                // Generate room options (2010-2030 as example)
                for ($i = 2010; $i <= 2030; $i++) {
                    $selected = ($i == $user['room_number']) ? 'selected' : '';
                    echo "<option value='$i' $selected>Room $i</option>";
                }
                ?>
            </select>
        </div>
        
        <!-- Confirm Order Button -->
        <button class="btn btn-primary btn-lg w-100 py-3 fw-bold" id="confirm-order">
            <i class="fas fa-paper-plane me-2"></i> Confirm Order
        </button>
    </div>
    
    <!-- Latest Orders Section -->
    <div class="latest-orders p-4">
        <h5 class="mb-3 d-flex justify-content-between align-items-center">
            <span>Latest Orders</span>
            <a href="my_orders.php" class="btn btn-sm btn-outline-primary">View All</a>
        </h5>
        
        <div id="latest-orders-list">
            <?php
            // Get latest 3 orders for the user
            $orders_query = "SELECT o.id, o.total_price, o.status, o.created_at, 
                            COUNT(oi.id) as item_count
                            FROM orders o
                            LEFT JOIN order_items oi ON o.id = oi.order_id
                            WHERE o.user_id = $user_id
                            GROUP BY o.id
                            ORDER BY o.created_at DESC 
                            LIMIT 3";
            $orders_result = mysqli_query($conn, $orders_query);
            
            if (mysqli_num_rows($orders_result) === 0) {
                echo '<div class="text-center py-3 text-muted">
                        <i class="fas fa-clock fa-2x mb-2"></i>
                        <p>No recent orders</p>
                      </div>';
            } else {
                while ($order = mysqli_fetch_assoc($orders_result)) {
                    $status_class = 'text-warning';
                    $status_icon = 'fa-spinner';
                    if ($order['status'] == 'out for delivery') {
                        $status_class = 'text-info';
                        $status_icon = 'fa-truck';
                    } elseif ($order['status'] == 'done') {
                        $status_class = 'text-success';
                        $status_icon = 'fa-check-circle';
                    }
                    
                    echo '<div class="order-card mb-3 p-3 border rounded">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-bold">#' . $order['id'] . '</span>
                                <span class="badge bg-light text-dark">
                                    ' . $order['item_count'] . ' ' . ($order['item_count'] == 1 ? 'item' : 'items') . '
                                </span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span><i class="far fa-calendar-alt me-2"></i>' . date('M j, Y g:i A', strtotime($order['created_at'])) . '</span>
                                <span class="fw-bold">' . number_format($order['total_price'], 2) . ' EGP</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="' . $status_class . '">
                                    <i class="fas ' . $status_icon . ' me-2"></i>' . ucfirst($order['status']) . '
                                </span>';
                    
                    // if ($order['status'] == 'processing') {
                    //     echo '<button class="btn btn-sm btn-outline-danger cancel-order" data-order="' . $order['id'] . '">
                    //             <i class="fas fa-times me-1"></i> Cancel
                    //           </button>';
                    // }
                    
                    echo '</div>
                          </div>';
                }
            }
            ?>
        </div>
    </div>
</div>
<!-- Pagination -->
<nav aria-label="Page navigation" class="mt-4">
    <ul class="pagination justify-content-center">
        <?php if ($current_page > 1): ?>
            <li class="page-item">
                <a class="page-link" href="?page=<?= $current_page - 1 ?>" aria-label="Previous">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>
        <?php endif; ?>
        
        <?php 
        // Show page numbers
        $start_page = max(1, $current_page - 2);
        $end_page = min($total_pages, $current_page + 2);
        
        if ($start_page > 1) {
            echo '<li class="page-item"><a class="page-link" href="?page=1">1</a></li>';
            if ($start_page > 2) {
                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
        }
        
        for ($i = $start_page; $i <= $end_page; $i++): ?>
            <li class="page-item <?= $i == $current_page ? 'active' : '' ?>">
                <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
            </li>
        <?php endfor; 
        
        if ($end_page < $total_pages) {
            if ($end_page < $total_pages - 1) {
                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
            echo '<li class="page-item"><a class="page-link" href="?page='.$total_pages.'">'.$total_pages.'</a></li>';
        }
        ?>
        
        <?php if ($current_page < $total_pages): ?>
            <li class="page-item">
                <a class="page-link" href="?page=<?= $current_page + 1 ?>" aria-label="Next">
                    <span aria-hidden="true">&raquo;</span>
                </a>
            </li>
        <?php endif; ?>
    </ul>
</nav>
    <!-- jQuery and Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
    $(document).ready(function() {
    let cart = [];
    
    // Category filter
    $('.filter-btn').click(function() {
        const category = $(this).data('category');
        $('.filter-btn').removeClass('active');
        $(this).addClass('active');
        
        if (category === 'all') {
            $('.product-item').show();
        } else {
            $('.product-item').hide();
            $(`.product-item[data-category="${category}"]`).show();
        }
    });
    
    // Plus button
    $('.plus-btn').click(function() {
        const productId = $(this).data('product');
        const productCard = $(this).closest('.product-item');
        const productPrice = parseFloat(productCard.data('product-price'));
        const input = $(`#quantity-${productId}`);
        input.val(parseInt(input.val()) + 1);
        updateCart(productId, productPrice, parseInt(input.val()), $(`#note-${productId}`).val());
        updateOrderSummary();
    });
    
    // Minus button
    $('.minus-btn').click(function() {
        const productId = $(this).data('product');
        const productCard = $(this).closest('.product-item');
        const productPrice = parseFloat(productCard.data('product-price'));
        const input = $(`#quantity-${productId}`);
        const newQty = Math.max(parseInt(input.val()) - 1, 0);
        input.val(newQty);
        updateCart(productId, productPrice, newQty, $(`#note-${productId}`).val());
        updateOrderSummary();
    });
    
    // Quantity input change
    $('.quantity-input').change(function() {
        const productId = $(this).attr('id').replace('quantity-', '');
        const productCard = $(this).closest('.product-item');
        const productPrice = parseFloat(productCard.data('product-price'));
        const newQty = Math.max(parseInt($(this).val()) || 0, 0);
        $(this).val(newQty);
        updateCart(productId, productPrice, newQty, $(`#note-${productId}`).val());
        updateOrderSummary();
    });
    
    // Note input change
    $('.note-input').on('input', function() {
        const productId = $(this).attr('id').replace('note-', '');
        const quantity = parseInt($(`#quantity-${productId}`).val()) || 0;
        if (quantity > 0) {
            const productCard = $(this).closest('.product-item');
            const productPrice = parseFloat(productCard.data('product-price'));
            updateCart(productId, productPrice, quantity, $(this).val());
            updateOrderSummary();
        }
    });
    
    // Update cart
    function updateCart(productId, price, quantity, note) {
        const productCard = $(`.product-item[data-product-id="${productId}"]`);
        const productName = productCard.find('.card-title').text();
        const productImage = productCard.find('.product-img').attr('src');
        
        const existingItemIndex = cart.findIndex(item => item.productId == productId);
        
        if (quantity > 0) {
            const cartItem = {
                productId: productId,
                name: productName,
                price: price,
                quantity: quantity,
                note: note,
                image: productImage
            };
            
            if (existingItemIndex >= 0) {
                cart[existingItemIndex] = cartItem;
            } else {
                cart.push(cartItem);
            }
        } else if (existingItemIndex >= 0) {
            cart.splice(existingItemIndex, 1);
        }
    }
    
    // Update order summary
    function updateOrderSummary() {
        let total = 0;
        let itemsHtml = '';
        
        cart.forEach(item => {
            const itemTotal = item.price * item.quantity;
            total += itemTotal;
            
            itemsHtml += `
                <div class="cart-item mb-3 pb-2 border-bottom">
                    <div class="d-flex align-items-start">
                        <img src="${item.image}" class="rounded me-3" width="60" height="60" style="object-fit: cover">
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between">
                                <h6 class="mb-1">${item.name}</h6>
                                <span class="text-primary fw-bold">${itemTotal.toFixed(2)} EGP</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">${item.price.toFixed(2)} EGP each</small>
                                <div class="quantity-controls">
                                    <button class="btn btn-sm btn-outline-secondary minus-summary" data-product="${item.productId}">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <span class="mx-2">${item.quantity}</span>
                                    <button class="btn btn-sm btn-outline-secondary plus-summary" data-product="${item.productId}">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                            ${item.note ? `<div class="mt-1 small text-muted"><i class="fas fa-comment-alt me-1"></i>${item.note}</div>` : ''}
                        </div>
                    </div>
                </div>
            `;
        });
        
        if (cart.length === 0) {
            itemsHtml = `
                <div class="text-center py-3 text-muted">
                    <i class="fas fa-shopping-basket fa-2x mb-2"></i>
                    <p>Your basket is empty</p>
                </div>
            `;
        }
        
        $('#selected-items').html(itemsHtml);
        $('#total-price').text(total.toFixed(2) + ' EGP');
        $('#item-count').text(cart.reduce((sum, item) => sum + item.quantity, 0) + ' ' + (cart.reduce((sum, item) => sum + item.quantity, 0) === 1 ? 'item' : 'items'));
        
        $('#confirm-order').prop('disabled', cart.length === 0);
    }
    
    // Handle quantity changes from summary
    $(document).on('click', '.plus-summary', function() {
        const productId = $(this).data('product');
        const input = $(`#quantity-${productId}`);
        input.val(parseInt(input.val()) + 1);
        const productCard = $(`.product-item[data-product-id="${productId}"]`);
        const productPrice = parseFloat(productCard.data('product-price'));
        updateCart(productId, productPrice, parseInt(input.val()), $(`#note-${productId}`).val());
        updateOrderSummary();
    });
    
    $(document).on('click', '.minus-summary', function() {
        const productId = $(this).data('product');
        const input = $(`#quantity-${productId}`);
        const newQty = Math.max(parseInt(input.val()) - 1, 0);
        input.val(newQty);
        const productCard = $(`.product-item[data-product-id="${productId}"]`);
        const productPrice = parseFloat(productCard.data('product-price'));
        updateCart(productId, productPrice, newQty, $(`#note-${productId}`).val());
        updateOrderSummary();
    });
    
  // Confirm order
$('#confirm-order').click(function() {
    const room = $('#room-select').val();
    
    if (!room) {
        alert('Please select your room number');
        $('#room-select').focus();
        return;
    }
    
    if (cart.length === 0) {
        alert('Please add at least one item to your order');
        return;
    }
    
    // Calculate total price
    const totalPrice = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    
    // Prepare order items for database
    const orderItems = cart.map(item => ({
        product_id: item.productId,
        quantity: item.quantity,
        note: item.note || ''
    }));
    
    // Submit order via AJAX
    $.ajax({
        url: 'process_order.php',
        method: 'POST',
        data: {
            user_id: <?php echo $user_id; ?>,
            room: room,
            items: JSON.stringify(orderItems),
            total_price: totalPrice.toFixed(2)
        },
        beforeSend: function() {
            $('#confirm-order').html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Processing...');
            $('#confirm-order').prop('disabled', true);
        },
        success: function(response) {
            try {
                const result = JSON.parse(response);
                if (result.success) {
                    alert('Order placed successfully!');
                    // Reset form and cart
                    $('.quantity-input').val(0);
                    $('.note-input').val('');
                    cart = [];
                    updateOrderSummary();
                    location.reload();
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (e) {
                alert('Order placed successfully!');
            }
        },
        error: function() {
            alert('Error submitting order. Please try again.');
        },
        complete: function() {
            $('#confirm-order').html('<i class="fas fa-paper-plane me-2"></i> Confirm Order');
            $('#confirm-order').prop('disabled', false);
        }
    });
});
    
    
//     // Cancel order
//     $(document).on('click', '.cancel-order', function(e) {
//         e.preventDefault();
//         const orderId = $(this).data('order');
        
//         if (confirm('Are you sure you want to cancel this order?')) {
//             $.ajax({
//                 url: 'cancel_order.php',
//                 method: 'POST',
//                 data: { order_id: orderId },
//                 success: function(response) {
//                     const result = JSON.parse(response);
//                     if (result.success) {
//                         alert('Order cancelled successfully');
//                         location.reload();
//                     } else {
//                         alert('Error: ' + result.message);
//                     }
//                 }
//             });
//         }
//     });
});
</script>
</body>
</html>