<?php 
require 'db_config.php';
session_start();
include 'layout.php';
mb_internal_encoding("UTF-8");

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    echo '<script type="text/javascript">window.location = "login.php";</script>';
    exit;
}

$selectedCategory = '';
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['selected_category'])) {
    $selectedCategory = $_POST['selected_category'];
}

$sql = "SELECT wh.date, u.name, wh.start_time, wh.end_time, wh.hours, u.category 
        FROM working_hours AS wh
        JOIN users AS u ON wh.user_id = u.id" .
        ($selectedCategory ? " WHERE u.category = '$selectedCategory'" : " WHERE u.category != 'Admin'") .
        " ORDER BY wh.date DESC, wh.start_time";
$result = $conn->query($sql);

function getPolishDayOfWeek($date) {
    setlocale(LC_TIME, 'pl_PL.UTF-8');
    return strftime("%A", strtotime($date));
}

$groupedByDate = [];
while ($row = $result->fetch_assoc()) {
    $groupedByDate[$row['date']][] = $row;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zmiany pracownicze</title>
</head>
<body>
<div class="date-form">
    <h1>Zmiany pracownicze</h1>
    <form action="" method="post" class="custom-form">
        <label for="selected_category">Wybierz kategorię:</label>
        <select name="selected_category">
        <option value="">Wszystkie</option>
            <?php
            $categorySql = "SELECT DISTINCT category FROM users WHERE category != 'Admin' ORDER BY category";
            $categoryResult = $conn->query($categorySql);
            while ($row = $categoryResult->fetch_assoc()) {
                echo "<option value='" . $row['category'] . "'" . ($selectedCategory == $row['category'] ? " selected" : "") . ">" . $row['category'] . "</option>";
            }
            ?>
        </select>
        <input type="submit" value="Wybierz">
    </form>
</div>
<?php
foreach ($groupedByDate as $date => $entries) {
    $polishDayOfWeek = getPolishDayOfWeek($date);
    echo "<h2>" . $date . ", " . $polishDayOfWeek . "</h2>";
    echo "<table border='1'>";
    echo "<tr><th>Imię i Nazwisko</th><th>Rozpoczęcie</th><th>Zakończenie</th><th>Ilość godzin</th></tr>";

    foreach ($entries as $entry) {
        $startTime = new DateTime($entry['start_time']);
        $endTimeString = $entry['end_time'] ? (new DateTime($entry['end_time']))->format('H:i') : 'Nie zakończono'; 
    
        echo "<tr>";
        echo "<td>" . $entry['name'] . "</td>";
        echo "<td>" . $startTime->format('H:i') . "</td>";
        echo "<td>" . $endTimeString . "</td>"; 
        echo "<td>" . $entry['hours'] . "</td>";
        echo "</tr>";
    }
    echo "</table><br>";
}
?>
<?php include 'footer.php'; ?>
</body>
</html>
