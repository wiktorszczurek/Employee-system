<?php 
require 'db_config.php'; 
session_start();
include 'layout.php';

if (!isset($_SESSION['rfid_login']) || $_SESSION['rfid_login'] !== true) {

    header('Location: user_dashboard.php');
    exit;
}



if (!isset($_SESSION['user_id'])) {
    die("Nie masz dostępu do tej strony. Proszę się zalogować.");
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

$stmt = $conn->prepare("SELECT id, start_time FROM working_hours WHERE user_id = ? AND is_active = TRUE ORDER BY id DESC LIMIT 1");
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

$shiftInProgress = false;
if ($result->num_rows > 0) {
    $activeShiftData = $result->fetch_assoc();
    $_SESSION['start_time_id'] = $activeShiftData['id'];
    $start_time_ISO = (new DateTime($activeShiftData['start_time']))->format(DateTime::ATOM);
    $shiftInProgress = true;
}

function roundToNearestHalfHour($time) {
    $timestamp = strtotime($time);
    $minutes = date('i', $timestamp);

    if ($minutes < 15) {

        $rounded = strtotime(date('Y-m-d H:00:00', $timestamp));
    } elseif ($minutes < 45) {

        $rounded = strtotime(date('Y-m-d H:30:00', $timestamp));
    } else {

        $rounded = strtotime($time . '+1 hour');
        $rounded = strtotime(date('Y-m-d H:00:00', $rounded));
    }

    return date('Y-m-d H:i:s', $rounded);
}


if (isset($_POST['start_shift'])) {
    $currentTime = date('Y-m-d H:i:s'); 
    $roundedTime = roundToNearestHalfHour($currentTime);


    $stmt = $conn->prepare("INSERT INTO working_hours (user_id, date, start_time, is_active) VALUES (?, CURDATE(), ?, TRUE)");
    $stmt->bind_param('is', $userId, $roundedTime);
    $stmt->execute();
    $workingHoursId = $conn->insert_id;


    $stmt = $conn->prepare("INSERT INTO exact_start_times (working_hours_id, exact_start_time) VALUES (?, ?)");
    $stmt->bind_param('is', $workingHoursId, $currentTime);
    $stmt->execute();
    $_SESSION['start_time_id'] = $conn->insert_id;

    $stmt->close();
    $shiftInProgress = true;

    echo "<script>window.location.href = 'hours_success.php';</script>";
}

if (isset($_POST['end_shift'])) {
  $start_time_id = $_SESSION['start_time_id'];

  $stmt = $conn->prepare("SELECT start_time FROM working_hours WHERE id = ?");
  $stmt->bind_param('i', $start_time_id);
  $stmt->execute();
  $result = $stmt->get_result();
  $stmt->close();

  if ($result->num_rows > 0) {
      $start_data = $result->fetch_assoc();
      $start_time = new DateTime($start_data['start_time']);
      $end_time = new DateTime(); 


      $interval = $start_time->diff($end_time);
      $hours = $interval->h + ($interval->days * 24); 
      $minutes = $interval->i;


      if ($minutes > 15 && $minutes <= 45) {
          $hours += 0.5;
      } else if ($minutes > 45) {
          $hours += 1;
      }


      $stmt = $conn->prepare("UPDATE working_hours SET end_time = ?, hours = ?, is_active = FALSE WHERE id = ?");
      $stmt->bind_param('sdi', $end_time->format('Y-m-d H:i:s'), $hours, $start_time_id);
      $stmt->execute();
      $stmt->close();
  }

  unset($_SESSION['start_time_id']);
  $shiftInProgress = false;

  echo "<script>window.location.href = 'hours_success.php';</script>";
}

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

$today = date("Y-m-d");
$userId = $_SESSION['user_id'];


$todayShiftSql = "SELECT start_time FROM schedules WHERE user_id='$userId' AND work_date='$today'";
$todayShiftResult = $conn->query($todayShiftSql);

$todayShiftMessage = ""; 

if ($todayShiftResult->num_rows > 0) {
    $todayShiftRow = $todayShiftResult->fetch_assoc();
    $todayShiftStartTime = substr($todayShiftRow['start_time'], 0, 5); 


    $shiftStartDateTime = new DateTime($todayShiftStartTime);
    $shiftStartDateTime->modify('-15 minutes');
    $earlyStartTime = $shiftStartDateTime->format('H:i');

    $todayShiftMessage = "<div style='margin: 10px;'>" .
        "Twoja zmiana dzisiaj zaczyna się o: " . $todayShiftStartTime .
        ". <br>Możesz rozpocząć zmianę najwcześniej o: " . $earlyStartTime .
        "</div>";
} else {
    $todayShiftMessage = "<div style='margin: 10px;'>Brak zaplanowanej zmiany na dzień dzisiejszy.</div>";
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zmiany Pracownicze</title>
    
    <style>
                .container_hours {
            background-color: #f0f8ff;
            border: 1px solid #d1e0e0;
            border-radius: 5px;
            padding: 10px;
            width: 600px;
            font-size: 1.1em;
                    margin: 0 auto;
            margin-top: 150px;
            
        }
.container_hours h1 {
        color: #333;
text-align: center;
margin-bottom: 20px;
font-size: 1.2em;
border-bottom: 2px solid #02b1d9;
padding-bottom: 10px;
letter-spacing: 1px; 
}

.shift input[type="submit"] {
  display: block;
  width: 250px; 
  padding: 12px;
  font-size: 20px; 
  font-weight: bold; 
  color: #fff;
  background-color: #5cb85c; 
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

.shift input[type="submit"]:before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(255, 255, 255, 0.2);
  z-index: 1;
  transition: all 0.3s ease;
  opacity: 0; 
  transform: scale(0.1, 0.1);
}

.shift input[type="submit"]:hover:before {
  opacity: 1;
  transform: scale(1, 1); 
}

.shift input[type="submit"]:active {
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.2); 
  transform: translateY(2px); 
}

.shift input[type="submit"][name="end_shift"] {
  background-color: #d9534f; 
}

.shift input[type="submit"][name="end_shift"]:before {
  background-color: rgba(255, 255, 255, 0.2);
}

.shift-info {
    font-size: 1.1em; 
    color: #333; 
    font-family: 'Arial', sans-serif; 
    text-align: center; 
    margin-top: 20px;
  }

  #shiftDuration {
    font-weight: bold; 
    font-size: 1.3em; 
    color: #02b1d9; 
    padding: 5px 10px; 
    background-color: #e6f4f1; 
    border-radius: 5px; 
    display: inline-block; 
    box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.2); 
    margin-left: 5px; 
  }
  .modal {
            display: none; 
            justify-content: center;
            text-align: center;
            z-index: 1; 
            left: 0;
            


        }

   
        .modal-content {

            margin: 5% auto;
            padding: 20px;

            width: 90%; 
        }

        .modal-button {

    margin: 10px; 
    font-size: 25px; 
    color: white; 
    border: none; 
    border-radius: 5px; 
    cursor: pointer; 
    transition: background-color 0.3s;
    width: 120px;
    height: 80px;
}


