

<?php


session_start();
require_once '../connetionDB/config.php';

// Initialize variables and error messages
$errors = [];
$nameErr = $emailErr = $passwordErr = $confirmPasswordErr = '';
$successMessage = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize inputs
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate Name
    if (empty($name)) {
        $nameErr = "Name is required.";
    } elseif (!preg_match("/^[a-zA-Z ]+$/", $name)) {
        $nameErr = "Name must contain only letters and spaces.";
    }

    // Validate Email
    if (empty($email)) {
        $emailErr = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $emailErr = "Invalid email format.";
    } else {
        // Check if email already exists
        $check_email = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check_email->bind_param("s", $email);
        $check_email->execute();
        $check_email->store_result();
        if ($check_email->num_rows > 0) {
            $emailErr = "This email is already registered.";
        }
        $check_email->close();
    }

    // Validate Password
    if (empty($password)) {
        $passwordErr = "Password is required.";
    } elseif (strlen($password) < 8) {
        $passwordErr = "Password must be at least 8 characters long.";
    } elseif (!preg_match("/[a-z]/i", $password)) {
        $passwordErr = "Password must contain at least one letter.";
    } elseif (!preg_match("/[0-9]/", $password)) {
        $passwordErr = "Password must contain at least one number.";
    }

    // Validate Confirm Password
    if (empty($confirm_password)) {
        $confirmPasswordErr = "Confirm password is required.";
    } elseif ($password !== $confirm_password) {
        $confirmPasswordErr = "Passwords do not match.";
    }

    // If no errors, proceed with adding the user
    if (empty($nameErr) && empty($emailErr) && empty($passwordErr) && empty($confirmPasswordErr)) {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Handle image upload
        $target_dir = "../user/useruploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $target_file = $target_dir . basename($_FILES["profile_picture"]["name"]);
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if file is an actual image
        $check = getimagesize($_FILES["profile_picture"]["tmp_name"]);
        if ($check === false) {
            $uploadOk = 0;
        }

        // Check file size (500KB max)
        if ($_FILES["profile_picture"]["size"] > 500000) {
            $uploadOk = 0;
        }

        // Allow only specific file formats
        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
            $uploadOk = 0;
        }

        if ($uploadOk) {
            if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
                // Insert user data into the database
                $sql = "INSERT INTO users (name, email, password, image) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssss", $name, $email, $hashed_password, $target_file);

                if ($stmt->execute()) {
                    $successMessage = "User added successfully!";
                } else {
                    $errors[] = "Error adding user.";
                }

               
            } else {
                $errors[] = "Error uploading profile picture.";
            }
        } else {
            $errors[] = "Invalid or unsupported profile picture.";
        }
    }

}
?>















<!DOCTYPE html>
<html>
<head>
    <title>Add User</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>

