<?php
require 'db_config.php'; 
session_start();
include 'layout.php';

if (!isset($_SESSION['user_id'])) {
    echo '<script type="text/javascript">window.location = "login.php";</script>';
    exit;
}

$isAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
$userId = $isAdmin && isset($_GET['user_id']) ? $_GET['user_id'] : $_SESSION['user_id'];

if (!$userId) {
    die("Nie podano ID użytkownika");
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.3.2/html2canvas.min.js"></script>

</head>
<style>
.table-with-margin {
    margin: 20px;
    background-color: white;
}
.downloadButton {
        padding: 10px 20px;
        background-color: #4CAF50;
        color: white;
        border: none;
        border-radius: 4px;
        font-size: 16px;
        cursor: pointer;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        transition: background-color 0.3s ease;
        width: 150px;
        margin: 0 auto;
    }
</style>

<body>



<?php 
function polishMonthName($date) {
    $monthNumber = date('n', strtotime($date)); // Pobieranie numeru miesiąca
    $months = [
        1 => 'Styczeń',
        2 => 'Luty',
        3 => 'Marzec',
        4 => 'Kwiecień',
        5 => 'Maj',
        6 => 'Czerwiec',
        7 => 'Lipiec',
        8 => 'Sierpień',
        9 => 'Wrzesień',
        10 => 'Październik',
        11 => 'Listopad',
        12 => 'Grudzień'
    ];

    return $months[$monthNumber]; // Zwracanie nazwy miesiąca
}
if ($isAdmin) {
    $userStmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
    $userStmt->bind_param('i', $userId);
    $userStmt->execute();
    $userResult = $userStmt->get_result();
    $userName = $userResult->num_rows > 0 ? $userResult->fetch_assoc()['name'] : "Nieznany użytkownik";
    $headerTitle =  $userName;
} else {
    $headerTitle = "Mój grafik";
}


setlocale(LC_TIME, 'pl_PL.utf8');

    $selectedMonth = $_POST['selected_month'] ?? date('Y-m');

    $stmt = $conn->prepare("SELECT work_date, start_time FROM schedules WHERE user_id = ? AND DATE_FORMAT(work_date, '%Y-%m') = ? ORDER BY work_date ASC");
    $stmt->bind_param('is', $userId, $selectedMonth);
    $stmt->execute();
    $result = $stmt->get_result();
echo "<div class='date-form'>";
echo "<h1>" . $headerTitle . "</h1>";
echo "<form method='post' class='custom-form''>
        
        <input type='month' name='selected_month' value='" . $selectedMonth . "'>
        <input type='submit' value='Wybierz'>
      </form>";
      echo "</div>";

      echo "<button class='downloadButton' onclick='downloadTableAsImage()'>Pobierz Grafik</button>";



echo "<table>";
echo "<tr>
        <th>" . polishMonthName($selectedMonth . '-01') . "</th>
        <th>Godzina rozpoczęcia</th>
      </tr>";


$lastWeekNumber = null;

while ($row = $result->fetch_assoc()) {
    $currentDate = $row['work_date'];
    $currentWeekNumber = date('W', strtotime($currentDate)); // numer tygodnia dla obecnej daty

    if ($lastWeekNumber !== null && $currentWeekNumber != $lastWeekNumber) {
        echo "<tr><td colspan='3' style='background-color: grey; height: 10px;'></td></tr>"; // Pusty wiersz oddzielający tygodnie
    }

    echo "<tr>
    <td>" . strftime('%e. %A', strtotime($currentDate)) . "</td>
    <td>" . substr($row['start_time'], 0, 5) . "</td>
  </tr>";

    $lastWeekNumber = $currentWeekNumber;
}

echo "</table>";
echo "<script>var selectedMonth = '" . polishMonthName($selectedMonth . '-01') . "';</script>";

?>





<?php include 'footer.php'; ?>

</body>
<script>
function downloadTableAsImage() {
    html2canvas(document.querySelector("table")).then(canvas => {
        let image = canvas.toDataURL("image/png").replace("image/png", "image/octet-stream");
        let link = document.createElement('a');
        link.download = selectedMonth + '_mojgrafik.png'; // Użycie zmiennej selectedMonth
        link.href = image;
        link.click();
    });
}
</script>


</html>


