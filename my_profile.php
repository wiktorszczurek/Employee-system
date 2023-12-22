<?php 
require 'db_config.php'; 
session_start();
include 'layout.php';


if (!isset($_SESSION['user_id'])) {
    echo '<script type="text/javascript">window.location = "login.php";</script>';
    exit;
}

$userId = $_SESSION['user_id'];



$userSql = "SELECT name, category, created_at FROM users WHERE id='$userId'";
$userResult = $conn->query($userSql);

if ($userResult->num_rows > 0) {
    $userRow = $userResult->fetch_assoc();
    $userName = $userRow['name'];
    $userCategory = $userRow['category'];
    $userCreatedAt = date('Y-m-d', strtotime($userRow['created_at'])); 
} else {
    echo '<script type="text/javascript">alert("Nie znaleziono profilu użytkownika."); window.location = "dashboard.php";</script>';
    exit;
}

$vacationSql = "SELECT total_hours, used_hours FROM vacation WHERE user_id='$userId'";
$vacationResult = $conn->query($vacationSql);

if ($vacationResult->num_rows > 0) {
    $vacationRow = $vacationResult->fetch_assoc();
    $totalHours = $vacationRow['total_hours'];
    $usedHours = $vacationRow['used_hours'];
    $remainingHours = $totalHours - $usedHours;
} else {
    $totalHours = $usedHours = $remainingHours = 0;
}


function formatDays($hours) {
    $hoursPerDay = 8;
    $days = $hours / $hoursPerDay;
    return ($days == intval($days)) ? intval($days) : number_format($days, 1);
}
?>


<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mój Profil</title>
    <style>


    label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
        font-size: 0.9em
    }

    input[type="password"],
    input[type="submit"] {
        width: 100%;
        padding: 10px;
        margin-bottom: 20px;
        border: 1px solid #ddd;
        border-radius: 4px;
        box-sizing: border-box;
    }

    input[type="submit"] {
        background-color: #4CAF50;
        color: white;
        border: none;
        cursor: pointer;
    }



    input[type="password"]:focus {
        border-color: #4CAF50;
        outline: none;
    }
    .date-form h2{

color: #333;
text-align: center;

font-size: 1.1em;
border-bottom: 1px solid #02b1d9;
padding-bottom: 10px;
letter-spacing: 1px; 
}

.date-form h3 {
    color: #333;
    text-align: center;
    margin-bottom: 10px;
    font-size: 1.3em;
    border-bottom: 1px solid #02b1d9;
    padding-bottom: 5px;
}

.date-form p {
    font-size: 1em;
    line-height: 1.5;
    color: #555;
    margin: 5px 0;
}

.date-form p span {
    font-weight: bold;
    color: #02b1d9;
}
.date-form a {
    color:#02b1d9;
}

</style>
</head>
<body>

        <div class="date-form">
        <h1>Mój Profil</h1>


        <p><?php echo htmlspecialchars($userName); ?></p>
        <p><?php echo htmlspecialchars($userCategory); ?></p>
        <p>Data utworzenia konta: <?php echo $userCreatedAt; ?></p>
        <br>
        <a href="przewodnik.php">Przewodnik po aplikacji</a>

        <br>
    <br>

    <h3>Urlopy</h3>
    <p>Całkowita liczba godzin urlopu: <span><?php echo $totalHours; ?>h</span> (<?php echo formatDays($totalHours); ?> dni)</p>
    <p>Wykorzystane godziny urlopu: <span><?php echo $usedHours; ?>h</span> (<?php echo formatDays($usedHours); ?> dni)</p>
    <p>Pozostałe godziny urlopu: <span><?php echo $remainingHours; ?>h</span> (<?php echo formatDays($remainingHours); ?> dni)</p>
</div>
</div>

    <?php include 'footer.php'; ?>
</body>
</html>
