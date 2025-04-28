<?php
session_start();

if (isset($_POST['alertMessage']) && isset($_POST['alertType'])) {
    $_SESSION['alertMessage'] = $_POST['alertMessage'];
    $_SESSION['alertType'] = $_POST['alertType'];
}

exit();
?>