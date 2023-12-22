<?php
require 'db_config.php';
session_start();
include 'layout.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    echo '<script type="text/javascript">window.location = "login.php";</script>';
    exit;
}

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
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Kategorie</title>
<style>
        .survey-card {
        width: 500px;
        margin: 20px auto;
        background-color: white;
        padding: 20px;
        border-radius: 5px;
        box-shadow: 0px 0px 10px 0px #aaa;
    }
    .survey-card  h2 {
            background-color: lightseagreen;
            padding: 10px;
            color: white;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }


        .date-form  ul {
            list-style-type: none;
            margin-bottom: 30px;
        }

        .date-form  ul li {
      
            padding: 10px;
            border-radius: 5px;
 

        }


        .date-form a {
            display: inline-block;
        padding: 5px 10px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        background-color: #02b1d9;
        color: white;
        text-decoration: none;
        }


    </style>
</head>
<body>
<div class="date-form">
    <h1>Kategorie</h1>


<ul>
    <?php foreach ($categories as $category): ?>
        <li>
            <a href="surveys.php?category=<?php echo urlencode($category); ?>"><?php echo $category; ?></a>
        </li>
    <?php endforeach; ?>
</ul>
    </div>
    <div class="date-form">
    <h1>Wszystkie checklisty</h1>
<ul>
    <?php while ($survey = $result_all_surveys->fetch_assoc()): ?>
        <li>
            <a href="view_surveys.php?survey_id=<?php echo $survey['id']; ?>"><?php echo htmlspecialchars($survey['name']); ?></a>
        </li>
    <?php endwhile; ?>
</ul>



</div>
<table border="1">
    <thead>
        <tr>
            <th>Nazwa ankiety</th>
            <th>Wysłane przez</th>
            <th>Data wysłania</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $result_surveys->fetch_assoc()): ?>
            <tr>
            <td><a href="view_surveys.php?survey_id=<?php echo $row['survey_id']; ?>"><?php echo htmlspecialchars($row['survey_name']); ?></a></td>

                <td><?php echo htmlspecialchars($row['user_name']); ?></td>
                <td><?php echo $row['created_at']; ?></td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>


<?php include 'footer.php'; ?>

</body>
</html>
