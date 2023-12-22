<?php
require 'db_config.php';
session_start();
include 'layout.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    echo '<script type="text/javascript">window.location = "login.php";</script>';
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    foreach ($_POST['vacation'] as $userId => $vacationData) {
        $totalHours = floatval($vacationData['total_hours']);
        $usedHours = floatval($vacationData['used_hours']);

        $checkSql = "SELECT * FROM vacation WHERE user_id='$userId'";
        $checkResult = $conn->query($checkSql);

        if ($checkResult->num_rows > 0) {
            $updateSql = "UPDATE vacation SET total_hours='$totalHours', used_hours='$usedHours' WHERE user_id='$userId'";
        } else {
            $updateSql = "INSERT INTO vacation (user_id, total_hours, used_hours) VALUES ('$userId', '$totalHours', '$usedHours')";
        }

        $conn->query($updateSql);
    }

    echo "Dane urlopu zaktualizowane dla wszystkich użytkowników.";
}

$usersQuery = "SELECT users.id, users.name, users.category, vacation.total_hours, vacation.used_hours FROM users LEFT JOIN vacation ON users.id = vacation.user_id WHERE users.category != 'Admin'";
$usersResult = $conn->query($usersQuery);


function formatDays($hours, $hoursPerDay = 8) {
    $days = $hours / $hoursPerDay;
    return ($days == intval($days)) ? intval($days) : number_format($days, 1);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Zarządzanie urlopami użytkowników</title>
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
input[type="number"] {
    width: 60px; 
    padding: 8px; 
    margin: 4px 0;
    box-sizing: border-box; 
}




    </style>
</head>
<body>
<div class="date-form">
    <h1>Urlopy</h1>
</div>
<div class="container">
        <form action="users_vacation.php" method="post">
            <table>
                <tr>
                    <th>Użytkownik</th>
                    <th>Całkowita liczba godzin urlopu</th>
                    <th>Wykorzystane godziny urlopu</th>
                    <th>Pozostałe godziny urlopu</th>

                </tr>
                <?php while($row = $usersResult->fetch_assoc()): 
                    $remainingHours = $row['total_hours'] - $row['used_hours'];
                ?>
                    <tr>
                        <td><?php echo $row['name']; ?></td>
                        <td>
                            <input type="number" name="vacation[<?php echo $row['id']; ?>][total_hours]" value="<?php echo $row['total_hours']; ?>">
                        </td>
                        <td>
                            <input type="number" name="vacation[<?php echo $row['id']; ?>][used_hours]" value="<?php echo $row['used_hours']; ?>">
                        </td>
                        <td>
                            <?php echo $remainingHours; ?>
                        </td>
   
                    </tr>
                <?php endwhile; ?>
            </table>
                <div class="save">
    <input type="submit" value="Zapisz">
            </div>
        </form>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>
