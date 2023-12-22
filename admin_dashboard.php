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

if ($categoriesResult === false) {
    die("Error: " . $conn->error);
}

$categories = [];
while ($categoryRow = $categoriesResult->fetch_assoc()) {
    $categories[] = $categoryRow['name'];
}


$days = [
    'Dzisiejsza zmiana' => date('Y-m-d'),
    'Jutrzejsza zmiana' => date('Y-m-d', strtotime('+1 day')),
    'Wczorajsza zmiana' => date('Y-m-d', strtotime('-1 day'))
];

function getSchedule($conn, $category, $date) {
    $stmt = $conn->prepare("SELECT u.name, s.start_time, s.end_time 
                            FROM users u 
                            INNER JOIN schedules s ON u.id = s.user_id 
                            WHERE u.category=? AND s.work_date = ?");
    $stmt->bind_param('ss', $category, $date);
    $stmt->execute();
    return $stmt->get_result();
}

if (isset($_POST['end_shift_user_id'])) {
    $userIdToEndShift = $_POST['end_shift_user_id'];


    $stmt = $conn->prepare("SELECT start_time FROM working_hours WHERE user_id = ? AND is_active = 1");
    $stmt->bind_param('i', $userIdToEndShift);
    $stmt->execute();
    $result = $stmt->get_result();
    $shiftData = $result->fetch_assoc();
    $stmt->close();

    if ($result->num_rows > 0) {
        $start_time = new DateTime($shiftData['start_time']);
        $end_time = new DateTime(); 

 
        $interval = $start_time->diff($end_time);
        $hours = $interval->h;
        $minutes = $interval->i;


        if ($minutes > 15 && $minutes <= 45) {
            $hours += 0.5;
        } else if ($minutes > 45) {
            $hours += 1;
        }


        $stmt = $conn->prepare("UPDATE working_hours SET end_time = NOW(), hours = ?, is_active = 0 WHERE user_id = ? AND is_active = 1");
        $stmt->bind_param('di', $hours, $userIdToEndShift);
        $stmt->execute();
        $stmt->close();


        echo "<script>window.location.href='admin_dashboard.php';</script>";
    }
}
$sql = "SELECT * FROM notes ORDER BY created_at DESC";
$result = $conn->query($sql);
$notes = $result->fetch_all(MYSQLI_ASSOC);

$query = "SELECT DISTINCT category FROM users WHERE category != 'admin'";

$query = "SELECT * FROM categories";
$result = $conn->query($query);
$categories = [];
while ($row = $result->fetch_assoc()) {
    $categories[] = $row['name'];
}


$sql = "
SELECT survey_answers.*, users.name AS user_name, surveys.name AS survey_name, surveys.id AS survey_id
FROM survey_answers 
JOIN users ON survey_answers.user_id = users.id 
JOIN survey_questions ON survey_questions.id = survey_answers.question_id 
JOIN surveys ON surveys.id = survey_questions.survey_id 
GROUP BY survey_answers.created_at 
ORDER BY survey_answers.created_at DESC 
LIMIT 10";




$result_surveys = $conn->query($sql);
if (!$result_surveys) {
    die("Błąd zapytania: " . $conn->error);
}
$sql_all_surveys = "SELECT id, name FROM surveys ORDER BY created_at DESC";
$result_all_surveys = $conn->query($sql_all_surveys);


?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administracyjny</title>
    <style>
      .container {
        display: flex;
        text-align: center;
      }

      .sidebar {
        padding: 20px;
        margin: 10px; 
        border-radius: 5px;
        border: 1px solid #ccc; 
      }
      .sidebar,
      .main-content-item {
        padding: 20px;
        margin: 10px; 
        background-color: #f0f8ff;
        border: 1px solid #ccc; 
        border-radius: 5px;
      }

      .left-sidebar,
      .right-sidebar {
        width: 400px;
        display: flex;
        flex-direction: column;
      }

      .main-content {
        flex-grow: 1;

        display: flex;
        flex-direction: column;
      }

      .main-content-item {
        padding: 20px;
        margin-bottom: 10px; 
   
      }

      .main-content-item:last-child {
        margin-bottom: 0;
      }
      .date-form {
        background-color: #f0f8ff; 
        border: 1px solid #d1e0e0;
        border-radius: 5px;
        padding: 10px;
        width: 99%;
        font-size: 1.1em;

        margin: 20px auto;
      }
      .date-form h1 {
        color: red;
        text-align: center;
        margin-bottom: 20px;
        font-size: 1.2em;
        border-bottom: 2px solid red;
        padding-bottom: 10px;
        letter-spacing: 1px; 
      }
      .category-container {
        text-align: center; 
        margin-bottom: 20px; 
      }
      .end-shift-btn {
        color: white;
        background-color: red;
        padding: 5px 10px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
      }
      table {
        max-width: 700px;
        text-align: center;
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 2.5rem;
        border: solid 1px #8f8f8f;
        font-size: 0.8rem;
        table-layout: fixed;
      }

      th,
      td {
        padding: 10px;
        text-align: center;
        border: solid 1px #8f8f8f;
      }

      td span {
        font-weight: bold;
      }

      th {
        background-color: #02b1d9;
        border-bottom: 1px solid #8f8f8f;
        color: white;
      }

      tr:nth-child(even) {
        background-color: #ececec;
      }

      th[colspan="2"] {
        background-color: white;
        color: black;
        font-weight: bold;
        text-transform: uppercase;
      }

      th:nth-child(1),
      td:nth-child(1) {
        width: 60%;
      }
      th:nth-child(2),
      td:nth-child(2) {
        width: 40%;
      }
      .sidebar a {
        display: block; 
        color: #0044cc; 
      }
      @media (max-width: 768px) {
    .container {
      flex-direction: column;
    }

    .left-sidebar, .right-sidebar {
      order: 2; 
      width: 99%;
    }

    .main-content {
      order: 1; 
    }
  }

  .note {
            border: 1px solid #ccc;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 3px;
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
.signature-style {
        font-family: 'Your Font Name', sans-serif;  

        display: inline-block;  
        margin-top: 10px;
        font-style: italic;  
    }
    h2 {
      border-bottom: 2px solid #02b1d9;
        padding-bottom: 10px;
        letter-spacing: 1px; 
    }
    .border {
      border: 1px solid #ccc;
      margin-top: 1rem;
      padding: 0.5rem;
      border-radius: 5px;
    }
    .border h3 {
      margin: 0.4rem;
    }
    </style>
</head>
<body>
<div class="date-form">
      <h1>Panel administratora</h1>
    </div>
    <div class="container">
      <div class="left-sidebar">
        <div class="sidebar">
        <h2>Ostatnio zalogowani (last hour)</h2>
        <br>
        <?php

$sql = "SELECT id, name, DATE_FORMAT(last_login, '%H:%i:%s') as last_login_time FROM users WHERE last_login > NOW() - INTERVAL 1 HOUR AND is_admin != 1 ORDER BY last_login DESC";

$result = $conn->query($sql);

if ($result->num_rows > 0) {

    while($row = $result->fetch_assoc()) {

        echo "<a href='user_manage.php?user_id=" . $row["id"] . "'>" . $row["name"] . " (" . $row["last_login_time"] . ")</a><br>";
    }
} else {
    echo "Brak użytkowników, którzy logowali się w ciągu ostatniej godziny.";
}


?>
        </div>
        <div class="sidebar">
        <br>
            <h2>Aktualne informacje</h2>
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
    <div class="sidebar">
      <h2>Ostatnie checklisty</h2>
      <br>
    <?php while ($row = $result_surveys->fetch_assoc()): ?>

                <a href="view_surveys.php?survey_id=<?php echo $row['survey_id']; ?>">
                    <?php echo htmlspecialchars($row['survey_name']); ?>
                </a>

                <?php echo htmlspecialchars($row['user_name']); ?>

            <div class="survey-date">
                <?php echo $row['created_at']; ?>
                <br>
                <br>

        </div>
    <?php endwhile; ?>
</div>

      </div>

      <div class="main-content">
        <div class="main-content-item">
            <h2>Aktywni</h2>
            <table>
        <tr>

            <th>Imię i nazwisko</th>
            <th>Data rozpoczęcia zmiany</th>

        </tr>

        <?php
        $sql = "SELECT users.id, users.name, working_hours.start_time 
                FROM users 
                INNER JOIN working_hours ON users.id = working_hours.user_id 
                WHERE working_hours.is_active = 1";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo "<tr>

                        <td>".$row["name"]."</td>
                        <td>".$row["start_time"]."        <br>      <form method='post' onsubmit='return confirmEndShift();'>
                        <input type='hidden' name='end_shift_user_id' value='".$row["id"]."' />
                        <input type='submit' class='end-shift-btn' value='Zakończ Zmianę' />
                    </form></td>

                      </tr>";
            }
        } else {
            echo "<tr><td colspan='3'>Brak aktywnych zmian</td></tr>";
        }
        ?>
    </table></div>

    <div class="main-content-item">
        <h2>Zmiany</h2>
    <table>
    <style>
            .day-header {
                background-color: white; 
                color: black; 
            }
        </style>

        <?php
        foreach ($days as $dayName => $dayDate) {
            echo "<tr><th colspan='3' class='day-header'>" . $dayName . "</th></tr>"; 

            echo "<tr>
                    <th>Kategoria</th>
                    <th>Imię i Nazwisko</th>
                    <th>Godzina rozpoczęcia</th>
                  </tr>";

            foreach ($categories as $categoryLabel) {
                $result = getSchedule($conn, $categoryLabel, $dayDate);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>" . $categoryLabel . "</td>
                                <td>" . $row['name'] . "</td>
                                <td>" . substr($row['start_time'], 0, 5) . "</td>
                              </tr>";
                    }
                } 
                
            }


            echo "<tr><td colspan='3' style='height: 20px;'></td></tr>";
        }
        ?>
    </table>
