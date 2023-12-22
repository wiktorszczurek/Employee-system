<?php
require 'db_config.php';
session_start();
include 'layout.php';

if (!isset($_SESSION['is_admin']) || ($_SESSION['is_admin'] != 1 && $_SESSION['is_admin'] != 2)) {
    echo '<script type="text/javascript">window.location = "login.php";</script>';
    exit;
}


$selectedCategory = $_POST['selected_category'] ?? '';
$month = date('Y-m'); 
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['selected_month'])) {
    $month = $_POST['selected_month'];
}

$userCategory = ''; 
if ($_SESSION['is_admin'] == 2) {
    $userCategoryQuery = $conn->prepare("SELECT category FROM users WHERE id = ?");
    $userCategoryQuery->bind_param('i', $_SESSION['user_id']);
    $userCategoryQuery->execute();
    $result = $userCategoryQuery->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $userCategory = $row['category'];
        $selectedCategory = $userCategory; 
    }
}

if ($_SESSION['is_admin'] == 1) {
    $categoriesQuery = "SELECT name FROM categories WHERE name != 'Admin'";
} elseif ($_SESSION['is_admin'] == 2 && !empty($userCategory)) {
    $categoriesQuery = "SELECT name FROM categories WHERE name = '" . $userCategory . "'";
} else {
    die("Brak dostępu do danych kategorii");
}

$categoriesResult = $conn->query($categoriesQuery);
if ($categoriesResult === false) {
    die("Error: " . $conn->error);
}

$categories = [];
while ($categoryRow = $categoriesResult->fetch_assoc()) {
    $categories[] = $categoryRow['name'];
}

$stmt = $conn->prepare("SELECT id, name FROM users WHERE category=?");
$stmt->bind_param('s', $selectedCategory);
$stmt->execute();
$resultUsers = $stmt->get_result();

$usernames = [];
while ($user = $resultUsers->fetch_assoc()) {
    $usernames[$user['id']] = $user['name'];
}

$daysInMonth = date('t', strtotime($month . '-01'));
$schedule = [];
foreach ($usernames as $userId => $username) {
    for ($i = 1; $i <= $daysInMonth; $i++) {
        $workDate = date('Y-m-d', strtotime($month . "-$i"));
        $stmt = $conn->prepare("SELECT start_time FROM schedules WHERE user_id = ? AND work_date = ?");
        $stmt->bind_param('is', $userId, $workDate);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $data = $result->fetch_assoc();
            $schedule[$userId][$i] = $data['start_time'];
        } else {
            $schedule[$userId][$i] = "";
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['schedule_submit'])) {
    $datesToUpdate = []; 

    foreach ($usernames as $userId => $username) {
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $workDate = date('Y-m-d', strtotime($month . "-$i"));
            $workHour = $_POST["user_${userId}_day_$i"] ?? null;


            $stmt = $conn->prepare("SELECT start_time FROM schedules WHERE user_id = ? AND work_date = ?");
            $stmt->bind_param('is', $userId, $workDate);
            $stmt->execute();
            $result = $stmt->get_result();
            $existingEntry = $result->fetch_assoc();

            if ($workHour) {

                $stmt = $conn->prepare("INSERT INTO schedules (user_id, work_date, start_time) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE start_time = ?");
                $stmt->bind_param('isss', $userId, $workDate, $workHour, $workHour);
                $stmt->execute();
                $datesToUpdate[$workDate] = true;
            } elseif ($existingEntry) {

                $stmt = $conn->prepare("DELETE FROM schedules WHERE user_id = ? AND work_date = ?");
                $stmt->bind_param('is', $userId, $workDate);
                $stmt->execute();

                $stmt = $conn->prepare("INSERT INTO removed_users (user_id, work_date) VALUES (?, ?)");
                $stmt->bind_param('is', $userId, $workDate);
                $stmt->execute();
            }
        }
    }


