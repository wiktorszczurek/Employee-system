<?php
session_start();


if (isset($_SESSION['rfid_login'])) {
    unset($_SESSION['rfid_login']);
}



session_destroy(); 
header('Location: login.php'); 
exit();
