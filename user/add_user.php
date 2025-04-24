<?php
include '../connetionDB/config.php'; // ملف الاتصال بقاعدة البيانات

$name        = 'Salma Hussein';
$email       = 's@gmail.com';
$password    = password_hash('s12345', PASSWORD_DEFAULT); // تشفير آمن
$room_number = '101';
$image       = 'salma.jpg';
$role        = 'user';

$sql = "INSERT INTO users (name, email, password, room_number, image, role)
        VALUES (?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssss", $name, $email, $password, $room_number, $image, $role);

if ($stmt->execute()) {
    echo "تمت الإضافة بنجاح";
} else {
    echo "خطأ: " . $stmt->error;
}
?>
