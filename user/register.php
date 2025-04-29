<?php
// Database connection
require_once '../connetionDB/config.php'; 

// Initialize variables and error messages
$errors = [];
$name = $email = $password = $room_number = $role = $image = '';
$nameErr = $emailErr = $passwordErr = $room_numberErr = $roleErr = $imageErr = '';
$dbError = ''; // For database-related errors

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate name
    if (empty($_POST['name'])) {
        $nameErr = "Name is required.";
        $errors[] = true;
    } else {
        $name = mysqli_real_escape_string($conn, $_POST['name']);
    }

    // Validate email
    if (empty($_POST['email'])) {
        $emailErr = "Email is required.";
        $errors[] = true;
    } elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $emailErr = "Invalid email format.";
        $errors[] = true;
    } else {
        $email = mysqli_real_escape_string($conn, $_POST['email']);
    }

    // Validate password
    if (empty($_POST['password'])) {
        $passwordErr = "Password is required.";
        $errors[] = true;
    } elseif (strlen($_POST['password']) < 8) {
        $passwordErr = "Password must be at least 8 characters long.";
        $errors[] = true;
    } else {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    }

    // Validate room number
    if (empty($_POST['room_number'])) {
        $room_numberErr = "Room number is required.";
        $errors[] = true;
    } else {
        $room_number = mysqli_real_escape_string($conn, $_POST['room_number']);
    }

    // Validate role
    if (empty($_POST['role'])) {
        $roleErr = "Role is required.";
        $errors[] = true;
    } else {
        $role = mysqli_real_escape_string($conn, $_POST['role']);
    }

    // Handle image upload
    $uploadDir = 'useruploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true); // Create directory if it doesn't exist
    }

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $image = $uploadDir . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $image);
    } else {
        $image = null; // No image uploaded
    }

    // If no validation errors, attempt to insert data into the database
    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, room_number, image, role) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $name, $email, $password, $room_number, $image, $role);

            if ($stmt->execute()) {
                echo "<div class='alert alert-success text-center'>Registration successful! </a></div>";
                // Redirect to login page after successful registration
                header("Location: login.php");
            } else {
                throw new Exception($stmt->error); // Throw exception for database errors
            }

            $stmt->close();
        } catch (Exception $e) {
            // Handle database errors (e.g., duplicate email)
            $dbError = $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(to right, #6c5ce7, #a29bfe);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .container {
            max-width: 600px;
            margin-top: 80px;
        }

        .card {
            border: none;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        h2 {
            font-weight: 600;
            color: #333;
            margin-bottom: 30px;
            text-align: center;
        }

        .form-label {
            font-weight: 500;
            color: #555;
        }

        .form-control {
            border-radius: 10px;
            padding: 15px;
            font-size: 16px;
        }

        .form-control:focus {
            box-shadow: 0 0 5px rgba(66, 133, 244, 0.5);
            border-color: #4285f4;
        }

        .btn {
            width: 100%;
            border-radius: 10px;
            padding: 12px;
            font-size: 18px;
            font-weight: 500;
        }

        .btn-primary {
            background-color: #4285f4;
            border: none;
        }

        .btn-primary:hover {
            background-color: #357ae8;
        }

        .mb-3 {
            margin-bottom: 20px;
        }

        .form-control-file {
            border-radius: 10px;
            padding: 12px;
        }

        .card-footer {
            text-align: center;
            margin-top: 20px;
        }

        .card-footer a {
            color: #4285f4;
            text-decoration: none;
        }

        .card-footer a:hover {
            text-decoration: underline;
        }

        .error-message {
            color: red;
            font-size: 0.9em;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="card">
        <?php if (!empty($dbError)): ?>
            <div class="alert alert-danger text-center">
                <?php
                // Customize the error message for duplicate email
                if (strpos($dbError, 'Duplicate entry') !== false) {
                    echo "This email is already registered. Please use a different email.";
                } else {
                    echo "An error occurred: " . htmlspecialchars($dbError);
                }
                ?>
            </div>
        <?php endif; ?>

        <h2>Registration</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="name" class="form-label">Full Name</label>
                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>">
                <div class="error-message"><?php echo $nameErr; ?></div>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>">
                <div class="error-message"><?php echo $emailErr; ?></div>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password">
                <div class="error-message"><?php echo $passwordErr; ?></div>
            </div>
            <div class="mb-3">
                <label for="room_number" class="form-label">Room Number</label>
                <input type="text" class="form-control" id="room_number" name="room_number" value="<?php echo htmlspecialchars($room_number); ?>">
                <div class="error-message"><?php echo $room_numberErr; ?></div>
            </div>
            <div class="mb-3">
                <label for="image" class="form-label">Profile Image</label>
                <input type="file" class="form-control-file" id="image" name="image">
                <div class="error-message"><?php echo $imageErr; ?></div>
            </div>
            <div class="mb-3">
                <label for="role" class="form-label">Role</label>
                <select class="form-control" id="role" name="role">
                    <option value="user" <?php echo $role === 'user' ? 'selected' : ''; ?>>User</option>
                </select>
                <div class="error-message"><?php echo $roleErr; ?></div>
            </div>
            <button type="submit" class="btn btn-primary">Register</button>
        </form>
        <div class="card-footer">
            <p>Already have an account? <a href="login.php">Login here</a></p>
        </div>
    </div>
</div>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>