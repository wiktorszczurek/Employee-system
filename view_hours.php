<?php 

require 'db_config.php'; 
session_start();
include 'layout.php';


if (!isset($_SESSION['user_id'])) {
    echo '<script type="text/javascript">window.location = "login.php";</script>';
    exit;
}

$hasTips = false;

$userId = $_SESSION['user_id'];
$sql = "SELECT date, hours, start_time, end_time, tip FROM working_hours WHERE user_id='$userId' ORDER BY date DESC LIMIT 30";

$result = $conn->query($sql);
$recentHours = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $recentHours[] = $row;
        if ($row['tip'] > 0) {
            $hasTips = true;
        }
    }
}
setlocale(LC_TIME, 'pl_PL.UTF-8');

$miesiaceMianownik = [
    1 => 'styczeń',
    2 => 'luty',
    3 => 'marzec',
    4 => 'kwiecień',
    5 => 'maj',
    6 => 'czerwiec',
    7 => 'lipiec',
    8 => 'sierpień',
    9 => 'wrzesień',
    10 => 'październik',
    11 => 'listopad',
    12 => 'grudzień'
];

$miesiaceNarzednik = [
    1 => 'styczniu',
    2 => 'lutym',
    3 => 'marcu',
    4 => 'kwietniu',
    5 => 'maju',
    6 => 'czerwcu',
    7 => 'lipcu',
    8 => 'sierpniu',
    9 => 'wrześniu',
    10 => 'październiku',
    11 => 'listopadzie',
    12 => 'grudniu'
];

$defaultMonth = date('Y-m');
$selectedMonth = $defaultMonth;

if (isset($_POST['selected_month'])) {
    $selectedMonth = $_POST['selected_month'];
}

list($selectedYear, $selectedMonthNum) = explode('-', $selectedMonth);

$selectedMonthName = $miesiaceNarzednik[intval($selectedMonthNum)];




$sumHoursQuery = "SELECT SUM(hours) AS total_hours FROM working_hours WHERE user_id='$userId' AND MONTH(date)='$selectedMonthNum' AND YEAR(date)='$selectedYear'";
$sumResult = $conn->query($sumHoursQuery);
$totalHours = 0;
if ($sumResult->num_rows > 0) {
    $row = $sumResult->fetch_assoc();
    $totalHours = $row['total_hours'];
}


$sumTipsQuery = "SELECT SUM(tip) AS total_tips FROM working_hours WHERE user_id='$userId' AND MONTH(date)='$selectedMonthNum' AND YEAR(date)='$selectedYear'";
$sumTipsResult = $conn->query($sumTipsQuery);
$totalTips = 0;
if ($sumTipsResult->num_rows > 0) {
    $row = $sumTipsResult->fetch_assoc();
    $totalTips = $row['total_tips'];
}


$sql = "SELECT date, hours, start_time, end_time, tip FROM working_hours WHERE user_id='$userId' AND MONTH(date)='$selectedMonthNum' AND YEAR(date)='$selectedYear' ORDER BY date DESC";
$result = $conn->query($sql);
$recentHours = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $recentHours[] = $row;
    }
}
function formatPolishDate($date) {
    setlocale(LC_TIME, 'pl_PL.UTF-8');
    return strftime("%e %B", strtotime($date)); 
}
function formatPolishDateWithDay($date) {
    setlocale(LC_TIME, 'pl_PL.UTF-8');
    return strftime("%e %B (%A)", strtotime($date)); 
}
function formatDayAndDayOfWeek($date) {
    setlocale(LC_TIME, 'pl_PL.UTF-8');
    return strftime("%e. %A", strtotime($date)); 
}

?>

    <div class="date-form">
    <h1>Godziny pracy</h1>

    <form action="" class="custom-form" method="post">
        <input type="month" name="selected_month" value="<?php echo $selectedMonth; ?>">
        <input type="submit" value="Wybierz">
    </form>
    <br>
    <p>Całkowita liczba godzin w <?php echo $selectedMonthName; ?> to: <span style="color: green;"><?php echo $totalHours; ?></span></p>
    <br>
<?php    if ($hasTips) {
    echo '<p>Całkowita suma napiwków w ' . $selectedMonthName . ' to: <span style="color: blue;">' . $totalTips . ' zł</span></p>';
}
?>


    </div>
    <?php 

$selectedMonthName = ucfirst($miesiaceMianownik[intval($selectedMonthNum)]);

if (count($recentHours) > 0) {
    echo '<table border="1">';
    echo '<tr><th>' . $selectedMonthName . '</th><th>Godzina rozpoczęcia</th><th>Godzina zakończenia</th><th>Ilość godzin</th>';
    if ($hasTips) {
        echo '<th>Napiwki</th>';
    }
    echo '</tr>';
    foreach ($recentHours as $entry) {
        $formattedDate = formatDayAndDayOfWeek($entry['date']);
        $formattedStartTime = date('H:i', strtotime($entry['start_time'])); 
        $formattedEndTime = $entry['end_time'] !== NULL ? date('H:i', strtotime($entry['end_time'])) : "Nie zakończono";
        $tip = $entry['tip'] !== NULL ? $entry['tip'] : "Brak"; 

        echo '<tr>';
        echo '<td>' . $formattedDate . '</td>';
        echo '<td>' . $formattedStartTime . '</td>'; 
        echo '<td>' . $formattedEndTime . '</td>';
        echo '<td>' . $entry['hours'] . '</td>';
        if ($hasTips) {
            $tip = $entry['tip'] !== NULL ? $entry['tip'] : "Brak";
            echo '<td>' . $tip . '</td>'; 
        }
        echo '</tr>';
    }
    echo '</table>';
} else {
    echo "<p>Nie dodałeś jeszcze żadnych godzin pracy.</p>";
}





?>


<?php include 'footer.php'; ?>


</body>
</html>
