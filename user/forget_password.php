<?php
session_start();
require_once '../connetionDB/config.php';

$step = 1;
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['email'])) {
        // Step 1: Check if email exists
        $email = trim($_POST['email']);
        
        $query = "SELECT id FROM users WHERE email = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);

        if ($user) {
            $_SESSION['reset_email'] = $email;
            $step = 2;
        } else {
            $error = "Email not found.";
        }
    } elseif (isset($_POST['new_password'])) {
        // Step 2: Update password
        if (!isset($_SESSION['reset_email'])) {
            $error = "Session expired. Please try again.";
        } else {
            $new_password = $_POST['new_password'];
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            $query = "UPDATE users SET password = ? WHERE email = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, 'ss', $hashed_password, $_SESSION['reset_email']);
            if (mysqli_stmt_execute($stmt)) {
                $success = "Password updated successfully!";
                unset($_SESSION['reset_email']);
                $step = 1;
            } else {
                $error = "Something went wrong. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Cafeteria</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(to right, #6c5ce7, #a29bfe);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .reset-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
            padding: 40px;
            width: 100%;
            max-width: 400px;
        }
        .reset-title {
            font-weight: bold;
            margin-bottom: 20px;
            text-align: center;
            color: #6c5ce7;
        }
        .btn-reset {
            background-color: #6c5ce7;
            border: none;
        }
        .btn-reset:hover {
            background-color: #5a4dcf;
        }
        .form-control:focus {
            border-color: #6c5ce7;
            box-shadow: 0 0 0 0.2rem rgba(108, 92, 231, 0.25);
        }
    </style>
</head>
<body>
    <div class="reset-card">
        <h2 class="reset-title">Reset Password</h2>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php if ($step == 1): ?>
            <!-- Step 1: Enter Email -->
            <form method="POST" class="needs-validation" novalidate>
                <div class="mb-3">
                    <label for="email" class="form-label">Enter your Email</label>
                    <input 
                        type="email" 
                        class="form-control" 
                        id="email" 
                        name="email" 
                        required
                    >
                    <div class="invalid-feedback">
                        Please enter a valid email address.
                    </div>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-reset">Next</button>
                </div>
            </form>

        <?php elseif ($step == 2): ?>
            <!-- Step 2: Enter New Password -->
            <form method="POST" class="needs-validation" novalidate>
                <div class="mb-3">
                    <label for="new_password" class="form-label">Enter New Password</label>
                    <input 
                        type="password" 
                        class="form-control" 
                        id="new_password" 
                        name="new_password" 
                        required 
                        minlength="6"
                    >
                    <div class="invalid-feedback">
                        Please enter a password (at least 6 characters).
                    </div>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-reset">Reset Password</button>
                </div>
            </form>
        <?php endif; ?>

        <div class="mt-3 text-center">
            <a href="login.php" class="text-decoration-none">Back to Login</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Bootstrap validation
        (() => {
          'use strict'
          const forms = document.querySelectorAll('.needs-validation')
          Array.from(forms).forEach(form => {
            form.addEventListener('submit', event => {
              if (!form.checkValidity()) {
                event.preventDefault()
                event.stopPropagation()
              }
              form.classList.add('was-validated')
            }, false)
          })
        })()
    </script>
</body>
</html>
