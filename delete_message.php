<?php
require 'db_config.php';
session_start();

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$messageId = $conn->real_escape_string($_GET['id']);


$deleteSql = "DELETE FROM messages WHERE id='$messageId' AND sender_id='$userId'";
if ($conn->query($deleteSql)) {

    header('Location: messages.php?message=MessageDeleted');
} else {

    header('Location: messages.php?error=DeleteFailed');
}
?>
