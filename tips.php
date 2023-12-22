<?php 
require 'db_config.php';
session_start();
include 'layout.php';
mb_internal_encoding("UTF-8");
setlocale(LC_TIME, 'pl_PL.utf8');


if ((!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) && ($_SESSION['user_id'] != 157)) {
    echo '<script type="text/javascript">alert("Nie masz dostępu do tej strony."); window.location = "user_dashboard.php";</script>';
    exit;
}


function recalculateTips($conn, $date) {
    $tip_query = "SELECT total_amount FROM daily_tips WHERE date = '$date'";
    $tip_result = $conn->query($tip_query);

    if ($tip_row = $tip_result->fetch_assoc()) {
        $total_amount = $tip_row['total_amount'];


        $reset_tips_query = "UPDATE working_hours SET tip = 0 WHERE date = '$date'";
        $conn->query($reset_tips_query);


        $categories = ['Kelner', 'Kuchnia Restauracyjna'];

        foreach ($categories as $category) {

            $qualified_count_query = "SELECT COUNT(DISTINCT u.id) as count FROM users u 
                                      JOIN working_hours wh ON u.id = wh.user_id
                                      WHERE u.category = '$category' AND wh.date = '$date' AND wh.hours >= 5";
            $qualified_count_result = $conn->query($qualified_count_query);
            $qualified_count_row = $qualified_count_result->fetch_assoc();
            $qualified_count = $qualified_count_row['count'];

            if ($qualified_count > 0) {
                $other_category = ($category == 'Kelner') ? 'Kuchnia Restauracyjna' : 'Kelner';
                $other_category_query = "SELECT COUNT(DISTINCT u.id) as count FROM users u
                                         JOIN working_hours wh ON u.id = wh.user_id
                                         WHERE u.category = '$other_category' AND wh.date = '$date' AND wh.hours >= 5";
                $other_category_result = $conn->query($other_category_query);
                $other_category_row = $other_category_result->fetch_assoc();
                $other_category_count = $other_category_row['count'];

                if ($other_category_count > 0) {
                    $tip_per_user = ($total_amount * ($category == 'Kelner' ? 0.65 : 0.35)) / $qualified_count;
                } else {
                    $tip_per_user = $total_amount / $qualified_count;
                }

                $workers_query = "SELECT DISTINCT u.id FROM users u
                                  JOIN working_hours wh ON u.id = wh.user_id
                                  WHERE u.category = '$category' AND wh.date = '$date' AND wh.hours >= 5";
                $workers_result = $conn->query($workers_query);

                while ($worker_row = $workers_result->fetch_assoc()) {
                    $user_id = $worker_row['id'];

                    $update_query = "UPDATE working_hours SET tip = $tip_per_user
                                     WHERE user_id = $user_id AND date = '$date'
                                     LIMIT 1"; 
                    $conn->query($update_query);
                }
            }
        }
    }
}



if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['date'])) {
    $date = $_POST['date'];
    $reset_tips_query = "UPDATE working_hours SET tip = 0 WHERE date = '$date'";
    $reset = isset($_POST['reset']);

    if ($reset) {
        recalculateTips($conn, $date);
    } else {
        $total_amount = $_POST['total_amount'] ?? 0;
        $insert_tip_query = "INSERT INTO daily_tips (date, total_amount) VALUES ('$date', $total_amount) ON DUPLICATE KEY UPDATE total_amount = VALUES(total_amount)";
        $conn->query($insert_tip_query);
        recalculateTips($conn, $date);
    }
}

$selected_month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');
$start_date = $selected_month . '-01';
$end_date = date('Y-m-t', strtotime($start_date));
$total_tips_query = "SELECT SUM(total_amount) AS total_tips FROM daily_tips WHERE date BETWEEN '$start_date' AND '$end_date'";
$total_tips_result = $conn->query($total_tips_query);
$total_tips_row = $total_tips_result->fetch_assoc();
$total_tips = $total_tips_row['total_tips'] ?? 0;
function polishMonthName($date) {
    $month = strftime('%m', strtotime($date));
    $months = [
        '01' => 'styczniu',
        '02' => 'lutym',
        '03' => 'marcu',
        '04' => 'kwietniu',
        '05' => 'maju',
        '06' => 'czerwcu',
        '07' => 'lipcu',
        '08' => 'sierpniu',
        '09' => 'wrześniu',
        '10' => 'październiku',
        '11' => 'listopadzie',
        '12' => 'grudniu'
    ];
    return $months[$month];
}



