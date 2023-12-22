<?php
$servername = "localhost";
$username = "srv56072_system";
$password = "1234";
$database = "srv56072_system";

$conn = new mysqli($servername, $username, $password, $database);


$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    die("Błąd połączenia: " . $conn->connect_error);
}
?>
