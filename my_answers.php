<?php
require 'db_config.php';
session_start();
include 'layout.php';

if (!isset($_SESSION['user_id'])) {
    echo '<script type="text/javascript">window.location = "login.php";</script>';
    exit;
}
$isAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
$user_id = $isAdmin && isset($_GET['user_id']) ? $_GET['user_id'] : $_SESSION['user_id'];

if (!$user_id) {
    die("Nie podano ID użytkownika");
}



$stmt = $conn->prepare("SELECT u.name, sa.created_at, sq.question, sa.answer, sa.comment, s.name AS survey_name
                        FROM survey_answers sa 
                        JOIN survey_questions sq ON sa.question_id = sq.id 
                        JOIN users u ON sa.user_id = u.id
                        JOIN surveys s ON sq.survey_id = s.id
                        WHERE sa.user_id = ? ORDER BY sa.created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$answers = [];
while ($row = $result->fetch_assoc()) {
    $answers[] = $row;
}

$stmt->close();

if ($isAdmin) {
    $userStmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
    $userStmt->bind_param('i', $user_id);
    $userStmt->execute();
    $userResult = $userStmt->get_result();
    $userName = $userResult->num_rows > 0 ? $userResult->fetch_assoc()['name'] : "Nieznany użytkownik";
    $headerTitle =  $userName;
} else {
    $headerTitle = "Moje odpowiedzi";
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Odpowiedzi z ankiet</title>
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
    <h1><?php echo htmlspecialchars($headerTitle); ?></h1>
    </div>
    <?php

    $grouped_answers = [];
    foreach ($answers as $answer) {
        $key = $answer['name'] . '_' . $answer['created_at'] . '_' . $answer['survey_name'];
        $grouped_answers[$key][] = $answer;
    }

    foreach ($grouped_answers as $key => $user_answers) {
        list($name, $created_at, $survey_name) = explode('_', $key);

        echo '<table border="1">
            <thead>
                <tr>
                    <th>' .'Data: ' . $created_at . '<br><br> ' . htmlspecialchars($survey_name) . '</th>
                    <th>Tak/Nie</th>
                    <th>Dlaczego nie?</th>
                </tr>
            </thead>
            <tbody>';

        $questionNumber = 1;

        foreach ($user_answers as $answer) {
            $question_text = $answer['question'];
            $answer_text = $answer['answer'];
            $comment_text = $answer['comment'];
            $answer_class = ''; 


            if ($answer_text == "Tak") {
                $answer_class = 'answer-yes';
            } elseif ($answer_text == "Nie") {
                $answer_class = 'answer-no';
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