</div>




        </div>
      <div class="right-sidebar">

      <div class="sidebar">
    <h2>Ostatnio dodane godziny</h2>
    <br>
    <style>
        .hours-entry {
            margin-bottom: 10px; 
        }
        .hours-entry a {
          text-decoration: none;
        }
    </style>

    <?php
    $sql = "SELECT working_hours.hours, working_hours.start_time, working_hours.end_time, users.name AS user_name, users.id AS user_id
            FROM working_hours
            JOIN users ON working_hours.user_id = users.id
            WHERE working_hours.hours IS NOT NULL
            ORDER BY working_hours.start_time DESC, users.id
            LIMIT 15";

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {

        while($row = $result->fetch_assoc()) {
            echo "<div class='hours-entry'><a href='user_hours.php?user_id=" . $row["user_id"] . "'>" . $row["user_name"] . ": " . $row["hours"] . " godzin</div> " . "</a>";
        }
    } else {
        echo "<div class='hours-entry'>0 results</div>";
    }
    ?>
</div>

<div class="sidebar">
    <h2>Top Pracownicy</h2>
<br>
    <?php
    echo '<form method="get" action="" class="custom-form">

        <input type="month" id="month" name="month" value="'.(isset($_GET['month']) ? $_GET['month'] : date('Y-m')).'">
        <input type="submit" value="Wybierz">
      </form>';

    ?>
    <br>

    
    <div class="border">

    <h3>Godziny</h3>

    <style>
        .trophy {
            display: inline-block;
            width: 16px; 
            height: 16px;
            background-size: cover;
        }
        .first-place {
            background-image: url('images/gold.png'); 
        }
        .second-place {
            background-image: url('images/silver.png'); 
        }
        .third-place {
            background-image: url('images/bronze.png');
        }
    </style>

