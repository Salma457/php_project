<?php
session_start();
require_once '../connetionDB/config.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../user/login.php");
    exit();
}

// Fetch all orders with user information
$query = "SELECT o.id, o.created_at, o.total_price, o.status, 
                 u.name as user_name, u.room_number
          FROM orders o
          JOIN users u ON o.user_id = u.id
          ORDER BY o.created_at DESC";
$result = mysqli_query($conn, $query);
$orders = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders Management | Cafeteria Admin</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #6c5ce7;
            --primary-hover: #5649c0;
        }
        body {
            background-color: #f8f9fa;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            border: none;
        }
        .card-header {
            background-color: var(--primary-color);
            color: white;
            border-radius: 10px 10px 0 0 !important;
        }
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        .btn-primary:hover {
            background-color: var(--primary-hover);
            border-color: var(--primary-hover);
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .processing {
            background-color: #fff3cd;
            color: #856404;
        }
        .out-for-delivery {
            background-color: #cce5ff;
            color: #004085;
        }
        .done {
            background-color: #d4edda;
            color: #155724;
        }
        .order-item {
            border-bottom: 1px solid #eee;
            padding: 10px 0;
        }
        .order-item:last-child {
            border-bottom: none;
        }
        .search-box {
            position: relative;
            margin-bottom: 20px;
        }
        .search-box i {
            position: absolute;
            top: 12px;
            left: 12px;
            color: #6c757d;
        }
        .search-input {
            padding-left: 35px;
            border-radius: 20px;
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold text-dark">Orders Management</h2>
            <div class="dropdown">
                <button class="btn btn-primary dropdown-toggle" type="button" id="filterDropdown" data-bs-toggle="dropdown">
                    <i class="fas fa-filter me-1"></i> Filter
                </button>
                <ul class="dropdown-menu" aria-labelledby="filterDropdown">
                    <li><a class="dropdown-item" href="?status=all">All Orders</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="?status=processing">Processing</a></li>
                    <li><a class="dropdown-item" href="?status=out for delivery">Out for Delivery</a></li>
                    <li><a class="dropdown-item" href="?status=done">Completed</a></li>
                </ul>
            </div>
        </div>

        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" id="searchInput" class="form-control search-input" placeholder="Search by user name or room number...">
        </div>

        <?php if (empty($orders)): ?>
            <div class="alert alert-info">
                No orders found.
            </div>
        <?php else: ?>
            <div class="row" id="ordersContainer">
                <?php foreach ($orders as $order): ?>
                    <div class="col-md-6 col-lg-4 mb-4 order-card" 
                         data-user="<?= htmlspecialchars(strtolower($order['user_name'])) ?>" 
                         data-room="<?= htmlspecialchars(strtolower($order['room_number'])) ?>"
                         data-status="<?= htmlspecialchars(strtolower($order['status'])) ?>">
                        <div class="card h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-0">Order #<?= $order['id'] ?></h5>
                                    <small class="text-white-50"><?= date('M j, Y g:i A', strtotime($order['created_at'])) ?></small>
                                </div>
                                <span class="status-badge <?= str_replace(' ', '-', $order['status']) ?>">
                                    <?= ucwords($order['status']) ?>
                                </span>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-3">
                                    <div>
                                        <h6 class="mb-1 fw-bold"><?= htmlspecialchars($order['user_name']) ?></h6>
                                        <small class="text-muted">Room: <?= htmlspecialchars($order['room_number']) ?></small>
                                    </div>
                                    <h5 class="text-primary fw-bold">$<?= number_format($order['total_price'], 2) ?></h5>
                                </div>
                                
                                <?php
                                // Fetch order items
                                $items_query = "SELECT p.name, p.price, oi.quantity, oi.note 
                                               FROM order_items oi
                                               JOIN products p ON oi.product_id = p.id
                                               WHERE oi.order_id = ?";
                                $stmt = mysqli_prepare($conn, $items_query);
                                mysqli_stmt_bind_param($stmt, "i", $order['id']);
                                mysqli_stmt_execute($stmt);
                                $items_result = mysqli_stmt_get_result($stmt);
                                $items = mysqli_fetch_all($items_result, MYSQLI_ASSOC);
                                ?>
                                
                                <div class="mb-3">
                                    <?php foreach ($items as $item): ?>
                                        <div class="order-item">
                                            <div class="d-flex justify-content-between">
                                                <span><?= htmlspecialchars($item['name']) ?> × <?= $item['quantity'] ?></span>
                                                <span>$<?= number_format($item['price'] * $item['quantity'], 2) ?></span>
                                            </div>
                                            <?php if (!empty($item['note'])): ?>
                                                <small class="text-muted">Note: <?= htmlspecialchars($item['note']) ?></small>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div class="card-footer bg-white border-top-0">
                                <div class="d-flex justify-content-between">
                                    <form action="update_order_status.php" method="POST" class="me-2">
                                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                            <option value="processing" <?= $order['status'] == 'processing' ? 'selected' : '' ?>>Processing</option>
                                            <option value="out for delivery" <?= $order['status'] == 'out for delivery' ? 'selected' : '' ?>>Out for Delivery</option>
                                            <option value="done" <?= $order['status'] == 'done' ? 'selected' : '' ?>>Completed</option>
                                        </select>
                                    </form>
                                    <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $order['id'] ?>">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Delete Confirmation Modal -->
                    <div class="modal fade" id="deleteModal<?= $order['id'] ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Confirm Deletion</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    Are you sure you want to delete Order #<?= $order['id'] ?>? This action cannot be undone.
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <form action="delete_order.php" method="POST">
                                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                        <button type="submit" class="btn btn-danger">Delete</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Custom JS -->
    <script>
        $(document).ready(function() {
            // Search functionality
            $('#searchInput').on('keyup', function() {
                const searchText = $(this).val().toLowerCase();
                
                $('.order-card').each(function() {
                    const userText = $(this).data('user');
                    const roomText = $(this).data('room');
                    
                    if (userText.includes(searchText) || roomText.includes(searchText)) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });

            // Filter functionality
            const urlParams = new URLSearchParams(window.location.search);
            const statusFilter = urlParams.get('status');
            
            if (statusFilter && statusFilter !== 'all') {
                $('.order-card').each(function() {
                    const orderStatus = $(this).data('status');
                    
                    if (orderStatus !== statusFilter) {
                        $(this).hide();
                    }
                });
            }
        });
    </script>
</body>
</html>