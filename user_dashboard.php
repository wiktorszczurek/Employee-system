<?php 

require 'db_config.php'; 
session_start();
include 'layout.php';


if (!isset($_SESSION['user_id'])) {
    echo '<script type="text/javascript">window.location = "login.php";</script>';
    exit;
}


$userId = $_SESSION['user_id'];

$userSql = "SELECT name FROM users WHERE id='$userId'";
$userResult = $conn->query($userSql);

if ($userResult->num_rows > 0) {
    $userRow = $userResult->fetch_assoc();
    $userName = $userRow['name'];
} else {
    $userName = "Użytkownik";
}
$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['hours'])) {
    $userId = $_SESSION['user_id'];
    $date = date("Y-m-d");
    $hours = $_POST['hours'];

    $checkSql = "SELECT id FROM working_hours WHERE user_id='$userId' AND date='$date'";
    if ($conn->query($checkSql)->num_rows == 0) {
        $sql = "INSERT INTO working_hours (user_id, date, hours) VALUES ('$userId', '$date', '$hours')";
        if ($conn->query($sql) === TRUE) {
            $message = "Godziny pracy dodane pomyślnie.";
        } else {
            $message = "Wystąpił błąd: " . $conn->error;
        }
    } else {
        $message = "Dodałeś już godziny pracy dla dzisiejszej daty.";
    }
}



$firstDayOfMonth = date("Y-m-01");
$lastDayOfMonth = date("Y-m-t");

$totalHoursSql = "SELECT SUM(hours) as total_hours FROM working_hours WHERE user_id='" . $_SESSION['user_id'] . "' AND date BETWEEN '$firstDayOfMonth' AND '$lastDayOfMonth'";
$result = $conn->query($totalHoursSql);
$totalHours = $result->fetch_assoc()['total_hours'];

$result = $conn->query($totalHoursSql);
$totalHours = $result->fetch_assoc()['total_hours'];


setlocale(LC_TIME, 'pl_PL.UTF-8');

$today = date("Y-m-d");
$userId = $_SESSION['user_id'];

$nextWorkDaySql = "SELECT work_date, start_time FROM schedules WHERE user_id='$userId' AND work_date >= '$today' ORDER BY work_date ASC LIMIT 3";

$nextWorkDayResult = $conn->query($nextWorkDaySql);
$workDays = [];
if ($nextWorkDayResult->num_rows > 0) {
    while ($row = $nextWorkDayResult->fetch_assoc()) {
        $workDays[] = [
            'work_date' => strftime('%A, %d-%m-%Y', strtotime($row['work_date'])),
            'start_time' => substr($row['start_time'], 0, 5)
        ];
    }
} else {
    $workDays[] = ['work_date' => "Brak zaplanowanych dni pracy", 'start_time' => ""];
}

$todayShiftSql = "SELECT start_time FROM schedules WHERE user_id='$userId' AND work_date='$today'";
$todayShiftResult = $conn->query($todayShiftSql);

$todayShiftMessage = "";  
if ($todayShiftResult->num_rows > 0) {
    $todayShiftRow = $todayShiftResult->fetch_assoc();
    $todayShiftStartTime = substr($todayShiftRow['start_time'], 0, 5);  
    $todayShiftMessage = "Twoja zmiana dzisiaj zaczyna się o: " . $todayShiftStartTime;
}


$sql = "SELECT * FROM notes ORDER BY created_at DESC";
$result = $conn->query($sql);
$notes = $result->fetch_all(MYSQLI_ASSOC);

$lastCheckSql = "SELECT last_message_check FROM users WHERE id='$userId'";
$lastCheckResult = $conn->query($lastCheckSql);
$lastMessageCheck = $lastCheckResult->fetch_assoc()['last_message_check'];


$newMessageSql = "SELECT m.sent_date as latest_message, u.name as sender_name 
                  FROM messages m
                  JOIN users u ON m.sender_id = u.id
                  WHERE m.category IN (SELECT category FROM users WHERE id='$userId') 
                  ORDER BY m.sent_date DESC LIMIT 1";
$newMessageResult = $conn->query($newMessageSql);

if ($newMessageResult->num_rows > 0) {
    $newMessageRow = $newMessageResult->fetch_assoc();
    $latestMessageDate = $newMessageRow['latest_message'];
    $senderName = $newMessageRow['sender_name'];


}

$tipQuery = "SELECT SUM(tip) AS total_tips FROM working_hours WHERE user_id = '$userId' AND date BETWEEN '$firstDayOfMonth' AND '$lastDayOfMonth'";
$tipResult = $conn->query($tipQuery);

if ($tipRow = $tipResult->fetch_assoc()) {
    $totalTips = $tipRow['total_tips'];
} else {
    $totalTips = 0; 
}