function generateEmailContent($selectedCategory, $date, $removedUser, $usernames, $scheduleResult) {
    $logoUrl = 'http://www.willateam.pl/willa2.png';
    $message = "
    <!DOCTYPE html>
    <html>
    <head>
        <title>Zmiana w grafiku pracy</title>
        <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #02b1d9;
            color: #FFFFFF; /* Białe napisy */
            line-height: 1.6;
        }
        .container {
            width: 80%;
            margin: 0 auto;
            background: #02b1d9;
            padding: 20px;
            border: 1px solid #ddd; /* Subtelna, jasna ramka */
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(255,255,255,0.1); /* Jasny cień dla kontrastu */
        }
        h1 {
            color: #FFFFFF; /* Biały kolor nagłówka */
            font-size: 1.1rem;
            text-align: center;
        }
        ul {
            list-style: none;
            padding: 0;
        }
        li {
            background-color: #019fc3;
            color: #FFFFFF; /* Białe napisy na liście */
            margin-bottom: 10px;
            padding: 10px;
            border-radius: 5px;
     
        }
        strong {
            color: #FFFFFF;
            font-weight: bold;
        }
        p {
            color: #FFFFFF;
        }
    </style>
    
    
    </head>
    <body>
        <div class='container'>
        <img src='$logoUrl' alt='Logo firmy' style='max-width: 85px; display: block; margin: 0 auto;'>
            <h1>Zmiana w grafiku dla kategorii: $selectedCategory na dzień $date</h1>
            <p><strong>Usunięto:</strong> {$usernames[$removedUser['user_id']]}</p>
            <p><strong>Aktualny grafik na ten dzień:</strong></p>
            <ul>
    ";


    while ($row = $scheduleResult->fetch_assoc()) {
        $message .= "<li>{$row['name']}, Start: {$row['start_time']}</li>";
    }

    $message .= "
            </ul>
        </div>
    </body>
    </html>
    ";

    return $message;
}


foreach (array_keys($datesToUpdate) as $date) {
    $stmt = $conn->prepare("SELECT user_id FROM removed_users WHERE work_date = ?");
    $stmt->bind_param('s', $date);
    $stmt->execute();
    $removedUserResult = $stmt->get_result();

    if ($removedUser = $removedUserResult->fetch_assoc()) {

        $stmt = $conn->prepare("SELECT users.name, schedules.start_time FROM schedules 
                                JOIN users ON schedules.user_id = users.id 
                                WHERE schedules.work_date = ? AND users.category = ?");
        $stmt->bind_param('ss', $date, $selectedCategory);
        $stmt->execute();
        $scheduleResult = $stmt->get_result();


        $emailContent = generateEmailContent($selectedCategory, $date, $removedUser, $usernames, $scheduleResult);


        $to = 'wiktor.szczurek1@gmail.com'; 
        $subject = 'Zmiana w grafiku pracy';
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8" . "\r\n";
        $headers .= 'From: willateam@grafik.com' . "\r\n" .
                   'Reply-To: webmaster@example.com' . "\r\n" .
                   'X-Mailer: PHP/' . phpversion();

        mail($to, $subject, $emailContent, $headers);

        $stmtDelete = $conn->prepare("DELETE FROM removed_users WHERE work_date = ?");
        $stmtDelete->bind_param('s', $date);
        $stmtDelete->execute();
    }
}

echo "<script>window.location.href='create_schedule.php';</script>";
}




if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['remove_schedule'])) {
    list($userIdToRemove, $dayToRemove) = explode('_', $_POST['remove_schedule']);
    $workDateToRemove = date('Y-m-d', strtotime($month . "-$dayToRemove"));


    $stmt = $conn->prepare("DELETE FROM schedules WHERE user_id = ? AND work_date = ?");
    $stmt->bind_param('is', $userIdToRemove, $workDateToRemove);
    $stmt->execute();

    $stmt = $conn->prepare("INSERT INTO removed_users (user_id, work_date) VALUES (?, ?)");
    $stmt->bind_param('is', $userIdToRemove, $workDateToRemove);
    $stmt->execute();

    echo "Usunięto godzinę pracy!";
}

$daysCount = [];
foreach ($usernames as $userId => $username) {
    $daysCount[$userId] = 0;
    for ($i = 1; $i <= $daysInMonth; $i++) {
        if (!empty($schedule[$userId][$i])) {
            $daysCount[$userId]++;
        }
    }
}
function getDayNameAbbreviation($date) {
    $dayNumber = date('N', strtotime($date)); 
    $days = ['Pn', 'Wt', 'Śr', 'Czw', 'Pt', 'Sb', 'Nd'];
    return $days[$dayNumber - 1];
}
function getMonthName($date) {
    $monthNumber = date('n', strtotime($date)); 
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
    return $months[$monthNumber];
}



?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
     .save input[type="submit"] {
  display: block;
  width: 250px; 
  padding: 12px;
  font-size: 20px;
  font-weight: bold; 
  color: #fff;
  background-color: #02b1d9;
  border: 2px solid transparent; 
  border-radius: 10px; 
  cursor: pointer;
  transition: all 0.3s ease; 
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2); 
  text-transform: uppercase; 
  margin: 20px auto; 
  position: relative; 
  overflow: hidden; 
}

    </style>
