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





?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
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
    </style>
</head>
<body>
<div class="container">
<h1><?php echo $userName; ?></h1>


<a href="user_hours.php?user_id=<?php echo $userId; ?>">Godziny użytkownika</a>
<br>
<br>
<a href="schedule.php?user_id=<?php echo $userId; ?>">Grafik użytkownika</a>
<br>
<br>
<a href="my_answers.php?user_id=<?php echo $userId; ?>">Checklist użytkownika</a>
<br>
<br>
<a href="vacation.php?user_id=<?php echo $userId; ?>">Urlopy użytkownika</a>
<br>
<br>
<a href="user_profil.php?user_id=<?php echo $userId; ?>">Zarządzanie użytkownikiem</a>

</div>

<?php include 'footer.php'; ?>
</body>
</html>