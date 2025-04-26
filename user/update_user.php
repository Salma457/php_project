<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "cafeteriaDB"; // تعديل اسم قاعدة البيانات

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";

// Check if ID is passed
if (isset($_GET['id'])) {
    $user_id = intval($_GET['id']);

    // Fetch current user data
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
        } else {
            $message = "<div class='alert alert-warning'>User not found.</div>";
        }

        $stmt->close();
    } else {
        $message = "<div class='alert alert-danger'>Error fetching user data: " . $conn->error . "</div>";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $room = $_POST['room_number'];
    $ext = $_POST['ext'];

    // Prepare UPDATE query
    $sql = "UPDATE users SET name = ?, email = ?, room_number = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("sssi", $name, $email, $room, $user_id);

        if ($stmt->execute()) {
            $message = "<div class='alert alert-success'>User updated successfully!</div>";
            // إعادة تحميل بيانات المستخدم بعد التحديث
            header("Refresh:1");
        } else {
            $message = "<div class='alert alert-danger'>Error updating user: " . $stmt->error . "</div>";
        }

        $stmt->close();
    } else {
        $message = "<div class='alert alert-danger'>Error preparing statement: " . $conn->error . "</div>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <?php echo $message; ?>
        <?php if (isset($user)): ?>
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-white">
                    <h4 class="mb-0">Update User</h4>
                </div>
                <div class="card-body">
                    <form action="" method="POST">
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="room_number" class="form-label">Room Number</label>
                            <input type="text" name="room_number" class="form-control" value="<?php echo htmlspecialchars($user['room_number'] ?? ''); ?>">
                        </div>

                        <div class="mb-3">
                            <label for="ext" class="form-label">Extension</label>
                            <input type="text" name="ext" class="form-control" value="<?php echo htmlspecialchars($user['ext'] ?? ''); ?>">
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success">Update User</button>
                            <a href="list_all_users.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
