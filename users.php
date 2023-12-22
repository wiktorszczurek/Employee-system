<?php
require 'db_config.php';

session_start();

include 'layout.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    echo '<script type="text/javascript">window.location = "login.php";</script>';
    exit;
}



$categoriesQuery = "SELECT name FROM categories";
$categoriesResult = $conn->query($categoriesQuery);

$categories = [];
while ($categoryRow = $categoriesResult->fetch_assoc()) {
    $categories[] = $categoryRow['name'];
}

function getMonthNameInPolish($monthNumber) {
    $monthsInPolish = [
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
    return $monthsInPolish[$monthNumber];
}

$currentMonthName = getMonthNameInPolish(date('n'));
$previousMonthName = getMonthNameInPolish(date('n') - 1 === 0 ? 12 : date('n') - 1);

function generateUniquePassword($conn) {
    do {
        $newPassword = substr(str_shuffle(str_repeat($x='0123456789', ceil(5/strlen($x)))), 1, 5);
        $query = $conn->prepare("SELECT id FROM users WHERE password = ?");
        $query->bind_param('s', $newPassword);
        $query->execute();
        $result = $query->get_result();
    } while ($result->num_rows > 0);

    return $newPassword;
}


if (isset($_POST['reset_password'])) {
    $userId = $_POST['user_id'];
    $newPassword = generateUniquePassword($conn);


    $updateQuery = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $updateQuery->bind_param('si', $newPassword, $userId);
    $updateQuery->execute();

    if ($updateQuery->affected_rows > 0) {
        echo "Hasło zostało zresetowane.";
    } else {
        echo "Błąd przy resetowaniu hasła.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administracyjny</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.16.2/xlsx.full.min.js"></script>

    <style>

    .admin-name {
        color: #ff0000; 
    }

    .hidden-password {
        color: transparent;
        text-shadow: 0 0 5px rgba(0,0,0,1);
        cursor: pointer;
    }

    .excel{
        width: 300px;
        background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
    margin-bottom: 20px;

    }
    #monthSelect {
        padding: 8px 16px;
        border: 1px solid #ccc;
        border-radius: 4px;
        background-color: white;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        font-size: 16px;
        margin-right: 8px;
    }

    #exportButton {
        padding: 10px 20px;
        background-color: #4CAF50;
        color: white;
        border: none;
        border-radius: 4px;
        font-size: 16px;
        cursor: pointer;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        transition: background-color 0.3s ease;
    }
    input[type='submit'] {
        padding: 5px 10px;
        background-color: #4CAF50;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        margin-right: 5px;
        transition: background-color 0.3s ease;
    }


    input[type='submit'][name='reset_password'] {
        margin-top: 8px;
        background-color: #f44336;
    }

    input[type='submit'][value='Aktualizuj'] {
        background-color: #008CBA;
        margin-top: 8px;
    }


    a[href*='delete_user.php'] {
        padding: 5px 10px;
        background-color: #f44336;
        color: white;
        border-radius: 4px;
        text-decoration: none;
        display: inline-block;
        transition: background-color 0.3s ease;
    }

.add-user-link {
    background-color: #02b1d9;
    color: #fff; 
    padding: 10px 20px; 
    text-decoration: none;
    border-radius: 5px; 
    font-weight: bold; 
}


</style>

</head>

<body>


<main>

</div>
    <div class="excel">
<select id="monthSelect">
    <option value="current" data-month-name="<?php echo $currentMonthName; ?>"><?php echo $currentMonthName; ?></option>
    <option value="previous" data-month-name="<?php echo $previousMonthName; ?>"><?php echo $previousMonthName; ?></option>
</select>
<br>
<br>

<button id="exportButton">Pobierz do Excela</button>
</div>
<a href="add_user.php" class="add-user-link">Dodaj Użytkownika</a>
<br>
<a href="users_vacation.php" class="add-user-link">Urlopy</a>
<br>


<?php
foreach ($categories as $category) {
    echo "<h2>" . ucfirst($category) . "</h2>"; 

    $stmt = $conn->prepare("SELECT * FROM users WHERE category=?");
    $stmt->bind_param('s', $category);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<table border='1'>
        <tr>

            <th class='name-column'>Imię i Nazwisko</th>

            <th class='current-month-hours'>Suma godzin ($currentMonthName)</th>
            <th class='previous-month-hours'>Suma godzin ($previousMonthName)</th>
            <th>Ostatnio dodane godziny</th>

        </tr>";

        while($row = $result->fetch_assoc()) {

            $hoursSql = "SELECT SUM(hours) as total_hours FROM working_hours WHERE user_id='" . $row['id'] . "'";
            $hoursResult = $conn->query($hoursSql);
            $totalHours = $hoursResult->fetch_assoc()['total_hours'];

            $currentMonthHoursSql = "SELECT SUM(hours) as total_hours_current_month FROM working_hours WHERE user_id='" . $row['id'] . "' AND MONTH(date) = MONTH(CURRENT_DATE()) AND YEAR(date) = YEAR(CURRENT_DATE())";
            $resultCurrentMonth = $conn->query($currentMonthHoursSql);
            $totalHoursCurrentMonth = $resultCurrentMonth->fetch_assoc()['total_hours_current_month'] ?? 0;

            $previousMonthHoursSql = "SELECT SUM(hours) as total_hours_previous_month FROM working_hours WHERE user_id='" . $row['id'] . "' AND MONTH(date) = MONTH(CURRENT_DATE() - INTERVAL 1 MONTH) AND YEAR(date) = YEAR(CURRENT_DATE() - INTERVAL 1 MONTH)";
            $resultPreviousMonth = $conn->query($previousMonthHoursSql);
            $totalHoursPreviousMonth = $resultPreviousMonth->fetch_assoc()['total_hours_previous_month'] ?? 0;

            $recentHoursSql = "SELECT hours, date as recent_date FROM working_hours WHERE user_id='" . $row['id'] . "' ORDER BY date DESC LIMIT 1";
            $recentHoursResult = $conn->query($recentHoursSql);

            $recentHours = "Brak";
            $recentDate = "";
            $nameClass = $row['is_admin'] == 2 ? 'admin-name' : '';


            if ($recentHoursResult->num_rows > 0) {
                $recentData = $recentHoursResult->fetch_assoc();
                $recentHours = $recentData['hours'];
                $recentDate = "(" . $recentData['recent_date'] . ")";
            }
            if (isset($_SESSION['error'])) {
                echo "<p>Błąd: " . $_SESSION['error'] . "</p>";
                unset($_SESSION['error']);
            }
            echo "<tr>";

            echo "<td class='name-column'><a href='user_manage.php?user_id=" . $row["id"] . "' class='" . $nameClass . "'>" . $row["name"] . "</a></td>";



                  echo "<td class='current-month-hours'>" . $totalHoursCurrentMonth . "</td>";
                  echo "<td class='previous-month-hours'>" . $totalHoursPreviousMonth . "</td>";
                  echo "<td>" . $recentHours . " " . $recentDate . "</td>";
                  
                  echo "</tr>";
              }
              echo "</table>";
    } else {
        echo "Brak użytkowników w kategorii " . ucfirst($category) . ".<br>";
    }
}
?>


