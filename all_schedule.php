<?php 
require 'db_config.php';
session_start();
include 'layout.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    echo '<script type="text/javascript">window.location = "login.php";</script>';
    exit;
}

$month = date('Y-m'); 
$selectedCategory = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['selected_month'])) {
        $month = $_POST['selected_month']; 
    }
    if (isset($_POST['selected_category'])) {
        $selectedCategory = $_POST['selected_category'];
    }
}

if (empty($selectedCategory)) {

    $sqlFirstCategory = "SELECT DISTINCT category FROM users WHERE category != 'admin' LIMIT 1";
    $resultFirstCategory = $conn->query($sqlFirstCategory);
    if ($resultFirstCategory && $resultFirstCategory->num_rows > 0) {
        $firstCategoryData = $resultFirstCategory->fetch_assoc();
        $selectedCategory = $firstCategoryData['category'];
    }
}

$monthNames = [
    '01' => 'Styczeń', '02' => 'Luty', '03' => 'Marzec',
    '04' => 'Kwiecień', '05' => 'Maj', '06' => 'Czerwiec',
    '07' => 'Lipiec', '08' => 'Sierpień', '09' => 'Wrzesień',
    '10' => 'Październik', '11' => 'Listopad', '12' => 'Grudzień'
];


$monthNumber = substr($month, 5, 2);
$monthName = $monthNames[$monthNumber];

function getPolishDayOfWeek($date) {
    $englishDayOfWeek = date('D', strtotime($date));
    $polishDays = [
        'Mon' => 'Pn', 'Tue' => 'Wt', 'Wed' => 'Śr', 
        'Thu' => 'Czw', 'Fri' => 'Pt', 'Sat' => 'Sb', 
        'Sun' => 'Nd'
    ];
    return $polishDays[$englishDayOfWeek];
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
<div class="date-form">
    <h1>Wszystkie grafiki</h1>
    <form class="custom-form" action="" method="post">
    <label for="selected_category">Wybierz kategorię:</label>
        <select name="selected_category" required>

            <?php
            $sqlCategories = "SELECT DISTINCT category FROM users WHERE category != 'admin'";
            $resultCategories = $conn->query($sqlCategories);
            while ($row = $resultCategories->fetch_assoc()) {
                echo "<option value='" . $row['category'] . "'" . ($selectedCategory == $row['category'] ? " selected" : "") . ">" . $row['category'] . "</option>";
            }
            ?>
        </select>
        <br>
        <br>
        <label for="selected_month">Wybierz miesiąc:</label>
        <input type="month" name="selected_month" value="<?php echo $month; ?>" required>

        <input type="submit" value="Pokaż grafik">
    </form>
</div>

<?php
if (!empty($selectedCategory)) {

    $sql = "SELECT id, name FROM users WHERE category='$selectedCategory'";
    $resultUsers = $conn->query($sql);

    $usernames = [];
    while ($user = $resultUsers->fetch_assoc()) {
        $usernames[$user['id']] = $user['name'];
    }


    $daysInMonth = date('t', strtotime($month . '-01'));
    $schedule = [];
    $daysCount = array_fill_keys(array_keys($usernames), 0);  

    foreach ($usernames as $userId => $username) {
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $workDate = date('Y-m-d', strtotime($month . "-$i"));
            $sql = "SELECT start_time FROM schedules WHERE user_id = '$userId' AND work_date = '$workDate'";
            $result = $conn->query($sql);
            if ($result && $result->num_rows > 0) {
                $data = $result->fetch_assoc();
                $schedule[$userId][$i] = $data['start_time'];
                $daysCount[$userId]++;  
            } else {
                $schedule[$userId][$i] = "";
            }
        }
    }

    echo "<br>";
    echo "<h2>Grafik dla kategorii: $selectedCategory</h2>";
    echo "<br>";
    echo "<button class='downloadButton' onclick='downloadTableAsImage(\"" . $selectedCategory . "\", \"" . $month . "\")'>Pobierz Grafik</button>";

    echo "<br>";
    echo "<table border='1'>";
    echo "<tr><th>$monthName</th>";
    foreach ($usernames as $username) {
        echo "<th>" . $username . "</th>";
    }
    echo "</tr>";

    for ($i = 1; $i <= $daysInMonth; $i++) {
        $date = $month . '-' . str_pad($i, 2, '0', STR_PAD_LEFT); 
        $dayOfWeek = getPolishDayOfWeek($date); 
    
        echo "<tr>";
        echo "<td>" . $i . '. ' . $dayOfWeek . "</td>"; 
        foreach ($usernames as $userId => $username) {
            echo "<td>";
            echo !empty($schedule[$userId][$i]) ? substr($schedule[$userId][$i], 0, 5) : '---';
            echo "</td>";
        }
        echo "</tr>";
    }


    echo "<tr><th>Ilość dni</th>";
    foreach ($usernames as $userId => $username) {
        echo "<td>" . $daysCount[$userId] . "</td>";
    }
    echo "</tr>";

    echo "</table><br>";

}
?>
<?php include 'footer.php'; ?>

</body>
<script>
function downloadTableAsImage(category, month) {
    var table = document.querySelector('table');
    var fileName = category + '_' + month + '.png'; 

    html2canvas(table).then(canvas => {
        var link = document.createElement('a');
        link.download = fileName;
        link.href = canvas.toDataURL();
        link.click();
    });
}

</script>

</html>
