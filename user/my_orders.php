<?php
session_start();
require_once '../connetionDB/config.php';

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];

$itemsPerPage = 5;
$currentPage = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($currentPage - 1) * $itemsPerPage;

$dateFrom = isset($_GET['date_from']) && $_GET['date_from'] ? $_GET['date_from'] : null;
$dateTo = isset($_GET['date_to']) && $_GET['date_to'] ? $_GET['date_to'] : null;

$baseSql = "SELECT id, total_price, status, created_at FROM orders WHERE user_id = ?";
$baseParams = [$userId];
$baseTypes = "i";

if ($dateFrom) {
    $baseSql .= " AND DATE(created_at) >= ?";
    $baseParams[] = $dateFrom;
    $baseTypes .= "s";
}
if ($dateTo) {
    $dateToFormatted = date('Y-m-d H:i:s', strtotime($dateTo . ' 23:59:59'));
    $baseSql .= " AND created_at <= ?";
    $baseParams[] = $dateToFormatted;
    $baseTypes .= "s";
}

$countSql = "SELECT COUNT(*) AS total_count FROM orders WHERE user_id = ?";
$countParams = [$userId];
$countTypes = "i";

if ($dateFrom) {
    $countSql .= " AND DATE(created_at) >= ?";
    $countParams[] = $dateFrom;
    $countTypes .= "s";
}
if ($dateTo) {
     $dateToFormatted = date('Y-m-d H:i:s', strtotime($dateTo . ' 23:59:59'));
    $countSql .= " AND created_at <= ?";
    $countParams[] = $dateToFormatted;
    $countTypes .= "s";
}

$countStmt = mysqli_prepare($conn, $countSql);
$totalOrders = 0;
if ($countStmt) {
    mysqli_stmt_bind_param($countStmt, $countTypes, ...$countParams);
    mysqli_stmt_execute($countStmt);
    $countResult = mysqli_stmt_get_result($countStmt);
    $countRow = mysqli_fetch_assoc($countResult);
    $totalOrders = $countRow['total_count'];
    mysqli_stmt_close($countStmt);
} else {
    error_log("Error preparing count statement: " . mysqli_error($conn));
}

$totalPages = ceil($totalOrders / $itemsPerPage);

if ($totalOrders == 0) {
     $currentPage = 1;
     $offset = 0;
     $totalPages = 1;
} elseif ($currentPage < 1) {
    $currentPage = 1;
    $offset = ($currentPage - 1) * $itemsPerPage;
} elseif ($currentPage > $totalPages) {
    $currentPage = $totalPages;
    $offset = ($currentPage - 1) * $itemsPerPage;
}

$orders = [];
$totalAmountDisplayed = 0;

$sql = $baseSql;
$sql .= " ORDER BY created_at DESC LIMIT ?, ?";
$params = array_merge($baseParams, [$offset, $itemsPerPage]);
$types = $baseTypes . "ii";

$stmt = mysqli_prepare($conn, $sql);
if ($stmt) {
     mysqli_stmt_bind_param($stmt, $types, ...$params);

    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($result)) {
        $orderItemsSql = "SELECT oi.quantity, oi.note, p.name AS product_name, p.price AS product_price, p.image AS image_p
                          FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?";
        $itemStmt = mysqli_prepare($conn, $orderItemsSql);
        $items = [];
        if ($itemStmt) {
            mysqli_stmt_bind_param($itemStmt, "i", $row['id']);
            mysqli_stmt_execute($itemStmt);
            $itemResult = mysqli_stmt_get_result($itemStmt);
            while ($itemRow = mysqli_fetch_assoc($itemResult)) {
                $items[] = $itemRow;
            }
            mysqli_stmt_close($itemStmt);
        } else {
            error_log("Error preparing item statement: " . mysqli_error($conn));
        }
        $row['items'] = $items;
        $orders[] = $row;
        $totalAmountDisplayed += $row['total_price'];
    }
    mysqli_stmt_close($stmt);
} else {
    error_log("Error preparing order statement: " . mysqli_error($conn));
}

mysqli_close($conn);

