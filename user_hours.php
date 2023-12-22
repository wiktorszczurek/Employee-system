<?php
require 'db_config.php';
session_start();
include 'layout.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    echo '<script type="text/javascript">window.location = "login.php";</script>';
    exit;
}

$userId = $_GET['user_id'];

$userSql = "SELECT name FROM users WHERE id='$userId'";
$userResult = $conn->query($userSql);

if ($userResult->num_rows > 0) {
    $user = $userResult->fetch_assoc();
    $userName = $user['name'];
} else {
    die("Nie znaleziono użytkownika o podanym ID");
}
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['entry_id']) && isset($_POST['hours'])) {
    $entryId = $_POST['entry_id'];
    $updatedHours = $_POST['hours'];
    $updateSql = "UPDATE working_hours SET hours='$updatedHours', edited=1 WHERE id='$entryId'";
    if ($conn->query($updateSql) === TRUE) {
        echo "Godziny zostały zaktualizowane.";
    } else {
        echo "Błąd: " . $conn->error;
    }
}
if(isset($_POST['add_hours'])) {
    $date = $_POST['date'];
    $hours = $_POST['hours'];
    $userId = $_GET['user_id'];

    $sql = "INSERT INTO working_hours (user_id, date, hours) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $userId, $date, $hours);  

    if($stmt->execute()) {

        echo "<script>window.location.href = 'user_hours.php?user_id=" . $userId . "';</script>";

    } else {
        echo "<p>Wystąpił błąd podczas dodawania godzin: " . $stmt->error . "</p>";
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



if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['monthYear'])) {
    $monthYear = explode('-', $_POST['monthYear']);
    $selectedMonth = $monthYear[1];
    $selectedYear = $monthYear[0];
} else {
    $selectedMonth = date('m');
    $selectedYear = date('Y');
}
$sql = "SELECT id, date, hours, start_time, end_time, edited FROM working_hours WHERE user_id='$userId' AND MONTH(date) = '$selectedMonth' AND YEAR(date) = '$selectedYear' ORDER BY date DESC";

$result = $conn->query($sql);





$selectedMonthName = $miesiaceNarzednik[$selectedMonth];

$sumHoursQuery = "SELECT SUM(hours) AS total_hours FROM working_hours WHERE user_id='$userId' AND MONTH(date)='$selectedMonth' AND YEAR(date)='$selectedYear'";
$sumResult = $conn->query($sumHoursQuery);
$totalHours = 0;
if ($sumResult->num_rows > 0) {
    $row = $sumResult->fetch_assoc();
    $totalHours = $row['total_hours'];
}
?>
    <style>
                .container {
            background-color: #f0f8ff; 
            border: 1px solid #d1e0e0;
            border-radius: 5px;
            padding: 10px;

            font-size: 1.1em;
            width: 350px;
            margin: 20px auto;
        }

       .container h1 {

color: #333;
text-align: center;
margin-bottom: 20px;
font-size: 1.2em;
border-bottom: 2px solid #02b1d9;
padding-bottom: 10px;
letter-spacing: 1px; 
}

.container a {
    color: #02b1d9;
    text-decoration: none;
    margin: 0 10px;
    padding: 15px 0; 
    border: 1px solid #02b1d9;
    border-radius: 5px;
    transition: background-color 0.3s;
    display: inline-block; 
    width: 250px; 
    text-align: center; 
}


        .container a:hover {
            background-color: #02b1d9;
            color: #fff;
        }
input[type="submit"] {
    display: block;
    width: 200px;
    padding: 10px;
    font-size: 18px;
    background-color: transparent;
    color: #02b1d9;
    border: 1px solid #02b1d9;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s ease;
    margin-top: 5px;
    box-sizing: border-box; 
    margin: 0 auto;
  }
input[type="submit"]:hover {
            background-color: #02b1d9;
            color: #fff;
        }
        input[type="submit"].aktu {
    display: block;
    width: 100px;
    padding: 5px;
    font-size: 12px;
    background-color: #4CAF50;
    color: white;
    border-color: #4CAF50;
    border-radius: 5px;
    cursor: pointer;


    box-sizing: border-box; 
    margin: 0 auto;
  }
.main-user{
    width: 400px;
    margin: 0 auto;
}
  .main-user h2 {
    color: #444;
}

.main-user form {
    background-color: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

.main-user label {
    display: block;
    margin-top: 10px;
}

.main-user input[type="date"],
.main-user input[type="number"] {
    width: 100%;
    padding: 8px;
    margin-top: 5px;
    border-radius: 4px;
    border: 1px solid #ddd;
    box-sizing: border-box;
}
    </style>

<div class="container">
<h1><?php echo $userName; ?></h1>


<form action="" class="custom-form" method="post">
    <label for="monthYear">Miesiąc i rok:</label>
    <input type="month" name="monthYear" value="<?php echo $selectedYear . '-' . str_pad($selectedMonth, 2, '0', STR_PAD_LEFT); ?>" required>
    <input type="submit" value="Wybierz">
</form>

    </div>
    <br>
    <div class="main-user">
    <p>Całkowita liczba godzin w <?php echo $selectedMonthName; ?> to: <strong><?php echo $totalHours; ?></strong></p>

    <br>
    <br>
    <h2>Dodaj godziny pracy dla użytkownika</h2>
    <br>
<form action="" method="post">
    <label for="date">Data:</label>
    <input type="date" name="date" required>
    
    <label for="hours">Godziny:</label>
    <input type="number" name="hours" step="0.01" required>
<br>
<br>
    <input type="submit" name="add_hours" value="Dodaj godziny">
</form>
    </div>
<br>

    <?php 
if ($result->num_rows > 0) {
    echo '<table border="1">';
    echo '<tr><th>Data</th><th>Godzina rozpoczęcia</th><th>Godzina zakończenia</th><th>Godziny</th><th>Akcja</th><th>Edytowane</th></tr>';
    while ($row = $result->fetch_assoc()) {
        echo '<form action="user_hours.php?user_id=' . $userId . '" method="post">';
        echo '<tr>';
        echo '<td>' . $row['date'] . '</td>';

        $formattedStartTime = $row['start_time'] ? date('H:i', strtotime($row['start_time'])) : '---';

        $formattedEndTime = $row['end_time'] ? date('H:i', strtotime($row['end_time'])) : '---';

        echo '<td>' . $formattedStartTime . '</td>';
        echo '<td>' . $formattedEndTime . '</td>';
        echo '<td><input type="number" step="0.5" name="hours" value="' . $row['hours'] . '"></td>'; 
        echo '<td>';
        echo '<input type="hidden" name="entry_id" value="' . $row['id'] . '">';
        echo '<input type="submit" class="aktu" value="Zaktualizuj">';
        echo '</td>';
        $editedText = $row['edited'] ? 'Tak' : 'Nie';
        echo '<td>' . $editedText . '</td>';
        echo '</tr>';
        echo '</form>';
    }
    echo '</table>';
} else {
    echo "<p>Brak godzin pracy dla tego użytkownika.</p>";
}


    ?>

<?php include 'footer.php'; ?>
