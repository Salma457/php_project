<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "cafeteriaDB"; // اسم قاعدة البيانات

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";

// Check if ID is passed
if (isset($_GET['id'])) {
    $user_id = intval($_GET['id']); // تأمين id

    // حذف بيانات المستخدم
    $sql = "DELETE FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("i", $user_id);

        if ($stmt->execute()) {
            // بعد الحذف، ترجع على صفحة عرض كل المستخدمين
            header("Location: list_all_users.php?message=deleted");
            exit();
        } else {
            $message = "Error deleting user: " . $stmt->error;
        }

        $stmt->close();
    } else {
        $message = "Error preparing statement: " . $conn->error;
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
    </div>
</body>
</html>