</head>
<body>
<div class="date-form">
    <h1>Ustaw grafik</h1>
<form action="" method="post" class="custom-form">
    <label for="selected_category">Wybierz kategorię:</label>
    <select name="selected_category" required>
    <?php foreach ($categories as $category) : ?>
        <?php if (!($_SESSION['is_admin'] == 1 && $category == 'Admin')) : ?>
            <option value="<?php echo $category; ?>" <?php echo ($selectedCategory == $category) ? 'selected' : ''; ?>><?php echo $category; ?></option>
        <?php endif; ?>
    <?php endforeach; ?>
</select>


    <br><br>
    <label for="selected_month">Wybierz miesiąc:</label>
    <input type="month" name="selected_month" value="<?php echo $month; ?>" required>

    <input type="submit" value="Pokaż/Ustaw grafik">
</form>
<br>
</div>

<form action="" method="post">
    <input type="hidden" name="selected_category" value="<?php echo $selectedCategory; ?>">
    <input type="hidden" name="schedule_submit" value="1">
    <input type="hidden" name="selected_month" value="<?php echo $month; ?>">

    <table border="1">
        <tr>
        <th><?php echo getMonthName($month . '-01'); ?></th>
            <?php foreach ($usernames as $username) : ?>
                <th><?php echo $username; ?></th>
            <?php endforeach; ?>
        </tr>

        <?php for ($i = 1; $i <= $daysInMonth; $i++) : ?>
    <tr>
        <?php
            $currentDate = $month . '-' . sprintf('%02d', $i); 
            $dayAbbreviation = getDayNameAbbreviation($currentDate); 
        ?>
<td><?php echo $i . '. ' . $dayAbbreviation . ''; ?></td>
<?php foreach ($usernames as $userId => $username) : ?>
    <td>
        <div style="position: relative;">
            <input 
                type="time" 
                name="user_<?php echo $userId; ?>_day_<?php echo $i; ?>" 
                value="<?php echo $schedule[$userId][$i] ?? ''; ?>"
                onfocus="if(this.value==''){this.value='09:00:00'}"
                id="input_<?php echo $userId; ?>_<?php echo $i; ?>"  
                onchange="showClearButton('<?php echo $userId; ?>', '<?php echo $i; ?>')"  
            >
            <img 
                src="images/delete.png" 
                alt="Usuń" 
                width="25" 
                height="25" 
                style="position: absolute; right: 0; top: 0; margin-top: 5px; display: none; cursor: pointer;"
                onclick="clearInput('<?php echo $userId; ?>', '<?php echo $i; ?>')" 
                id="clear_<?php echo $userId; ?>_<?php echo $i; ?>"  
            >
        </div>
        <?php if (!empty($schedule[$userId][$i])) : ?>
    <button 
        type="submit" 
        name="remove_schedule" 
        value="<?php echo $userId . '_' . $i; ?>"
        onclick="return confirm('Czy na pewno chcesz usunąć?')"
        style="background: none; border: none; padding: 0; cursor: pointer;"
    >
        <img src="images/delete.png" alt="Usuń" width="25" height="25" style="margin-top: 5px;">
    </button>
<?php endif; ?>

    </td>
<?php endforeach; ?>
</tr>
<?php endfor; ?>

        <tr>
        <tr><th>Ilość dni</th>
            <?php foreach ($usernames as $userId => $username) : ?>
                <td><?php echo $daysCount[$userId]; ?></td>
            <?php endforeach; ?>
        </tr>

    </table>
    <div class="save">
    <input type="submit" value="Zapisz grafik">
            </div>
</form>
<br>
<br>
<br>
<?php include 'footer.php'; ?>

</body>
<script>
function showClearButton(userId, day) {

    var clearButtonId = "clear_" + userId + "_" + day;
    var clearButton = document.getElementById(clearButtonId);
    var inputValue = document.getElementById("input_" + userId + "_" + day).value;

    if (inputValue) {
        clearButton.style.display = 'block';
    } else {
        clearButton.style.display = 'none';
    }
}

function clearInput(userId, day) {

    var inputId = "input_" + userId + "_" + day;
    var clearButtonId = "clear_" + userId + "_" + day;
    
    document.getElementById(inputId).value = '';
    document.getElementById(clearButtonId).style.display = 'none';
}
</script>





</html>