<?php
require 'db_config.php';

session_start();

include 'layout.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    echo '<script type="text/javascript">window.location = "login.php";</script>';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $userId = $_POST['user_id'];
    $newRFID = $_POST['rfid'];

    $stmt = $conn->prepare("UPDATE users SET rfid = ? WHERE id = ?");
    $stmt->bind_param('si', $newRFID, $userId);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $_SESSION['message'] = "RFID został zaktualizowany.";
    } else {
        $_SESSION['error'] = "Nie udało się zaktualizować RFID.";
    }

    echo "<script>window.location.href = 'users.php';</script>";
}
?>