?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>

                .note {
            border: 1px solid #ccc;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 3px;
            max-width: 500px;
        }

        .info {
    border: 2px solid #02b1d9;
    background-color: #e6f7ff; 
    border-radius: 5px;

}

.important {
    border: 2px solid red;
    background-color: #ffeeee; 
    border-radius: 5px;
}

input[type="number"] {
    width: 100%;
    padding: 10px;
    margin-bottom: 10px;
    border: 2px solid black;
    border-radius: 3px;
    font-size: 16px;
}


#hours {
    max-width: 80px;
}


input[type="number"]:invalid {
    border-color: #ff3333;
}


input[type="number"]::placeholder {
    color: #999;
}


input[type="number"]:hover {
    border-color: #666;
}
.custom-form {
    background-color: #f7f7f7;
    padding: 20px;
    border-radius: 5px;
}
.work-day-info {
            background-color: #f0f8ff; 
            border: 1px solid #d1e0e0;
            border-radius: 5px;
            padding: 10px;
            margin: 10px 0;
            font-size: 1.1em;
        }

        .work-day-info h3 {
            color: #333;
            font-weight: bold;
        }
        .work-day-info h1 {

    color: #333;
    text-align: center;
    margin-bottom: 20px;
    font-size: 1.1em;
    border-bottom: 2px solid #02b1d9;
    padding-bottom: 10px;
    letter-spacing: 1px; 
}



        .work-day-info p {
            margin: 5px 0;
            color: #555;
        }
        .signature-style {
        font-family: 'Your Font Name', sans-serif;  

        display: inline-block;  
        margin-top: 10px;
        font-style: italic;  
    }
    .modal {
  display: none;
  position: fixed;
  z-index: 1;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  overflow: auto;
  background-color: rgba(0,0,0,0.4);
}

.modal-content {
  position: relative;
  background-color: #02b1d9;
  margin: auto;
  padding: 20px; 
  border: 1px solid #888;
  width: 100%;
  border-radius: 5px;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  margin: 20px auto;
  padding-bottom: 30px;
  box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2);
}
.modal-content a {
    color: white;
}

.close {
  position: absolute;
  right: 15px;
  top: 5px; 

  font-size: 40px;
  font-weight: bold;
  color: white;
}

.modal-content > p {
  padding-top: 20px;
}
.modal-content p {
    color: white;
}

.close:hover,
.close:focus {

  text-decoration: none;
  cursor: pointer;
  color: #d52a0f;
  cursor: pointer;
}

 a{
    color:#02b1d9;
    font-size: 1.5rem;
}

    
</style>
</head>
<body>
<main>
<div class="work-day-info">
<h1>Witaj, <?php echo $userName; ?>!</h1>
<br>
<h3>Ilość godzin w tym miesiącu:<br><span style="color: green;"> <?php echo $totalHours; ?></span></h3>


<?php if ($latestMessageDate > $lastMessageCheck): ?>
    <div id="myModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <p>Nowa wiadomość od <span><?php echo htmlspecialchars($senderName);?></span>. <br><br><a href='messages.php'> Kliknij tutaj, aby przeczytać</a>.</p>
        </div>
    </div>
<?php endif; ?>

<br>
<?php
if ($todayShiftMessage) {
    echo "<h3>" . $todayShiftMessage . "</h3><br>";
}


?>

            <h3>Najbliższe dni pracy:</h3>
            <?php foreach ($workDays as $day): ?>
                <p><?php echo $day['work_date'] . " " . $day['start_time']; ?></p>
            <?php endforeach; ?>

        </div>
        <br>
            <a href="przewodnik.php">Przewodnik po aplikacji</a>
    <?php 
    if ($message != "") {
        echo "<p>" . $message . "</p>";
    }
    ?>
        <div class="note-cont">
            <br>
            <h2>Informacje</h2>
            <br>
            <?php foreach($notes as $note): ?>
    <div class="note <?php echo $note['category']; ?>">
        <?php if ($note['category'] == 'important'): ?>
            <h3 style="color: red;">WAŻNE!</h3>
            <br>
        <?php endif; ?>
        <h3><?php echo htmlspecialchars($note['title']); ?></h3>
        <br>
        <p><?php echo nl2br(htmlspecialchars($note['content'])); ?></p>
        <?php if (!empty($note['signature'])): ?>
    <p class="signature-style"><?php echo htmlspecialchars($note['signature']); ?></p>
<?php endif; ?>
        

        

    </div>
<?php endforeach; ?>
    </div>
</main>

<script>
    document.addEventListener("DOMContentLoaded", function() {
    var modal = document.getElementById("myModal");
    var span = document.getElementsByClassName("close")[0];

    <?php if ($latestMessageDate > $lastMessageCheck): ?>
        modal.style.display = "block";
    <?php endif; ?>

    span.onclick = function() {
        modal.style.display = "none";
    }

    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
});

</script>
<?php include 'footer.php'; ?>
</body>
</html>