.modal-button-yes {
    background-color: #4CAF50; 
}



.modal-button-no {
    background-color: #f44336; 
}


.modal p{
  font-size: 20px;
}
.modal strong {
  font-weight: bold;

}
.content_main{

    background-color: #f0f8ff; 
            border: 1px solid #d1e0e0;
            border-radius: 5px;
            padding: 10px;
 
            font-size: 1.1em;
}
@media screen and (max-width: 768px) {
    .content_main {
        margin-top: 50%;

    }
    .container_hours {
            width: 90%;
            
        }
        .modal-button {
    padding: 10px 20px; 
    margin: 5px; 
    font-size: 18x; 
    color: white; 
    border: none; 
    border-radius: 5px; 
    cursor: pointer; 
    transition: background-color 0.3s; 
}
    }
    
.content_main h1 {
    color: #333;
    text-align: center;
    margin-bottom: 20px;
    font-size: 1.5em;
    border-bottom: 2px solid #02b1d9;
    padding-bottom: 10px;
    letter-spacing: 1px; 
}

.modall {
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

.modall-content {
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
.modall-content a {
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


.modall-content > p {
  padding-top: 20px;
}
.modall-content p {
    color: white;
}

.close:hover,
.close:focus {

  text-decoration: none;
  cursor: pointer;
  color: #d52a0f;
  cursor: pointer;
}

        </style>

</head>
<body>
<div class="container_hours">
<?php if ($latestMessageDate > $lastMessageCheck): ?>
    <div id="myModal" class="modall">
        <div class="modall-content">
            <span class="close">&times;</span>
            <p>Nowa wiadomość od <span><?php echo htmlspecialchars($senderName);?></span>. <br><br><a href='messages.php'> Kliknij tutaj, aby przeczytać</a>.</p>
        </div>
    </div>
<?php endif; ?>
<h1>Witaj, <?php echo $userName; ?>!</h1>
<div id="startShiftModal" class="modal">



    <div class="modal-content">
    <p style="color: green;">Rozpoczęcie zmiany</p>
    <br>
    <?php
if ($todayShiftMessage) {
    echo "<h3>" . $todayShiftMessage . "</h3><br>";
}


?>
        <br>
        <p>Czy chcesz <strong style="color:green;">rozpocząć</strong> zmianę?</p>
        <br>
        <button class="modal-button modal-button-yes" onclick="document.getElementById('startShiftForm').submit();">Tak</button>
        <button class="modal-button modal-button-no" onclick="window.location.href='user_dashboard.php'">Nie</button>

    </div>
</div>

<div id="endShiftModal" class="modal">
    <div class="modal-content">
        <p style="color: red;">Zmiana w toku!</p>
        <br>
        <p>Czy chcesz <strong style="color:red;">zakończyć</strong> zmianę?</p>
        <br>
        <button class="modal-button modal-button-yes" onclick="document.getElementById('endShiftForm').submit();">Tak</button>
        <button class="modal-button modal-button-no" onclick="window.location.href='user_dashboard.php'">Nie</button>
        <?php if ($shiftInProgress): ?>

    <?php endif; ?>
    </div>

</div>

<div class="shift">
 
    <form id="startShiftForm" method="post" style="display:none;">
        <input type="hidden" name="start_shift" />
    </form>
    <form id="endShiftForm" method="post" style="display:none;">
        <input type="hidden" name="end_shift" />
    </form>



</div>
        </div>
        <?php include 'footer.php';?>
<script>
    function showModal(modalId) {
        document.getElementById(modalId).style.display = "block";
    }

    function closeModal(modalId) {
        document.getElementById(modalId).style.display = "none";
    }

    window.onload = function() {
        <?php if ($shiftInProgress): ?>
        showModal('endShiftModal');
        <?php else: ?>
        showModal('startShiftModal');
        <?php endif; ?>
    };


</script>





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
</body>
</html>