<?php
$selectedMonth = isset($_GET['month']) ? $_GET['month'] : date('Y-m');
$sqlHours = "SELECT users.name, SUM(working_hours.hours) AS total_hours
             FROM working_hours
             JOIN users ON working_hours.user_id = users.id
             WHERE DATE_FORMAT(working_hours.date, '%Y-%m') = '$selectedMonth'
             GROUP BY working_hours.user_id
             ORDER BY total_hours DESC";

$resultHours = $conn->query($sqlHours);
$place = 1;
$lastTotalHours = 0;
$actualPlace = 1;

if ($resultHours->num_rows > 0) {
    while($row = $resultHours->fetch_assoc()) {
        if ($row["total_hours"] != $lastTotalHours) {
            $lastTotalHours = $row["total_hours"];
            if ($actualPlace > 3) {
                break;
            }
            $place = $actualPlace++;
        }
        $trophyClass = ($place == 1) ? "first-place" : (($place == 2) ? "second-place" : "third-place");
        echo "<div><span class='trophy $trophyClass'></span> " . $row["name"]. ": " . $row["total_hours"]. " godzin" . "</div>";
    }
} else {
    echo "<div>Brak danych</div>";
}
?>
</div>
    <div class="border">


<h3>Napiwki</h3>
<?php
$selectedMonth = isset($_GET['month']) ? $_GET['month'] : date('Y-m');
$categories = ['Kelner', 'Kuchnia Restauracyjna'];

