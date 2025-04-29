<?php

include_once '../connetionDB/config.php'; 

$message = "";


if (isset($_GET['id'])) {
    $user_id = intval($_GET['id']); 

   
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
   
        $sql = "DELETE FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("i", $user_id);

            if ($stmt->execute()) {
                
                header("Location: list_all_users.php?message=deleted");
                exit();
            } else {
                $message = "Error deleting user: " . $stmt->error;
            }

            $stmt->close();
        } else {
            $message = "Error preparing statement: " . $conn->error;
        }
    }
} else {
    $message = "Invalid user ID.";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete User</title>
   
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <?php if (!empty($message)): ?>
            <div class="alert alert-danger text-center">
                <?php echo htmlspecialchars($message); ?>
            </div>
            <div class="text-center">
                <a href="list_all_users.php" class="btn btn-primary">Back to Users List</a>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['id'])): ?>
           
            <div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-labelledby="deleteConfirmationModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="deleteConfirmationModalLabel">Confirm Deletion</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            Are you sure you want to delete this user? This action cannot be undone.
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <form method="POST" action="">
                                <input type="hidden" name="confirm_delete" value="1">
                                <button type="submit" class="btn btn-danger">Delete</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

           
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmationModal'));
                    deleteModal.show();
                });
            </script>
        <?php endif; ?>
    </div>
</body>
</html>