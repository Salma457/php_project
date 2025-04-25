<?php


require_once '../connetionDB/config.php'; 


if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

$userId = 1; 


$cancelMessage = '';

if (isset($_POST['action']) && $_POST['action'] === 'cancel' && isset($_POST['order_id'])) {
    $orderIdToCancel = filter_var($_POST['order_id'], FILTER_VALIDATE_INT);

    if ($orderIdToCancel) {
       
        $verifySql = "SELECT status FROM orders WHERE id = ? AND user_id = ?";
        $verifyStmt = mysqli_prepare($conn, $verifySql);

        if ($verifyStmt) {
            mysqli_stmt_bind_param($verifyStmt, "ii", $orderIdToCancel, $userId);
            mysqli_stmt_execute($verifyStmt);
            $verifyResult = mysqli_stmt_get_result($verifyStmt);
            $orderToCancel = mysqli_fetch_assoc($verifyResult);
            mysqli_stmt_close($verifyStmt);

            if ($orderToCancel && $orderToCancel['status'] === 'processing') {
              
                mysqli_begin_transaction($conn);

                try {
                  
                    $cancelSql = "DELETE FROM orders WHERE id = ?";
                    $cancelStmt = mysqli_prepare($conn, $cancelSql);
                    if (!$cancelStmt) {
                        throw new Exception("Error preparing cancel statement: " . mysqli_error($conn));
                    }
                    mysqli_stmt_bind_param($cancelStmt, "i", $orderIdToCancel);
                    if (!mysqli_stmt_execute($cancelStmt)) {
                        throw new Exception("Error deleting order: " . mysqli_error($conn));
                    }
                    mysqli_stmt_close($cancelStmt);

                    
                    mysqli_commit($conn);

                   
                    $redirectUrl = "/php_project/user/my_orders.php?cancel_status=success&cancelled_order_id=" . urlencode($orderIdToCancel);
                    if (isset($_POST['date_from']) && $_POST['date_from']) {
                        $redirectUrl .= "&date_from=" . urlencode($_POST['date_from']);
                    }
                    if (isset($_POST['date_to']) && $_POST['date_to']) {
                        $redirectUrl .= "&date_to=" . urlencode($_POST['date_to']);
                    }
                    header("Location: " . $redirectUrl);
                    exit();
                } catch (Exception $e) {
                    mysqli_rollback($conn);
                    $cancelMessage = "<div class='alert alert-danger mt-3'>Error cancelling order #{$orderIdToCancel}: " . htmlspecialchars($e->getMessage()) . "</div>";
                    error_log("Error cancelling order ID: {$orderIdToCancel}, User: {$userId}, Error: " . $e->getMessage());
                }
            } else {
                $cancelMessage = "<div class='alert alert-danger mt-3'>Order #{$orderIdToCancel} cannot be cancelled or does not exist.</div>";
            }
        } else {
            $cancelMessage = "<div class='alert alert-danger mt-3'>Error verifying order: " . mysqli_error($conn) . "</div>";
            error_log("Error preparing verification statement User: {$userId} Error: " . mysqli_error($conn));
        }
    } else {
        $cancelMessage = "<div class='alert alert-danger mt-3'>Invalid order ID provided.</div>";
    }
}


if (isset($_GET['cancel_status'])) {
    if ($_GET['cancel_status'] === 'success') {
        $cancelledId = isset($_GET['cancelled_order_id']) ? htmlspecialchars($_GET['cancelled_order_id']) : 'the order';
        $cancelMessage = "<div class='alert alert-success mt-3'>Order #{$cancelledId} cancelled successfully!</div>";
    } elseif ($_GET['cancel_status'] === 'error') {
        $cancelMessage = "<div class='alert alert-danger mt-3'>Cancellation failed. Please try again.</div>";
    }
}


mysqli_close($conn);
?>

