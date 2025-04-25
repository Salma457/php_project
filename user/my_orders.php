<?php

require_once '../connetionDB/config.php'; 

// Check if connection is successful
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}


$userId = 1; 




$dateFrom = isset($_GET['date_from']) && $_GET['date_from'] ? $_GET['date_from'] : null;
$dateTo = isset($_GET['date_to']) && $_GET['date_to'] ? $_GET['date_to'] : null;


$orders = [];
$totalAmountDisplayed = 0;

$sql = "SELECT id, total_price, status, created_at FROM orders WHERE user_id = ?";
$params = [$userId];
$types = "i";

if ($dateFrom) {
    $sql .= " AND DATE(created_at) >= ?";
    $params[] = $dateFrom;
    $types .= "s";
}
if ($dateTo) {
    $dateToFormatted = date('Y-m-d', strtotime($dateTo . ' +1 day'));
    $sql .= " AND created_at < ?";
    $params[] = $dateToFormatted;
    $types .= "s";
}
$sql .= " ORDER BY created_at DESC";

$stmt = mysqli_prepare($conn, $sql);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($result)) {
        $orderItemsSql = "SELECT oi.quantity, oi.note, p.name AS product_name, p.price AS product_price, p.image AS product_image
                          FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?";
        $itemStmt = mysqli_prepare($conn, $orderItemsSql);
        if ($itemStmt) {
            mysqli_stmt_bind_param($itemStmt, "i", $row['id']);
            mysqli_stmt_execute($itemStmt);
            $itemResult = mysqli_stmt_get_result($itemStmt);
            $items = [];
            while ($itemRow = mysqli_fetch_assoc($itemResult)) {
                $items[] = $itemRow;
            }
            $row['items'] = $items;
            mysqli_stmt_close($itemStmt);
        } else {
            error_log("Error preparing item statement: " . mysqli_error($conn));
            $row['items'] = [];
        }
        $orders[] = $row;
        $totalAmountDisplayed += $row['total_price'];
    }
    mysqli_stmt_close($stmt);
} else {
    error_log("Error preparing order statement: " . mysqli_error($conn));
}

mysqli_close($conn);
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
        }
        .accordion-button:not(.collapsed) {
            background-color: #e7f1ff;
            color: #0a58ca;
            box-shadow: inset 0 -1px 0 rgba(0, 0, 0, 0.125);
        }
        .accordion-button:focus {
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }
        .accordion-item {
            border: 1px solid #dee2e6;
            border-radius: 5px;
            margin-bottom: 10px;
            overflow: hidden;
            transition: box-shadow 0.3s ease;
        }
        .accordion-item:hover {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }
        .order-summary {
            display: flex;
            justify-content: space-between;
            width: 100%;
            padding-right: 40px;
            font-size: 0.95rem;
        }
        .order-summary > div {
            flex: 1 1 0;
            padding: 0 10px;
            text-align: left;
        }
        .order-summary > div:first-child {
            flex: 0 0 200px;
            padding-left: 0;
        }
        .order-summary > div:last-child {
            flex: 0 0 100px;
            text-align: right;
            padding-right: 0;
        }
        .order-summary .status-badge {
            min-width: 120px;
            text-align: center;
        }
        .accordion-body {
            background-color: #f8f9fa;
            padding: 15px;
            border-top: 1px solid #dee2e6;
        }
        .order-item {
            display: flex;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px dashed #e0e0e0;
        }
        .order-item:last-child {
            border-bottom: none;
        }
        .order-item img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 5px;
            margin-right: 15px;
        }
        .item-details {
            flex-grow: 1;
            font-size: 0.9rem;
        }
        .item-details .note {
            font-style: italic;
            color: #6c757d;
            font-size: 0.85rem;
            margin-top: 3px;
        }
        .item-price {
            min-width: 80px;
            text-align: right;
            font-weight: 500;
            color: #28a745;
        }
        .total-amount-display {
            font-size: 1.5rem;
            font-weight: bold;
            color: #198754;
            margin-top: 30px;
            text-align: right;
            padding-top: 15px;
            border-top: 2px solid #dee2e6;
        }
        .btn-cancel {
            font-size: 0.8rem;
            padding: 0.25rem 0.5rem;
        }
        .modern-btn {
            border-radius: 20px;
            padding: 10px 20px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .modern-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .container {
            animation: fadeIn 0.5s ease-out;
        }
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
                    <label for="date_from" class="form-label">Date From:</label>
                    <input type="date" class="form-control" id="date_from" name="date_from" value="<?php echo htmlspecialchars($dateFrom ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <label for="date_to" class="form-label">Date To:</label>
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
                                        <i class="far fa-calendar-alt me-1 opacity-75"></i>
                                        <?php echo htmlspecialchars(date('Y-m-d h:i A', strtotime($order['created_at']))); ?>
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
           onclick="return postCancelOrder('<?php echo $order['id']; ?>', '<?php echo $dateFrom ?? ''; ?>', '<?php echo $dateTo ?? ''; ?>');">
            <i class="fas fa-times me-1"></i>Cancel
        </a>
    <?php else: ?>
        <span class="text-muted fst-italic">-</span>
    <?php endif; ?>
</div>

<script>
    function postCancelOrder(orderId, dateFrom, dateTo) {
        if (!confirm('Are you sure you want to cancel this order?')) return false;

        const formData = new FormData();
        formData.append('action', 'cancel');
        formData.append('order_id', orderId);
        formData.append('date_from', dateFrom);
        formData.append('date_to', dateTo);

        fetch('cancellation_order.php', {
            method: 'POST',
            body: formData,
        })
        .then(response => response.text())
        .then(data => {
         
            console.log(data);
            location.reload(); 
        })
        .catch(error => console.error('Error:', error));

        return false; 
    }
</script>


                                </div>
                            </button>
                        </h2>
                        <div id="<?php echo $collapseId; ?>" class="accordion-collapse collapse" aria-labelledby="<?php echo $headerId; ?>" data-bs-parent="#ordersAccordion">
                            <div class="accordion-body">
                                <?php if (!empty($order['items'])): ?>
                                    <h5 class="mb-3"><i class="fas fa-list me-1 opacity-75"></i>Items:</h5>
                                    <?php foreach ($order['items'] as $item): ?>
                                        <div class="order-item">
                                            <img src="<?php echo htmlspecialchars($item['product_image'] ?? 'placeholder.png'); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                                            <div class="item-details">
                                                <strong><?php echo htmlspecialchars($item['product_name']); ?></strong>
                                                (<?php echo htmlspecialchars($item['quantity']); ?> x <?php echo htmlspecialchars(number_format($item['product_price'], 2)); ?> EGP)
                                                <?php if (!empty($item['note'])): ?>
                                                    <div class="note"><i class="far fa-sticky-note me-1 opacity-75"></i>Note: <?php echo htmlspecialchars($item['note']); ?></div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="item-price">
                                                <?php echo htmlspecialchars(number_format($item['quantity'] * $item['product_price'], 2)); ?> EGP
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
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

        <?php if (!empty($orders)): ?>
        <div class="total-amount-display">
            <i class="fas fa-dollar-sign me-1"></i>Total : <?php echo htmlspecialchars(number_format($totalAmountDisplayed, 2)); ?> EGP
        </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script>
        // Auto-dismiss alerts after 5 seconds
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
    </script>
</body>
</html>