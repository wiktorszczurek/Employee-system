<?php
require 'db_config.php';
session_start();
include 'layout.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    echo '<script type="text/javascript">window.location = "login.php";</script>';
    exit;
}

$userId = $_GET['user_id'] ?? null;
$selectedMonth = $_GET['month'] ?? date('Y-m'); 

if (!$userId) {
    die("Nie podano ID użytkownika");
}


$sql = "SELECT * FROM schedule WHERE user_id = ? AND work_date LIKE ?";
$monthPattern = $selectedMonth . '%'; 
$stmt = $conn->prepare($sql);
$stmt->bind_param('is', $userId, $monthPattern);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "<h1>Grafik pracy użytkownika</h1>";
    echo "<table>";
    echo "<tr><th>Data</th><th>Godzina rozpoczęcia</th><th>Godzina zakończenia</th></tr>";

    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['work_date'] . "</td>";
        echo "<td>" . $row['start_time'] . "</td>";
        echo "<td>" . $row['end_time'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "Brak grafiku pracy dla tego użytkownika.";
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
<form action="user_schedule.php" method="get">
    <input type="hidden" name="user_id" value="<?php echo $userId; ?>">
    <label for="month">Wybierz miesiąc:</label>
    <input type="month" id="month" name="month" value="<?php echo $selectedMonth; ?>">
    <input type="submit" value="Pokaż grafik">
</form>
</body>
</html>