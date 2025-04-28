<?php
// Check if user is logged in and is admin
$is_admin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
?>

<nav class="navbar navbar-expand-lg navbar-dark mb-4" style="background-color: #6c5ce7;">
    <div class="container">
        <a class="navbar-brand fw-bold" href="dashboard.php">Cafeteria Admin</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <?php if ($is_admin): ?>
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="../admin/manual_order.php">Manual Order</a>
                </li>
                <li class="nav-item">
                <a class="nav-link" href="../user/products_list.php">Products</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../user/list_all_users.php">Users</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../admin/orders.php">Orders</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../admin/checks.php">Checks</a>
                </li>
                <?php endif; ?>
            </ul>
            <div class="d-flex align-items-center">
                <span class="text-white me-3">Welcome, <?= htmlspecialchars($_SESSION['user_name'] ?? 'Admin') ?></span>
                <a href="../user/logout.php" class="btn btn-outline-light">
                    <i class="fas fa-sign-out-alt me-1"></i> Logout
                </a>
            </div>
        </div>
    </div>
</nav>