$query = "SELECT wh.date, u.name, u.category, wh.start_time, wh.end_time, wh.hours, wh.tip 
          FROM working_hours wh 
          JOIN users u ON wh.user_id = u.id 
          WHERE u.category IN ('Kelner', 'Kuchnia Restauracyjna')
          AND wh.date BETWEEN '$start_date' AND '$end_date'
          ORDER BY wh.date DESC, wh.start_time ASC";

function getPolishDayOfWeek($date) {
    setlocale(LC_TIME, 'pl_PL.UTF-8');
    return strftime("%A", strtotime($date));
}

$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<style>
    input[type=submit] {
        background-color: #4CAF50; 
        color: white;
        padding: 5px 10px;
        margin: 8px 0;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }



    .reset-button {
        background-color: #f44336; 
    }
</style>

<body>
<div class="date-form">
    <h1>Napiwki</h1>
    <?php
echo '<form method="get" action="" class="custom-form">
        <label for="month">Wybierz miesiąc:</label>
        <input type="month" id="month" name="month" value="'.(isset($_GET['month']) ? $_GET['month'] : date('Y-m')).'">
        <input type="submit" value="Wybierz">
      </form>';
      



      ?>
      </div>


<?php


$usersTipsQuery = "
    SELECT u.name, u.category, SUM(wh.tip) as total_tips, COUNT(DISTINCT wh.date) as tip_days
    FROM users u
    JOIN working_hours wh ON u.id = wh.user_id
    WHERE u.category IN ('Kelner', 'Kuchnia Restauracyjna') 
    AND wh.date BETWEEN '$start_date' AND '$end_date'
    GROUP BY u.id
    ORDER BY SUM(wh.tip) DESC, u.category, u.name";



$usersTipsResult = $conn->query($usersTipsQuery);

if ($usersTipsResult->num_rows > 0) {
    echo "<table border='1'>
            <tr>
                <th>Imię i nazwisko</th>
                <th>Kategoria</th>
                <th>Suma napiwków</th>
                <th>Liczba dni</th>
            </tr>";
    while ($row = $usersTipsResult->fetch_assoc()) {
        echo "<tr>
                <td>" . $row["name"] . "</td>
                <td>" . $row["category"] . "</td>
                <td>" . $row["total_tips"] . " zł</td>
                <td>" . $row["tip_days"] . "</td>
              </tr>";
    }
    echo "</table><br>";
} else {
    echo "<p>Brak danych do wyświetlenia.</p>";
}

echo "<br>";
echo "<br>";
$current_date = '';
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        if ($row["date"] != $current_date) {
            if ($current_date != '') {
                echo "</table><br>"; 
            }
            $current_date = $row["date"];
            $polishDayOfWeek = getPolishDayOfWeek($current_date);
            echo "<h2>" . $current_date . ", " . $polishDayOfWeek . "</h2>";
            echo "<br>";

            $current_tip_query = "SELECT total_amount FROM daily_tips WHERE date = '$current_date'";
            $current_tip_result = $conn->query($current_tip_query);
            $current_tip_row = $current_tip_result->fetch_assoc();
            $current_total_amount = $current_tip_row ? $current_tip_row['total_amount'] : 0;

            echo '<form method="post" action="">
            <input type="hidden" name="date" value="'.$current_date.'">
            <label for="total_amount_'.$current_date.'">Kwota napiwku:</label>
            <input type="number" id="total_amount_'.$current_date.'" name="total_amount" value="'.$current_total_amount.'">
            <input type="submit" value="Dodaj">
            <input type="submit" name="reset" value="Resetuj" class="reset-button">
          </form>';
    

                  echo "<table border='1'>
                  <tr>
                      <th>Imię i nazwisko</th>
                      <th>Kategoria</th>

                      <th>Ilość godzin</th>
                      <th>Napiwek</th>
                  </tr>";

                  $subquery = "SELECT u.name, u.category, wh.tip, wh.hours
                  FROM working_hours wh
                  JOIN users u ON wh.user_id = u.id
                  WHERE wh.date = '$current_date' AND u.category IN ('Kelner', 'Kuchnia Restauracyjna')
                  GROUP BY u.id
                  ORDER BY u.category, u.name, wh.start_time ASC";
     
     
$subresult = $conn->query($subquery);

while($subrow = $subresult->fetch_assoc()) {
    echo "<tr>
            <td>" . $subrow["name"]. "</td>
            <td>" . $subrow["category"]. "</td>
            <td>" . $subrow["hours"]. "</td>
            <td>" . $subrow["tip"]. "</td>

          </tr>";
}
          echo "</table>"; 
      }
  }
} else {
  echo "Brak danych do wyświetlenia";
}

$conn->close();
?>
<?php include 'footer.php'; ?>
</body>
</html>