</main>
<?php include 'footer.php'; ?>
</body>
<script>
function confirmUpdate(userName) {
    return confirm("Czy na pewno chcesz zaktualizować RFID dla użytkownika " + userName + "?");
}
</script>
<script>
function togglePasswordVisibility(span) {
    if (span.classList.contains('hidden-password')) {
        span.classList.remove('hidden-password');
    } else {
        span.classList.add('hidden-password');
    }
}
</script>
<script>
function confirmReset() {
    return confirm("Czy na pewno chcesz zresetować hasło dla tego użytkownika?");
}
</script>

<script>
document.getElementById('exportButton').addEventListener('click', function() {
    var selectedMonthElement = document.getElementById('monthSelect');
    var selectedMonth = selectedMonthElement.value;
    var monthName = selectedMonthElement.options[selectedMonthElement.selectedIndex].text;

    var wb = XLSX.utils.book_new();
    var ws_data = [['Imię i Nazwisko', 'Godziny ' + monthName]]; 

    document.querySelectorAll('table').forEach(function(table) {
        table.querySelectorAll('tr').forEach(function(row, index) {
            if (index > 0) { 
                var name = row.querySelector('.name-column') ? row.querySelector('.name-column').textContent : '';
                var hours = row.querySelector(selectedMonth === 'current' ? '.current-month-hours' : '.previous-month-hours') ? row.querySelector(selectedMonth === 'current' ? '.current-month-hours' : '.previous-month-hours').textContent : '';
                ws_data.push([name, hours]); 
            }
        });
    });

    var ws = XLSX.utils.aoa_to_sheet(ws_data);
    XLSX.utils.book_append_sheet(wb, ws, 'Dane');

    var filename = 'Godziny_' + monthName.replace(/\s+/g, '') + '.xlsx';
    XLSX.writeFile(wb, filename);
});



</script>

</html>