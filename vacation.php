<?php
require 'db_config.php';
session_start();
include 'layout.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    echo '<script type="text/javascript">window.location = "login.php";</script>';
    exit;
}

$userId = $_GET['user_id'];

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Nie znaleziono użytkownika o podanym ID");
}

$user = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $totalHours = $_POST['total_hours'];
    $usedHours = $_POST['used_hours'];


    $checkSql = "SELECT * FROM vacation WHERE user_id='$userId'";
    $checkResult = $conn->query($checkSql);

    if ($checkResult->num_rows > 0) {
        $updateSql = "UPDATE vacation SET total_hours='$totalHours', used_hours='$usedHours' WHERE user_id='$userId'";
    } else {
        $updateSql = "INSERT INTO vacation (user_id, total_hours, used_hours) VALUES ('$userId', '$totalHours', '$usedHours')";
    }

    if ($conn->query($updateSql) === TRUE) {
        echo "Dane urlopu zaktualizowane.";
    } else {
        echo "Błąd: " . $conn->error;
    }
}

$query = "SELECT total_hours, used_hours FROM vacation WHERE user_id='$userId'";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $totalHours = $row['total_hours'];
    $usedHours = $row['used_hours'];
    $remainingHours = $totalHours - $usedHours;
} else {
    $totalHours = $usedHours = $remainingHours = 0;
}

$hoursPerDay = 8;
$totalDays = $totalHours / $hoursPerDay;
$usedDays = $usedHours / $hoursPerDay;
$remainingDays = $remainingHours / $hoursPerDay;

function formatDays($hours, $hoursPerDay) {
    $days = $hours / $hoursPerDay;
    return ($days == intval($days)) ? intval($days) : number_format($days, 1);
}

$totalDaysFormatted = formatDays($totalHours, $hoursPerDay);
$usedDaysFormatted = formatDays($usedHours, $hoursPerDay);
$remainingDaysFormatted = formatDays($remainingHours, $hoursPerDay);


$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Urlopy użytkownika</title>
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

        .show-password-btn {
            color: #02b1d9;
            text-decoration: none;
            margin: 0 10px;
            padding: 5px 0; 
            border: 1px solid #02b1d9;
            border-radius: 5px;
            transition: background-color 0.3s;
            display: inline-block; 
            width: 150px;
            text-align: center; 
            cursor: pointer;
            background-color: transparent;
        }

        .show-password-btn:hover {
            background-color: #02b1d9;
            color: #fff;
        }

        form {
            margin-top: 20px;
        }

        input[type="submit"] {
            color: #02b1d9;
            text-decoration: none;
            margin: 0 10px;
            padding: 5px 0; 
            border: 1px solid #02b1d9;
            border-radius: 5px;
            transition: background-color 0.3s;
            display: inline-block;
            width: 150px; 
            text-align: center;
            cursor: pointer;
            background-color: transparent;
        }

        input[type="submit"]:hover {
            background-color: #02b1d9;
            color: #fff;
        }

        form {
            margin-top: 20px;
        }

        input[type="text"] {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
        }

        input[type="submit"].delete-user-btn {
            background-color: #ff0000;
            color: #fff;
            text-decoration: none;
            margin: 0 10px;
            padding: 5px 0; 
            border: 1px solid #ff0000;
            border-radius: 5px;
            transition: background-color 0.3s;
            display: inline-block; 
            width: 150px; 
            text-align: center;
            cursor: pointer;
        }

        input[type="submit"].delete-user-btn:hover {
            background-color: #ff0000;
            color: #fff;
        }
        input[type="submit"].adm-btn {
            background-color: #4CAF50;
            color: #fff;
            text-decoration: none;
            margin: 0 10px;
            padding: 5px 0; 
            border: 1px solid #4CAF50;
            border-radius: 5px;
            transition: background-color 0.3s;
            display: inline-block; 
            width: 250px; 
            text-align: center; 
            cursor: pointer;
        }

    </style>
</head>
<body>
    <div class="container">
        <h1><?php echo $user['name']; ?></h1>

        <form action="vacation.php?user_id=<?php echo $userId; ?>" method="post">
            <label for="total_hours">Całkowita liczba godzin urlopu:</label>
            <input type="number" id="total_hours" name="total_hours" value="<?php echo $totalHours; ?>" required><br>
            <br>
            <label for="used_hours">Wykorzystane godziny urlopu:</label>
            <input type="number" id="used_hours" name="used_hours" value="<?php echo $usedHours; ?>" required><br>
<br>
            <input type="submit" value="Zapisz">
        </form>
<br>
<br>
<p>Całkowita liczba godzin urlopu: <?php echo $totalHours; ?> (<?php echo $totalDaysFormatted; ?> dni)</p>
<br>
    <p>Wykorzystane godziny urlopu: <?php echo $usedHours; ?> (<?php echo $usedDaysFormatted; ?> dni)</p>
    <br>
    <p>Pozostałe godziny urlopu: <?php echo $remainingHours; ?> (<?php echo $remainingDaysFormatted; ?> dni)</p>
</div>
    <?php include 'footer.php'; ?>
</body>
</html>