foreach ($categories as $category) {

    echo "<h4>$category</h4>";


    $sqlTips = "SELECT users.name, SUM(working_hours.tip) AS total_tips
                FROM working_hours
                JOIN users ON working_hours.user_id = users.id
                WHERE DATE_FORMAT(working_hours.start_time, '%Y-%m') = '$selectedMonth' AND users.category = '$category'
                GROUP BY working_hours.user_id
                ORDER BY total_tips DESC";

$resultTips = $conn->query($sqlTips);
$place = 1;
$lastTipAmount = 0;
$actualPlace = 1;


if ($resultTips->num_rows > 0) {
    while($row = $resultTips->fetch_assoc()) {
        if ($row["total_tips"] != $lastTipAmount) {
            $lastTipAmount = $row["total_tips"];
            if ($actualPlace > 3) {
                break;
            }
            $place = $actualPlace++;
        }
        $trophyClass = ($place == 1) ? "first-place" : (($place == 2) ? "second-place" : "third-place");
        echo "<div><span class='trophy $trophyClass'></span> " . $row["name"]. ": " . $row["total_tips"]. " zł" . "</div>";

    }
    echo "<br>";
} else {
    echo "<div>Brak danych</div>";
}
}
?>








    </div>

    <div class="border">

    <h3>Punktualność</h3>

<?php
$selectedMonth = isset($_GET['month']) ? $_GET['month'] : date('Y-m');
$sqlPunctuality = "SELECT u.id, u.name, 
                   AVG(ABS(TIMESTAMPDIFF(MINUTE, wh.start_time, est.exact_start_time))) AS average_difference
                   FROM working_hours wh
                   JOIN users u ON wh.user_id = u.id
                   JOIN exact_start_times est ON wh.id = est.working_hours_id
                   WHERE DATE_FORMAT(wh.start_time, '%Y-%m') = '$selectedMonth'
                   GROUP BY u.id
                   ORDER BY average_difference ASC";

$resultPunctuality = $conn->query($sqlPunctuality);
$place = 1;
$lastAverageDifference = 0;
$actualPlace = 1;

if ($resultPunctuality->num_rows > 0) {
    while($row = $resultPunctuality->fetch_assoc()) {
        if ($row["average_difference"] != $lastAverageDifference) {
            $lastAverageDifference = $row["average_difference"];
            if ($actualPlace > 3) {
                break;
            }
            $place = $actualPlace++;
        }
        $trophyClass = ($place == 1) ? "first-place" : (($place == 2) ? "second-place" : "third-place");
        echo "<div><span class='trophy $trophyClass'></span> " . $row["name"]. ": " . round($row["average_difference"], 2). " minut" . "</div>";
    }
} else {
    echo "<div>Brak danych</div>";
}
?>
</div>
<div class="border">

<h3>Checklist</h3>

<?php
$selectedMonth = isset($_GET['month']) ? $_GET['month'] : date('Y-m');
$sqlSurveys = "SELECT u.name, COUNT(DISTINCT sa.session_id, sa.created_at) AS total_surveys
               FROM survey_answers sa
               JOIN users u ON sa.user_id = u.id
               WHERE DATE_FORMAT(sa.created_at, '%Y-%m') = '$selectedMonth'
               GROUP BY sa.user_id
               ORDER BY total_surveys DESC";


$resultSurveys = $conn->query($sqlSurveys);
$place = 1;
$lastTotalSurveys = 0;
$actualPlace = 1;

if ($resultSurveys->num_rows > 0) {
    while($row = $resultSurveys->fetch_assoc()) {
        if ($row["total_surveys"] != $lastTotalSurveys) {
            $lastTotalSurveys = $row["total_surveys"];
            if ($actualPlace > 3) {
                break;
            }
            $place = $actualPlace++;
        }
        $trophyClass = ($place == 1) ? "first-place" : (($place == 2) ? "second-place" : "third-place");
        echo "<div><span class='trophy $trophyClass'></span> " . $row["name"]. ": " . "" . $row["total_surveys"]. " " . "</div>";
    }
} else {
    echo "<div>Brak danych</div>";
}
?>
</div>


</div>



      </div>
    </div>






<?php include 'footer.php'; ?>

</body>
<script>
function confirmEndShift() {
    return confirm('Czy na pewno chcesz zakończyć zmianę tego użytkownika?');
}
</script>
</html>
