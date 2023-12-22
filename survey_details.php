<?php
require 'db_config.php';
session_start();
include 'layout.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    echo '<script type="text/javascript">window.location = "login.php";</script>';
    exit;
}

if (!isset($_GET['survey_id'])) {
    die("Brak ID ankiety w adresie URL");
}

$survey_id = $_GET['survey_id'];
$message = '';


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['question'])) 
    foreach ($_POST['question'] as $question) {
        if (!empty($question)) {
            

            $check_stmt = $conn->prepare("SELECT id FROM survey_questions WHERE survey_id = ? AND question = ?");
            $check_stmt->bind_param('is', $survey_id, $question);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows == 0) { 
                $stmt = $conn->prepare("INSERT INTO survey_questions (survey_id, question) VALUES (?, ?)");
                $stmt->bind_param('is', $survey_id, $question);
                
                if (!$stmt->execute()) {
                    $message .= "Wystąpił błąd podczas dodawania pytania: $question. ";
                }
                $stmt->close();
            }
            
            $check_stmt->close();
        }
    }
    

if (isset($_POST['delete_question_id'])) {
    $delete_id = $_POST['delete_question_id'];

    $stmt = $conn->prepare("DELETE FROM survey_questions WHERE id = ?");
    $stmt->bind_param('i', $delete_id);

    if ($stmt->execute()) {
        $message .= "Pytanie zostało usunięte!";
    } else {
        $message .= "Wystąpił błąd podczas usuwania pytania.";
    }
    

    $stmt->close();

}



if (isset($_POST['edited_question']) && isset($_POST['question_id'])) {
    $edited_question = $_POST['edited_question'];
    $question_id = $_POST['question_id'];

    $stmt = $conn->prepare("UPDATE survey_questions SET question = ? WHERE id = ?");
    $stmt->bind_param('si', $edited_question, $question_id);

    if ($stmt->execute()) {
        $message .= "Pytanie zostało zaktualizowane!";
    } else {
        $message .= "Wystąpił błąd podczas aktualizacji pytania.";
    }

    $stmt->close();

}

$stored_questions = array();
$query = "SELECT id, question FROM survey_questions WHERE survey_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $survey_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $stored_questions[] = $row;
}
$stmt->close();

?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dodaj/Edytuj Pytania</title>
    <style>


        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background-color: #f0f8ff;
            border: 1px solid #d1e0e0;
            border-radius: 5px;


        }

        h2 {
            text-align: center;
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

        .message {
            color: green;
            text-align: center;
            margin-bottom: 10px;
        }

        .question-list {
            list-style-type: none;
            padding: 0;
        }

        .question-item {
            margin-bottom: 10px;
        }

        .question-item strong {
            font-weight: bold;
        }

        .question-item form {
            display: flex;
            align-items: center;
            width: 100%;
        }

        .question-item textarea {
            width: 70%;
            padding: 5px;
            border: 1px solid #ccc;
        }

        .question-item input[type="submit"] {
            background-color: #02b1d9;
            color: #fff;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
            border-radius: 5px;
        }



        .add-question-form {
            margin-top: 20px;
        }

        .add-question-form label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .add-question-form textarea {
            width: 100%;
            padding: 5px;
            border: 1px solid #ccc;
            margin-bottom: 10px;
        }

        .add-question-form button {
            background-color: #02b1d9;
            color: #fff;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
            border-radius: 5px;
        }

        .add-question-form input[type="submit"] {
            background-color: #02b1d9;
            color: #fff;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
            border-radius: 5px;
        }


    </style>
</head>
<body>
<div class="container">
    <h1>Dodaj/edytuj pytania</h1>
    <?php if ($message): ?>
    <p class="message"><?php echo $message; ?></p>
    <?php endif; ?>

    <h3>Aktualne pytania:</h3>
    <ul class="question-list">
        <?php $question_number = 1; ?>
        <?php foreach ($stored_questions as $stored_question):  ?>
        <li class="question-item">
            <strong><?php echo $question_number++; ?>. </strong>
            <form action="" method="post">
                <textarea name="edited_question"><?php echo htmlspecialchars($stored_question['question']); ?></textarea>
                <input type="hidden" name="question_id" value="<?php echo $stored_question['id']; ?>">
                <br>
                <br>
                <input type="submit" value="Zapisz zmiany">
            </form>
            <form action="" method="post">
                <input type="hidden" name="delete_question_id"  value="<?php echo $stored_question['id']; ?>">
                <input type="submit" value="Usuń" style="background-color: red;" style="margin: 0 auto;" onclick="return confirm('Czy na pewno chcesz usunąć to pytanie?');">
            </form>
        </li>
        <?php endforeach; ?>
    </ul>

    <h3>Dodaj nowe pytania:</h3>
    <form class="add-question-form" action="" method="post">
        <label for="new_question">Nowe pytanie:</label>
        <textarea name="question[]" id="new_question" rows="3"></textarea>
        <button type="button" id="addQuestionBtn">Dodaj kolejne pytanie</button>
        <input type="submit" value="Zapisz pytania">
    </form>
</div>

    <?php include 'footer.php'; ?>

<script>
    let questionCount = 1;

    document.getElementById('addQuestionBtn').addEventListener('click', function() {
        questionCount++;
        var questionContainer = document.querySelector('.add-question-form');
        var newLabel = document.createElement('label');
        newLabel.setAttribute('for', 'question_' + questionCount);
        newLabel.innerHTML = 'Nowe pytanie ' + questionCount + ':';
        var newTextarea = document.createElement('textarea');
        newTextarea.setAttribute('name', 'question[]');
        newTextarea.setAttribute('id', 'question_' + questionCount);
        newTextarea.setAttribute('rows', '3');

        questionContainer.insertBefore(newLabel, questionContainer.lastChild);
        questionContainer.insertBefore(newTextarea, questionContainer.lastChild);
    });
</script>
</body>
</html>