.error-message {
            color: red;
            font-size: 0.9em;
            margin-top: 5px;
        }
        /* Custom styles for success alert */
        .alert-success {
            position: fixed;
            top: 10px;
            right: 10px;
            z-index: 9999;
        }
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --accent: #4cc9f0;
            --light: #f8f9fa;
            --dark: #212529;
            --gradient: linear-gradient(135deg, #4361ee 0%, #4cc9f0 100%);
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(-45deg, #ee7752, #e73c7e, #23a6d5, #23d5ab);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: var(--dark);
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .container {
            max-width: 600px;
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            animation: fadeInUp 0.8s cubic-bezier(0.22, 1, 0.36, 1);
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        h2 {
            color: var(--secondary);
            font-weight: 600;
            margin-bottom: 25px;
            position: relative;
            padding-bottom: 10px;
            text-align: center;
        }

        h2:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: var(--gradient);
            border-radius: 2px;
            animation: expandLine 1s ease-out forwards;
        }

        @keyframes expandLine {
            from { width: 0; opacity: 0; }
            to { width: 80px; opacity: 1; }
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .form-group label {
            font-weight: 500;
            margin-bottom: 8px;
            display: block;
            color: var(--dark);
            transition: all 0.3s ease;
        }

        .form-control {
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            padding: 12px 15px;
            transition: all 0.3s ease;
            box-shadow: none;
            background-color: rgba(255, 255, 255, 0.8);
        }

        .form-control:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 0.2rem rgba(76, 201, 240, 0.25);
            transform: translateY(-2px);
        }

        .btn {
            border-radius: 10px;
            padding: 10px 20px;
            font-weight: 500;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            border: none;
            margin-right: 10px;
        }

        .btn-primary {
            background: var(--gradient);
            box-shadow: 0 4px 15px rgba(67, 97, 238, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(67, 97, 238, 0.4);
        }

        .btn-secondary {
            background: #6c757d;
            box-shadow: 0 4px 15px rgba(108, 117, 125, 0.3);
        }

        .btn-secondary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(108, 117, 125, 0.4);
            background: #5a6268;
        }

        .alert {
            border-radius: 10px;
            animation: fadeIn 0.5s ease-out;
            margin-top: 20px;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Floating animation for form elements */
        .form-group:hover label {
            color: var(--primary);
            transform: translateX(5px);
        }

        /* File input custom styling */
        .custom-file-input {
            position: relative;
            overflow: hidden;
            display: inline-block;
        }

        .custom-file-input input[type="file"] {
            position: absolute;
            font-size: 100px;
            opacity: 0;
            right: 0;
            top: 0;
        }

        .custom-file-label {
            border: 1px solid #ced4da;
            border-radius: 10px;
            padding: 10px 15px;
            background-color: #f8f9fa;
            display: block;
            transition: all 0.3s ease;
        }

        .custom-file-input:hover .custom-file-label {
            background-color: #e9ecef;
            border-color: var(--accent);
        }

        /* Password strength indicator */
        .password-strength {
            height: 4px;
            background: #e9ecef;
            border-radius: 2px;
            margin-top: 5px;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .password-strength-bar {
            height: 100%;
            width: 0;
            background: var(--gradient);
            transition: all 0.3s ease;
        }
    </style>
</head>
<body>

    <div class="container">
        <h2><i class="fas fa-user-plus" style="margin-right: 10px;"></i>Add User</h2>

        <?php if (!empty($successMessage)): ?>
            <div id="success-alert" class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($successMessage); ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>
        
      
        
        <form method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label><i class="fas fa-user" style="margin-right: 8px;"></i>Name:</label>
                <input type="text" class="form-control" name="name" required  value="<?php echo htmlspecialchars($name ?? ''); ?>" >
                <div class="error-message"><?php echo $nameErr; ?></div>
            </div>
            <div class="form-group">
                <label><i class="fas fa-envelope" style="margin-right: 8px;"></i>Email:</label>
                <input type="email" class="form-control" name="email" required value="<?php echo htmlspecialchars($email ?? ''); ?>">
                <div class="error-message"><?php echo $emailErr; ?></div>

            </div>
            <div class="form-group">
                <label><i class="fas fa-lock" style="margin-right: 8px;"></i>Password:</label>
                <input type="password" class="form-control" name="password" required id="password">
                <div class="error-message"><?php echo $passwordErr; ?></div>
                <div class="password-strength">
                    <div class="password-strength-bar" id="password-strength-bar"></div>
                </div>
            </div>
            <div class="form-group">
                <label><i class="fas fa-lock" style="margin-right: 8px;"></i>Confirm Password:</label>
                <input type="password" class="form-control" name="confirm_password" required>
                <div class="error-message"><?php echo $confirmPasswordErr; ?></div>
            </div>
            <div class="form-group">
                <label><i class="fas fa-image" style="margin-right: 8px;"></i>Profile Picture:</label>
                <div class="custom-file-input">
                    <input type="file" class="form-control" name="profile_picture" id="profile_picture">
                    <label class="custom-file-label" for="profile_picture">Choose file...</label>
                </div>
            </div>
            <div class="text-center">
                <button type="submit" class="btn btn-primary" name="submit">Save <i class="fas fa-save"></i></button>
                <button type="reset" class="btn btn-secondary">Reset <i class="fas fa-undo"></i></button>
            </div>
        </form>
    </div>

<?php
if (isset($_POST['submit'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Include database connection
    require_once '../connetionDB/config.php';

    // First check if email already exists
    $check_email = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check_email->bind_param("s", $email);
    $check_email->execute();
    $check_email->store_result();
    
    if ($check_email->num_rows > 0) {
        echo "<script>
                $(document).ready(function() {
                    $('.container').prepend(
                        '<div class=\"alert alert-danger alert-dismissible fade show\" role=\"alert\">' +
                        '<i class=\"fas fa-exclamation-circle\"></i> This email already exists!' +
                        '<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">' +
                        '<span aria-hidden=\"true\">&times;</span>' +
                        '</button>' +
                        '</div>'
                    );
                });
              </script>";
        $check_email->close();
        $conn->close();
        exit();
    }
    $check_email->close();

    // Validate form data
    if (strlen($password) < 8) {
        echo "<script>
                $(document).ready(function() {
                    $('.container').prepend(
                        '<div class=\"alert alert-danger alert-dismissible fade show\" role=\"alert\">' +
                        '<i class=\"fas fa-exclamation-circle\"></i> Password must be at least 8 characters long.' +
                        '<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">' +
                        '<span aria-hidden=\"true\">&times;</span>' +
                        '</button>' +
                        '</div>'
                    );
                });
              </script>";
    } elseif (!preg_match("/[a-z]/i", $password)) {
        echo "<script>
                $(document).ready(function() {
                    $('.container').prepend(
                        '<div class=\"alert alert-danger alert-dismissible fade show\" role=\"alert\">' +
                        '<i class=\"fas fa-exclamation-circle\"></i> Password must contain at least one letter.' +
                        '<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">' +
                        '<span aria-hidden=\"true\">&times;</span>' +
                        '</button>' +
                        '</div>'
                    );
                });
              </script>";
    } elseif (!preg_match("/[0-9]/", $password)) {
        echo "<script>
                $(document).ready(function() {
                    $('.container').prepend(
                        '<div class=\"alert alert-danger alert-dismissible fade show\" role=\"alert\">' +
                        '<i class=\"fas fa-exclamation-circle\"></i> Password must contain at least one number.' +
                        '<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">' +
                        '<span aria-hidden=\"true\">&times;</span>' +
                        '</button>' +
                        '</div>'
                    );
                });
              </script>";
    } elseif ($password != $confirm_password) {
        echo "<script>
                $(document).ready(function() {
                    $('.container').prepend(
                        '<div class=\"alert alert-danger alert-dismissible fade show\" role=\"alert\">' +
                        '<i class=\"fas fa-exclamation-circle\"></i> Passwords do not match.' +
                        '<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">' +
                        '<span aria-hidden=\"true\">&times;</span>' +
                        '</button>' +
                        '</div>'
                    );
                });
              </script>";
    } else {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Handle image upload
        $target_dir = "userphoto/";
        $target_file = $target_dir . basename($_FILES["profile_picture"]["name"]);
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if image file is a actual image or fake image
        $check = getimagesize($_FILES["profile_picture"]["tmp_name"]);
        if($check === false) {
            $uploadOk = 0;
        }

        // Check if file already exists
        if (file_exists($target_file)) {
            $uploadOk = 0;
        }

        // Check file size
        if ($_FILES["profile_picture"]["size"] > 500000) {
            $uploadOk = 0;
        }

        // Allow certain file formats
        if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
        && $imageFileType != "gif" ) {
            $uploadOk = 0;
        }

        // Check if $uploadOk is set to 0 by an error
        if ($uploadOk == 0) {
            // No message shown for upload errors
        } else {
            // Check if the target directory exists
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
                // Insert user data into the database
                $sql = "INSERT INTO users (name, email, password, image) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssss", $name, $email, $hashed_password, $target_file);

                if ($stmt->execute()) {
                    echo "<script>
                            $(document).ready(function() {
                                $('.container').prepend(
                                    '<div class=\"alert alert-success alert-dismissible fade show\" role=\"alert\">' +
                                    '<i class=\"fas fa-check-circle\"></i> User added successfully!' +
                                    '<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">' +
                                    '<span aria-hidden=\"true\">&times;</span>' +
                                    '</button>' +
                                    '</div>'
                                );
                            });
                          </script>";
                } else {
                    echo "<script>
                            $(document).ready(function() {
                                $('.container').prepend(
                                    '<div class=\"alert alert-danger alert-dismissible fade show\" role=\"alert\">' +
                                    '<i class=\"fas fa-exclamation-circle\"></i> Error adding user.' +
                                    '<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">' +
                                    '<span aria-hidden=\"true\">&times;</span>' +
                                    '</button>' +
                                    '</div>'
                                );
                            });
                          </script>";
                }

                $stmt->close();
            }
        }
    }
    $conn->close();
}
?>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>



// Automatically hide success alert after 1 second
$(document).ready(function () {
            if ($('#success-alert').length) {
                setTimeout(function () {
                    $('#success-alert').fadeOut('slow', function () {
                        $(this).remove();
                    });
                }, 1000);
            }});







    $(document).ready(function() {
        // File input label update
        $('#profile_picture').change(function() {
            var fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').text(fileName || 'Choose file...');
        });

        // Password strength indicator
        $('#password').keyup(function() {
            var password = $(this).val();
            var strength = 0;
            
            if (password.length >= 8) strength += 25;
            if (password.match(/[a-z]/)) strength += 25;
            if (password.match(/[A-Z]/)) strength += 25;
            if (password.match(/[0-9]/)) strength += 25;
            
            $('#password-strength-bar').css('width', strength + '%');
            
            if (strength < 50) {
                $('#password-strength-bar').css('background', '#ff5252');
            } else if (strength < 75) {
                $('#password-strength-bar').css('background', '#ffb142');
            } else {
                $('#password-strength-bar').css('background', '#2ecc71');
            }
        });

        // Add ripple effect to buttons
        $('.btn').click(function(e) {
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
    });
</script>
</body>
</html>