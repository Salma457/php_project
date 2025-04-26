<!DOCTYPE html>
<html>
<head>
    <title>Checks Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --accent: #4cc9f0;
            --success: #38b000;
            --light: #f8f9fa;
            --dark: #212529;
            --gradient: linear-gradient(135deg, #4361ee 0%, #4cc9f0 100%);
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(-45deg, #f8f9fa, #e9ecef, #dee2e6, #ced4da);
            background-size: 400% 400%;
            min-height: 100vh;
            padding: 20px 0;
            animation: gradientBG 15s ease infinite;
            color: var(--dark);
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .container {
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.08);
            padding: 40px;
            animation: fadeInUp 0.8s cubic-bezier(0.22, 1, 0.36, 1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        h1, h2, h3, h4, h5 {
            font-weight: 700;
            color: var(--dark);
        }

        h1 {
            font-family: 'Playfair Display', serif;
            margin-bottom: 35px;
            position: relative;
            padding-bottom: 15px;
            font-size: 2.5rem;
            background: linear-gradient(90deg, var(--primary), var(--accent));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            text-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        h1:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 80px;
            height: 5px;
            background: var(--gradient);
            border-radius: 3px;
            animation: expandLine 1.2s ease-out forwards;
        }

        @keyframes expandLine {
            from { width: 0; opacity: 0; }
            to { width: 80px; opacity: 1; }
        }

        h2 {
            font-size: 1.8rem;
            margin: 30px 0 20px;
            color: var(--secondary);
            position: relative;
            display: inline-block;
        }

        h2:before {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 0;
            width: 40px;
            height: 3px;
            background: var(--accent);
            border-radius: 2px;
        }

        .accordion {
            margin-bottom: 20px;
            border-radius: 15px !important;
            overflow: hidden;
        }

        .card {
            border: none;
            border-radius: 15px !important;
            margin-bottom: 20px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
            transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
            overflow: hidden;
            background-color: white;
        }

        .card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 30px rgba(67, 97, 238, 0.15);
        }

        .card-header {
            cursor: pointer;
            background-color: white;
            border-bottom: none;
            padding: 20px 25px;
            transition: all 0.3s ease;
            position: relative;
            display: flex;
            align-items: center;
        }

        .card-header:hover {
            background-color: #f8f9fa;
        }

        .card-header:after {
            font-family: "Font Awesome 6 Free";
            content: "\f078";
            font-weight: 900;
            position: absolute;
            right: 25px;
            font-size: 1rem;
            color: var(--primary);
            transition: all 0.4s cubic-bezier(0.68, -0.55, 0.27, 1.55);
        }

        .card-header.collapsed:after {
            transform: rotate(-90deg);
            color: var(--dark);
        }

        .card-header h5 {
            margin: 0;
            display: flex;
            align-items: center;
            font-weight: 600;
        }

        .card-header h5 i {
            margin-right: 15px;
            font-size: 1.2rem;
            color: var(--primary);
            transition: all 0.3s ease;
        }

        .card-header:hover h5 i {
            transform: scale(1.1);
            color: var(--accent);
        }

        .order-item {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            padding: 20px;
            background-color: white;
            border-radius: 12px;
            transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
            box-shadow: 0 3px 15px rgba(0, 0, 0, 0.03);
            border-left: 4px solid var(--accent);
            position: relative;
            overflow: hidden;
        }

        .order-item:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: var(--gradient);
            transition: all 0.4s ease;
        }

        .order-item:hover {
            transform: translateX(10px);
            box-shadow: 0 8px 25px rgba(67, 97, 238, 0.1);
        }

        .order-item:hover:before {
            width: 8px;
        }

        .order-item img {
            width: 70px;
            height: 70px;
            margin-right: 20px;
            object-fit: cover;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
            border: 2px solid white;
        }

        .order-item:hover img {
            transform: scale(1.08) rotate(2deg);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
        }

        .order-item div p {
            margin: 5px 0;
            font-size: 0.95rem;
        }

        .order-item div p strong {
            font-weight: 600;
            color: var(--secondary);
        }

        .filter-button {
            margin-top: 0;
            background: var(--gradient);
            border: none;
            padding: 12px 30px;
            border-radius: 12px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
            box-shadow: 0 5px 20px rgba(67, 97, 238, 0.3);
            text-transform: uppercase;
            font-size: 0.85rem;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .filter-button:after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 5px;
            height: 5px;
            background: rgba(255, 255, 255, 0.5);
            opacity: 0;
            border-radius: 100%;
            transform: scale(1, 1) translate(-50%);
            transform-origin: 50% 50%;
        }

        .filter-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(67, 97, 238, 0.4);
        }

        .filter-button:focus, .filter-button:active {
            outline: none;
        }

        .filter-button:active:after {
            animation: ripple 1s ease-out;
        }

        @keyframes ripple {
            0% {
                transform: scale(0, 0);
                opacity: 1;
            }
            100% {
                transform: scale(20, 20);
                opacity: 0;
            }
        }

        .form-control, .form-control:focus {
            border: 1px solid #e0e0e0;
            border-radius: 12px;
            padding: 12px 20px;
            box-shadow: none;
            transition: all 0.3s ease;
            font-size: 0.95rem;
        }

        .form-control:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 0.2rem rgba(76, 201, 240, 0.25);
        }

        label {
            font-weight: 500;
            margin-bottom: 8px;
            color: var(--dark);
            font-size: 0.95rem;
        }

        .pagination {
            margin-top: 40px;
            display: flex;
            justify-content: center;
        }

        .pagination a {
            margin: 0 8px;
            border-radius: 12px !important;
            transition: all 0.3s ease;
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            border: 2px solid transparent;
        }

        .pagination a.active, .pagination a:hover {
            background: var(--gradient);
            color: white !important;
            border-color: transparent;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3);
        }

        .card-body {
            padding: 25px;
            background-color: #f9fafb;
        }

        /* Pulse animation for new items */
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.02); }
            100% { transform: scale(1); }
        }

        .pulse {
            animation: pulse 1.5s infinite;
        }

        /* Floating animation */
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }

        .floating {
            animation: float 6s ease-in-out infinite;
        }

        /* Status badges */
        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-left: 10px;
        }

        .status-active {
            background-color: rgba(56, 176, 0, 0.1);
            color: var(--success);
        }

        /* Tooltip styles */
        [data-tooltip] {
            position: relative;
            cursor: pointer;
        }

        [data-tooltip]:before {
            content: attr(data-tooltip);
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            background-color: var(--dark);
            color: white;
            padding: 5px 10px;
            border-radius: 6px;
            font-size: 0.8rem;
            white-space: nowrap;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            margin-bottom: 10px;
        }

        [data-tooltip]:hover:before {
            opacity: 1;
            visibility: visible;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><i class="fas fa-file-invoice-dollar floating" style="margin-right: 15px;"></i>Checks Dashboard</h1>

        <form method="GET">
            <div class="form-row align-items-center">
                <div class="col-md-3">
                    <label for="date_from"><i class="fas fa-calendar-alt" style="margin-right: 8px;"></i>Date from:</label>
                    <input type="date" id="date_from" name="date_from" class="form-control" value="<?php echo isset($_GET['date_from']) ? $_GET['date_from'] : ''; ?>">
                </div>
                <div class="col-md-3">
                    <label for="date_to"><i class="fas fa-calendar-check" style="margin-right: 8px;"></i>Date to:</label>
                    <input type="date" id="date_to" name="date_to" class="form-control" value="<?php echo isset($_GET['date_to']) ? $_GET['date_to'] : ''; ?>">
                </div>
                <div class="col-md-3">
                    <label for="user"><i class="fas fa-user" style="margin-right: 8px;"></i>User:</label>
                    <select id="user" name="user" class="form-control">
                        <option value="">All Users</option>
                        <?php
                        include('../connetionDB/config.php');

                        $sql = "SELECT id, name FROM users";
                        $result = mysqli_query($conn, $sql);

                        if (mysqli_num_rows($result) > 0) {
                            while($row = mysqli_fetch_assoc($result)) {
                                $selected = (isset($_GET['user']) && $_GET['user'] == $row['id']) ? 'selected' : '';
                                echo "<option value='" . $row['id'] . "' " . $selected . ">" . $row['name'] . "</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary filter-button">
                        <i class="fas fa-filter" style="margin-right: 8px;"></i>Filter
                    </button>
                </div>
            </div>
        </form>

        <h2><i class="fas fa-users" style="margin-right: 12px;"></i>Users</h2>
        <div class="accordion" id="userAccordion">
            <?php
            include('../connetionDB/config.php');

            $date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
            $date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';
            $user_id = isset($_GET['user']) ? $_GET['user'] : '';

            $page = isset($_GET['page']) ? $_GET['page'] : 1;
            $results_per_page = 10;
            $start_from = ($page-1) * $results_per_page;

            $sql = "SELECT u.id, u.name FROM users u LEFT JOIN orders o ON u.id = o.user_id ";
            $where_clause = "";

            if (!empty($date_from) && !empty($date_to)) {
                $where_clause .= "WHERE o.created_at BETWEEN '$date_from' AND '$date_to' ";
            }

            if (!empty($user_id)) {
                $where_clause .= (!empty($where_clause) ? "AND" : "WHERE") . " u.id = '$user_id' ";
            }

            $sql .= $where_clause . "LIMIT $start_from, $results_per_page";

            $result = mysqli_query($conn, $sql);

            if (mysqli_num_rows($result) > 0) {
                $i = 1;
                while($row = mysqli_fetch_assoc($result)) {
                    echo "<div class='card pulse'>";
                    echo "  <div class='card-header' id='heading" . $i . "' data-toggle='collapse' data-target='#collapse" . $i . "' aria-expanded='false' aria-controls='collapse" . $i . "'>";
                    echo "      <h5 class='mb-0'><i class='fas fa-user-circle'></i>" . $row['name'] . "</h5>";
                    echo "  </div>";

                    echo "  <div id='collapse" . $i . "' class='collapse' aria-labelledby='heading" . $i . "' data-parent='#userAccordion'>";
                    echo "      <div class='card-body'>";
                    echo "          <h3><i class='fas fa-receipt' style='margin-right: 10px;'></i>Orders</h3>";

                    // Fetch orders for the current user
                    $user_id = $row['id'];
                    $sql_orders = "SELECT id, created_at, total_price FROM orders WHERE user_id = '$user_id'";
                    if (!empty($date_from) && !empty($date_to)) {
                        $sql_orders .= " AND created_at BETWEEN '$date_from' AND '$date_to'";
                    }
                    $result_orders = mysqli_query($conn, $sql_orders);

                    if (mysqli_num_rows($result_orders) > 0) {
                        $j = 1;
                        echo "<div class='accordion' id='orderAccordion" . $i . "'>";
                        while($order_row = mysqli_fetch_assoc($result_orders)) {
                            echo "<div class='card'>";
                            echo "  <div class='card-header' id='orderHeading" . $i . $j . "' data-toggle='collapse' data-target='#orderCollapse" . $i . $j . "' aria-expanded='false' aria-controls='orderCollapse" . $i . $j . "'>";
                            echo "      <h5 class='mb-0'><i class='fas fa-shopping-bag'></i>Order Date: " . $order_row['created_at'] . " - Total: $" . $order_row['total_price'] . " <span class='status-badge status-active' data-tooltip='Active Order'><i class='fas fa-check-circle'></i> Verified</span></h5>";
                            echo "  </div>";
                            echo "  <div id='orderCollapse" . $i . $j . "' class='collapse' aria-labelledby='orderHeading" . $i . $j . "' data-parent='#orderAccordion" . $i . "'>";
                            echo "      <div class='card-body'>";
                            echo "          <h4><i class='fas fa-box-open' style='margin-right: 10px;'></i>Order Items</h4>";

                            // Fetch order items for the current order
                            $order_id = $order_row['id'];
                            $sql_order_items = "SELECT oi.quantity, p.name, p.price, p.image FROM order_items oi LEFT JOIN products p ON oi.product_id = p.id WHERE oi.order_id = '$order_id'";
                            $result_order_items = mysqli_query($conn, $sql_order_items);

                            if (mysqli_num_rows($result_order_items) > 0) {
                                while($order_item_row = mysqli_fetch_assoc($result_order_items)) {
                                    echo "<div class='order-item'>";
                                    echo "  <img src='../uploads/" . $order_item_row['image'] . "' alt='" . $order_item_row['name'] . "'>";
                                    echo "  <div>";
                                    echo "      <p><strong>" . $order_item_row['name'] . "</strong></p>";
                                    echo "      <p><i class='fas fa-tag' style='margin-right: 6px; color: var(--accent);'></i>Price: $" . $order_item_row['price'] . "</p>";
                                    echo "      <p><i class='fas fa-cubes' style='margin-right: 6px; color: var(--accent);'></i>Quantity: " . $order_item_row['quantity'] . "</p>";
                                    echo "  </div>";
                                    echo "</div>";
                                }
                            } else {
                                echo "<div class='alert alert-light' role='alert'>";
                                echo "<i class='fas fa-info-circle' style='margin-right: 8px;'></i>No items found in this order.";
                                echo "</div>";
                            }

                            echo "      </div>";
                            echo "  </div>";
                            echo "</div>";
                            $j++;
                        }
                        echo "</div>";
                    } else {
                        echo "<div class='alert alert-light' role='alert'>";
                        echo "<i class='fas fa-info-circle' style='margin-right: 8px;'></i>No orders found for this user.";
                        echo "</div>";
                    }

                    echo "      </div>";
                    echo "  </div>";
                    echo "</div>";
                    $i++;
                }
            } else {
                echo "<div class='alert alert-light' role='alert'>";
                echo "<i class='fas fa-info-circle' style='margin-right: 8px;'></i>No users found.";
                echo "</div>";
            }
            ?>
        </div>

        <div class="pagination">
            <?php
            $sql = "SELECT COUNT(*) AS total FROM users";

            if (!empty($date_from) && !empty($date_to)) {
                $sql = "SELECT COUNT(DISTINCT u.id) AS total FROM users u LEFT JOIN orders o ON u.id = o.user_id WHERE o.created_at BETWEEN '$date_from' AND '$date_to'";
            }

            if (!empty($user_id)) {
                $sql = "SELECT COUNT(DISTINCT u.id) AS total FROM users u LEFT JOIN orders o ON u.id = o.user_id WHERE u.id = '$user_id' AND o.created_at BETWEEN '$date_from' AND '$date_to'";
            }

            $result = mysqli_query($conn, $sql);
            $row = mysqli_fetch_assoc($result);
            $total_pages = ceil($row["total"] / $results_per_page);

            for ($i=1; $i<=$total_pages; $i++) {
                $active = ($page == $i) ? 'active' : '';
                echo "<a class='btn btn-light " . $active . "' href='checks.php?page=" . $i . "&date_from=" . $date_from . "&date_to=" . $date_to . "&user=" . $user_id . "'>" . $i . "</a>";
            }
            ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function(){
            // Add animation to accordion toggle
            $('.card-header').click(function() {
                $(this).toggleClass('collapsed');
            });
            
            // Add staggered animation to order items
            $('.order-item').each(function(i) {
                $(this).css({
                    'opacity': '0',
                    'transform': 'translateX(-20px)'
                }).delay(i * 100).animate({
                    'opacity': '1',
                    'transform': 'translateX(0)'
                }, 300);
            });
            
            // Add ripple effect to buttons
            $('.filter-button').click(function(e) {
                // Remove any old one
                $(".ripple").remove();
                
                // Setup
                var posX = $(this).offset().left,
                    posY = $(this).offset().top,
                    buttonWidth = $(this).width(),
                    buttonHeight = $(this).height();
                
                // Add the element
                $(this).prepend("<span class='ripple'></span>");
                
                // Make it round!
                if(buttonWidth >= buttonHeight) {
                    buttonHeight = buttonWidth;
                } else {
                    buttonWidth = buttonHeight; 
                }
                
                // Get the center of the element
                var x = e.pageX - posX - buttonWidth / 2;
                var y = e.pageY - posY - buttonHeight / 2;
                
                // Add the ripples CSS and start the animation
                $(".ripple").css({
                    width: buttonWidth,
                    height: buttonHeight,
                    top: y + 'px',
                    left: x + 'px'
                }).addClass("rippleEffect");
            });
            
            // Stop pulse animation after 3 seconds
            setTimeout(function() {
                $('.pulse').removeClass('pulse');
            }, 3000);
        });
    </script>
</body>
</html>