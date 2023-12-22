<?php 
require 'db_config.php'; 
session_start();

if (!isset($_SESSION['rfid_login']) || $_SESSION['rfid_login'] !== true) {
    header('Location: user_dashboard.php');
    exit;
}

include 'layout.php';

if (!isset($_SESSION['user_id'])) {
    die("Nie masz dostępu do tej strony. Proszę się zalogować.");
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

       .container_hours #countdown {
            font-size: 30px;
            color: red;
        }

       .container_hours p {
            font-size: 24px;
            margin: 10px 0;
        }

       .container_hours .button-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
        }

      .container_hours  .button-container a {
            flex-basis: calc(50% - 20px); 
            text-decoration: none;

            color: #02b1d9;
            margin: 10px;
            padding: 10px;
            border-radius: 5px;
            font-size: 18px;
            border: 1px solid #02b1d9;
            transition: background-color 0.3s ease;
        }
        .green-text {
            color: green;
        }
        .container_hours  .button-container a:hover {
            background-color: #02b1d9;
            color: #fff;
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
    </style>

    <script>

        function autoLogout() {
            var countdown = 10; 
            var countdownElement = document.getElementById('countdown');

            function updateCountdown() {
                countdown--;
                countdownElement.innerText = countdown;

                if (countdown <= 0) {
                    window.location.href = 'logout.php'; 
                }
            }

            setInterval(updateCountdown, 1000); 
        }


        window.onload = autoLogout;
    </script>
</head>
<body>
    <div class="container_hours">

        
        <?php

        $stmt = $conn->prepare("SELECT is_active, start_time FROM working_hours WHERE user_id = ? ORDER BY id DESC LIMIT 1");
        $stmt->bind_param('i', $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {
            $activeShiftData = $result->fetch_assoc();
            if ($activeShiftData['is_active'] == 1) {
                echo '<p>Zmiana rozpoczęta! <br> Data: <span class="green-text">' .  $activeShiftData['start_time'] . '</span></p>';
            } else {

                $stmt = $conn->prepare("SELECT hours FROM working_hours WHERE user_id = ? AND is_active = FALSE ORDER BY id DESC LIMIT 1");
                $stmt->bind_param('i', $_SESSION['user_id']);
                $stmt->execute();
                $result = $stmt->get_result();
                $stmt->close();
        
                if ($result->num_rows > 0) {
                    $shiftData = $result->fetch_assoc();
                    $hoursWorked = $shiftData['hours'];
                    echo '<p>Zmiana zakończona!</p>';
                    echo '<p>Przepracowane godziny: <span class="green-text"> ' . number_format($hoursWorked, 2) . '</span></p>';
                }
            }
        } else {
            echo '<p>Brak aktywnej zmiany.</p>';
        }
        ?>
        <br>

        <p>Automatyczne wylogowanie za<br> <span id="countdown">10</span> sekund.</p>

        <div class="button-container">
            <a href="user_dashboard.php">Panel</a>
            <a href="schedule.php">Mój grafik</a>
            <a href="full_schedule.php">Pełny grafik</a>
            <a href="view_hours.php">Godziny</a>
            <a href="my_surveys.php">Checklist</a>
            <a href="messages.php">Wiadomości</a>
            <a href="my_profile.php">Mój profil</a>
            <a href="logout.php">Wyloguj się</a>
        </div>
    </div>

    <?php include 'footer.php';?>
</body>
</html>
