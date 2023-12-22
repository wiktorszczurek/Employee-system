<?php
require 'db_config.php';
session_start();
include 'layout.php';

if (!isset($_SESSION['user_id'])) {
    echo '<script type="text/javascript">window.location = "login.php";</script>';
    exit;
}

$user_id = $_SESSION['user_id'];


$stmt = $conn->prepare("SELECT category FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    die("Nie znaleziono użytkownika.");
}


$user_category = $user['category'];

if (!isset($_GET['survey_id'])) {
    die("Nie przekazano ID ankiety.");
}


$survey_id = $_GET['survey_id'];

$stmt = $conn->prepare("SELECT u.name, sa.created_at, sq.question, sa.answer, sa.comment 
                        FROM survey_answers sa 
                        JOIN survey_questions sq ON sa.question_id = sq.id 
                        JOIN users u ON sa.user_id = u.id
                        WHERE sq.survey_id = ? ORDER BY sa.created_at DESC");
$stmt->bind_param("i", $survey_id);
$stmt->execute();
$result = $stmt->get_result();

$answers = [];
while ($row = $result->fetch_assoc()) {
    $answers[] = $row;
}

$stmt->close();

$stmt = $conn->prepare("SELECT name FROM surveys WHERE id = ?");
$stmt->bind_param("i", $survey_id);
$stmt->execute();
$surveyResult = $stmt->get_result();
$surveyName = '';
if ($row = $surveyResult->fetch_assoc()) {
    $surveyName = $row['name'];
}
$stmt->close();

?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Odpowiedzi z ankiety</title>
    <style>

        .answer-yes {
    color: green;
}

.answer-no {
    color: red;
}



    </style>
</head>
<body>
<div class="date-form">
<h1><?php echo htmlspecialchars($surveyName); ?></h1>
</div>
    <?php
    $grouped_answers = [];
    foreach ($answers as $answer) {
        $key = $answer['name'] . '_' . $answer['created_at'];
        $grouped_answers[$key][] = $answer;
    }

    $stmt = $conn->prepare("SELECT id, question FROM survey_questions WHERE survey_id = ?");
    $stmt->bind_param("i", $survey_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $questions = [];
    while ($row = $result->fetch_assoc()) {
        $questions[$row['id']] = $row['question'];
    }
    $stmt->close();

    foreach ($grouped_answers as $key => $user_answers) {
        $name = $user_answers[0]['name'];
        $created_at = $user_answers[0]['created_at'];

        echo '<table border="1">
            <thead>
                <tr>
                    <th>' . htmlspecialchars($name) . '<br>Data: ' . $created_at . '</th>
                    <th>Tak/Nie</th>
                    <th>Dlaczego nie?</th>
                </tr>
            </thead>
            <tbody>';

        $questionNumber = 1; 

        foreach ($questions as $question_id => $question_text) {
            $answer_text = '';
            $comment_text = '';
            $answer_class = ''; 
        
            foreach ($user_answers as $answer) {
                if ($answer['question'] == $question_text) {
                    $answer_text = $answer['answer'];
                    $comment_text = $answer['comment'];
        
                    if ($answer_text == "Tak") {
                        $answer_class = 'answer-yes';
                    } elseif ($answer_text == "Nie") {
                        $answer_class = 'answer-no';
                    }
                    
                    break;
                }
            }
        
            echo '<tr>
                    <td>' . $questionNumber . '. ' . htmlspecialchars($question_text) . '</td>
                    <td class="' . $answer_class . '">' . htmlspecialchars($answer_text) . '</td> <!-- Dodane klasy -->
                    <td>' . htmlspecialchars($comment_text) . '</td>
                  </tr>';
        
            $questionNumber++;
        }

        echo '</tbody>
        </table><br>';
    }
    ?>


<?php include 'footer.php'; ?>

</body>
</html>