function buildPaginationUrl($page, $dateFrom, $dateTo) {
    $queryParams = ['page' => $page];
    if ($dateFrom) {
        $queryParams['date_from'] = $dateFrom;
    }
    if ($dateTo) {
        $queryParams['date_to'] = $dateTo;
    }
    return '?' . http_build_query($queryParams);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Arial', sans-serif;
        }
        .container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            margin-top: 30px;
            margin-bottom: 30px;
        }
        h1, h2 {
            color: #343a40;
            text-align: center;
            margin-bottom: 20px;
        }
         h1 {
            color: #0d6efd;
         }
        .filter-form {
            background-color: #e9ecef;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);
        }
        .accordion-button {
            font-weight: 500;
            transition: background-color 0.3s ease;
            align-items: center;
            padding: 15px 20px;
        }
        .accordion-button:not(.collapsed) {
            background-color: #cfe2ff;
            color: #0a58ca;
            box-shadow: inset 0 -1px 0 rgba(0, 0, 0, 0.125);
        }
        .accordion-button:focus {
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }
        .accordion-item {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            margin-bottom: 15px;
            overflow: hidden;
            transition: box-shadow 0.3s ease;
        }
        .accordion-item:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }
        .order-summary {
            display: flex;
            justify-content: space-between;
            width: 100%;
            padding-right: 40px;
            font-size: 0.95rem;
             align-items: center;
        }
        .order-summary > div {
            flex: 1 1 0;
            padding: 0 10px;
            text-align: left;
        }
         .order-summary > div:first-child {
            flex: 0 0 180px;
            padding-left: 0;
            font-size: 0.9rem;
            color: #6c757d;
        }
        .order-summary > div:nth-child(2) {
             flex: 0 0 120px;
             text-align: center;
        }
         .order-summary > div:nth-child(3) {
             flex: 0 0 100px;
             text-align: right;
             font-size: 1.1rem;
         }
        .order-summary > div:last-child {
            flex: 0 0 80px;
            text-align: right;
            padding-right: 0;
        }
        .order-summary .status-badge {
            min-width: 100px;
            text-align: center;
            font-size: 0.85rem;
            padding: 0.4em 0.6em;
        }
        .accordion-body {
            background-color: #f8f9fa;
            padding: 20px;
            border-top: 1px solid #dee2e6;
        }
        .order-item {
            display: flex;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px dashed #e0e0e0;
        }
        .order-item:last-child {
            border-bottom: none;
        }
        .order-item img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            margin-right: 20px;
             border: 1px solid #dee2e6;
             flex-shrink: 0;
        }
        .item-details {
            flex-grow: 1;
            font-size: 1rem;
        }
        .item-details strong {
            display: block;
            margin-bottom: 3px;
             color: #343a40;
        }
         .item-details span {
             font-size: 0.9rem;
             color: #6c757d;
         }
        .item-details .note {
            font-style: italic;
            color: #6c757d;
            font-size: 0.85rem;
            margin-top: 5px;
             padding-left: 10px;
             border-left: 2px solid #0d6efd;
        }
        .item-price {
            min-width: 90px;
            text-align: right;
            font-weight: bold;
            color: #198754;
            font-size: 1rem;
             flex-shrink: 0;
        }
        .total-amount-display {
            font-size: 1.6rem;
            font-weight: bold;
            color: #198754;
            margin-top: 30px;
            text-align: right;
            padding-top: 20px;
            border-top: 2px dashed #dee2e6;
        }
        .btn-cancel {
            font-size: 0.85rem;
            padding: 0.3rem 0.75rem;
             border-radius: 5px;
             transition: background-color 0.2s ease-in-out, border-color 0.2s ease-in-out, color 0.2s ease-in-out;
        }
         .btn-cancel:hover {
             background-color: #dc3545;
             color: white;
             border-color: #dc3545;
         }
        .modern-btn {
            border-radius: 25px;
            padding: 10px 25px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            font-weight: 500;
        }
        .modern-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        }
         .pagination .page-link {
             color: #0d6efd;
         }
         .pagination .page-item.active .page-link {
             background-color: #0d6efd;
             border-color: #0d6efd;
             color: white;
         }
         .pagination .page-item.disabled .page-link {
             color: #6c757d;
         }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .container {
            animation: fadeIn 0.5s ease-out;
        }
         .accordion-item {
             animation: fadeIn 0.6s ease-out forwards;
         }
          .accordion-item:nth-child(2) { animation-delay: 0.1s; }
          .accordion-item:nth-child(3) { animation-delay: 0.2s; }


        .accordion-button .order-summary > div {
            transition: color 0.2s ease;
        }
        .accordion-button:hover .order-summary > div {
            color: #0a58ca;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><i class="fas fa-receipt me-2"></i>My Orders</h1>

        <form method="GET" action="my_orders.php" class="filter-form mb-4">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="date_from" class="form-label"><i class="far fa-calendar-alt me-1 opacity-75"></i>Date From:</label>
                    <input type="date" class="form-control" id="date_from" name="date_from" value="<?php echo htmlspecialchars($dateFrom ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <label for="date_to" class="form-label"><i class="far fa-calendar-alt me-1 opacity-75"></i>Date To:</label>
                    <input type="date" class="form-control" id="date_to" name="date_to" value="<?php echo htmlspecialchars($dateTo ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100 modern-btn"><i class="fas fa-filter me-1"></i>Filter Orders</button>
                </div>
            </div>
        </form>

        <div class="accordion" id="ordersAccordion">
            <?php if (!empty($orders)): ?>
                <?php foreach ($orders as $index => $order): ?>
                    <?php
                        $statusBadgeClass = 'bg-secondary';
                        if ($order['status'] === 'processing') {
                            $statusBadgeClass = 'bg-warning text-dark';
                        } elseif ($order['status'] === 'out for delivery') {
                            $statusBadgeClass = 'bg-info text-dark';
                        } elseif ($order['status'] === 'done') {
                            $statusBadgeClass = 'bg-success';
                        } elseif ($order['status'] === 'cancelled') {
                             $statusBadgeClass = 'bg-danger';
                        }
                        $orderIdHtml = "order-" . htmlspecialchars($order['id']);
                        $collapseId = "collapse-" . $orderIdHtml;
                        $headerId = "header-" . $orderIdHtml;
                    ?>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="<?php echo $headerId; ?>">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#<?php echo $collapseId; ?>" aria-expanded="false" aria-controls="<?php echo $collapseId; ?>">
                                <div class="order-summary">
                                     <div>
                                            Order #<?php echo htmlspecialchars($order['id']); ?>
                                            <div class="text-muted small">
                                                 <i class="far fa-calendar-alt me-1 opacity-75"></i>
                                                <?php echo htmlspecialchars(date('Y-m-d', strtotime($order['created_at']))); ?>
                                                 <i class="far fa-clock ms-2 me-1 opacity-75"></i>
                                                <?php echo htmlspecialchars(date('h:i A', strtotime($order['created_at']))); ?>
                                            </div>
                                     </div>
                                    <div>
                                        <span class="badge rounded-pill status-badge <?php echo $statusBadgeClass; ?>">
                                            <?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $order['status']))); ?>
                                        </span>
                                    </div>
                                    <div>
                                        <strong class="text-success">
                                            <?php echo htmlspecialchars(number_format($order['total_price'], 2)); ?> EGP
                                        </strong>
                                    </div>
                                    <div class="action-column">
                                        <?php if ($order['status'] === 'processing'): ?>
                                            <a href="#" class="btn btn-sm btn-outline-danger btn-cancel"
                                               onclick="return postCancelOrder('<?php echo $order['id']; ?>', '<?php echo htmlspecialchars($dateFrom ?? ''); ?>', '<?php echo htmlspecialchars($dateTo ?? ''); ?>', '<?php echo $currentPage; ?>');">
                                                <i class="fas fa-times me-1"></i>Cancel
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted fst-italic small">-</span> <?php endif; ?>
                                    </div>
                                </div>
                            </button>
                        </h2>
                        <div id="<?php echo $collapseId; ?>" class="accordion-collapse collapse" aria-labelledby="<?php echo $headerId; ?>" data-bs-parent="#ordersAccordion">
                            <div class="accordion-body">
                                <?php if (!empty($order['items'])): ?>
                                    <h5 class="mb-3"><i class="fas fa-list me-1 opacity-75"></i>Items:</h5>
                                    <ul class="list-unstyled">
                                        <?php foreach ($order['items'] as $item): ?>
                                            <li class="order-item">
                                                <img src="<?php echo htmlspecialchars($item['image_p'] ?? 'placeholder.png'); ?>"
                                                     alt="<?php echo htmlspecialchars($item['product_name']); ?>"
                                                     onerror="this.onerror=null; this.src='image_p';" > <div class="item-details">
                                                    <strong><?php echo htmlspecialchars($item['product_name']); ?></strong>
                                                    <span><?php echo htmlspecialchars($item['quantity']); ?> x <?php echo htmlspecialchars(number_format($item['product_price'], 2)); ?> EGP</span>
                                                    <?php if (!empty($item['note'])): ?>
                                                        <div class="note"><i class="far fa-sticky-note me-1 opacity-75"></i>Note: <?php echo htmlspecialchars($item['note']); ?></div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="item-price">
                                                    <?php echo htmlspecialchars(number_format($item['quantity'] * $item['product_price'], 2)); ?> EGP
                                                </div>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    <p class="text-muted text-center">No items found for this order.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert alert-info text-center" role="alert">
                    <i class="fas fa-info-circle me-2"></i>You have no orders matching the selected criteria.
                </div>
            <?php endif; ?>
        </div>

        <?php if ($totalOrders > 0 || ($totalOrders == 0 && ($dateFrom || $dateTo))): ?>
             <div class="total-amount-display">
                 <i class="fas fa-dollar-sign me-1"></i>Total Displayed: <?php echo htmlspecialchars(number_format($totalAmountDisplayed, 2)); ?> EGP
             </div>

            <?php if ($totalPages > 1): ?>
                <nav aria-label="Orders pagination" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?php echo ($currentPage <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="<?php echo buildPaginationUrl($currentPage - 1, $dateFrom, $dateTo); ?>" tabindex="-1" aria-disabled="<?php echo ($currentPage <= 1) ? 'true' : 'false'; ?>"><i class="fas fa-angle-left"></i> Previous</a>
                        </li>
                        <?php
                        $startPage = max(1, $currentPage - 2);
                        $endPage = min($totalPages, $currentPage + 2);

                        if ($startPage > 1) {
                            echo '<li class="page-item"><a class="page-link" href="' . buildPaginationUrl(1, $dateFrom, $dateTo) . '">1</a></li>';
                            if ($startPage > 2) {
                                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                            }
                        }

                        for ($i = $startPage; $i <= $endPage; $i++): ?>
                            <li class="page-item <?php echo ($i === $currentPage) ? 'active' : ''; ?>" aria-current="<?php echo ($i === $currentPage) ? 'page' : ''; ?>">
                                <a class="page-link" href="<?php echo buildPaginationUrl($i, $dateFrom, $dateTo); ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor;

                         if ($endPage < $totalPages) {
                             if ($endPage < $totalPages - 1) {
                                 echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                             }
                             echo '<li class="page-item"><a class="page-link" href="' . buildPaginationUrl($totalPages, $dateFrom, $dateTo) . '">' . $totalPages . '</a></li>';
                         }
                        ?>
                        <li class="page-item <?php echo ($currentPage >= $totalPages) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="<?php echo buildPaginationUrl($currentPage + 1, $dateFrom, $dateTo); ?>">Next <i class="fas fa-angle-right"></i></a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php endif; ?>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.transition = 'opacity 0.5s';
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 500);
                }, 5000);
            });
        });

        function postCancelOrder(orderId, dateFrom, dateTo, page) {
            if (!confirm('Are you sure you want to cancel this order?')) return false;

            const formData = new FormData();
            formData.append('action', 'cancel');
            formData.append('order_id', orderId);

            fetch('cancellation_order.php', {
                method: 'POST',
                body: formData,
            })
            .then(response => {
                 if (!response.ok) {
                     return response.text().then(text => { throw new Error(`HTTP error! status: ${response.status}, Response: ${text}`); });
                 }
                 return response.text();
             })
            .then(data => {
                console.log('Cancellation response:', data);
                let redirectUrl = 'my_orders.php?page=' + page;
                if (dateFrom) redirectUrl += '&date_from=' + encodeURIComponent(dateFrom);
                if (dateTo) redirectUrl += '&date_to=' + encodeURIComponent(dateTo);

                window.location.href = redirectUrl;
            })
            .catch(error => {
                 console.error('Error during cancellation:', error);
                 alert('An error occurred while cancelling the order. Details: ' + error.message);
            });

            return false;
        }
    </script>
</body>
</html>