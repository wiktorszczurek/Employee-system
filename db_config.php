<?php
$servername = "";
$username = "";
$password = "";
$database = "";

$conn = new mysqli($servername, $username, $password, $database);


$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    die("Błąd połączenia: " . $conn->connect_error);
}
?>
