<?php
include '../connetionDB/config.php'; 

$name        = 'Salma Hussein';
$email       = 's22@gmail.com';
$password    = password_hash('s12345', PASSWORD_DEFAULT);
$room_number = '2002';
$image       = 'salma.jpg';
$role        = 'user';

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
