<?php
include '../connetionDB/config.php'; 

$name        = 'soli';
$email       = 's1234@gmail.com';
$password    = password_hash('s12345', PASSWORD_DEFAULT);
$room_number = '2002';
$image       = 'salma.jpg';
$role        = 'admin';

$sql = "INSERT INTO users (name, email, password, room_number, image, role)
        VALUES (?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssss", $name, $email, $password, $room_number, $image, $role);

if ($stmt->execute()) {
     echo "added succsfully";
} else {
    echo "error " . $stmt->error;
}
?>
