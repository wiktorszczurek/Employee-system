<?php
require 'db_config.php';
session_start();
include 'layout.php';

if (!isset($_SESSION['user_id'])) {
    echo '<script type="text/javascript">window.location = "login.php";</script>';
    exit;
}

if (!isset($_SESSION['session_id'])) {
    $_SESSION['session_id'] = uniqid(); 
}

$user_id = $_SESSION['user_id'];
$message = "";


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


$stmt = $conn->prepare("SELECT id FROM surveys WHERE id = ?");
$stmt->bind_param("i", $survey_id);
$stmt->execute();
$result = $stmt->get_result();
$survey = $result->fetch_assoc();

if (!$survey) {
    die("Nie znaleziono ankiety.");
}


if ($_SERVER['REQUEST_METHOD'] == "POST") {
    foreach ($_POST['question'] as $question_id => $answer) {
        $comment = $_POST['comment'][$question_id];

        $stmt = $conn->prepare("INSERT INTO survey_answers (user_id, question_id, answer, comment, session_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iisss", $user_id, $question_id, $answer, $comment, $_SESSION['session_id']);
        $stmt->execute();
    }

    echo "<script>window.location.href = 'view_surveys.php?survey_id=" . $survey_id . "';</script>";
}



$questions = [];
$stmt = $conn->prepare("SELECT id, question FROM survey_questions WHERE survey_id = ?");
$stmt->bind_param("i", $survey_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $questions[] = $row;
}

$stmt = $conn->prepare("SELECT id, name FROM surveys WHERE id = ?");
$stmt->bind_param("i", $survey_id);
$stmt->execute();
$result = $stmt->get_result();
$survey = $result->fetch_assoc();

if (!$survey) {
    die("Nie znaleziono ankiety.");
}
$stmt->close();





?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Wypełnij ankietę</title>
    <style>
        body{
            margin: 0;
            padding: 0;
        }
    .container {
        width: 600px;
        margin: 20px auto;
        padding: 20px;

        background-color: #f0f8ff;
            border: 1px solid #d1e0e0;
            border-radius: 5px;
    }

    h1 {

color: #333;
text-align: center;
margin-bottom: 20px;
font-size: 1.2em;
border-bottom: 2px solid #02b1d9;
padding-bottom: 10px;
letter-spacing: 1px; 
}

    .question-item {

        padding: 1rem;
        border-radius: 5px;

    }

    .custom-checkbox {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 70px;
        height: 35px;
        background: #999999;
        border-radius: 5px;
        cursor: pointer;
        margin-right: 5px;
        position: relative;
    }

    .custom-checkbox span {
        position: relative;
        display: block;
        text-align: center;
        width: 100%;
        color: white;
        z-index: 1;
    }

    .custom-checkbox input {
        position: absolute;
        opacity: 0;
        cursor: pointer;
        height: 0;
        width: 0;
    }

    .custom-checkbox.checked-yes {
        background-color: #008000;
    }

    .custom-checkbox.checked-no {
        background-color: #ff0000;
    }

    textarea {
        width: 200px;   
    height: 50px;  
    resize: vertical; 
    resize: none;
    border-color: #d1e0e0;
    }

    input[type="submit"] {
        display: block;
    width: 200px;
    padding: 10px;
    font-size: 18px;
    background-color: transparent;
    color: #02b1d9;
    border: 1px solid #02b1d9;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s ease;
    margin-top: 5px;
    box-sizing: border-box; 
    margin: 0 auto;
    }
 input[type="submit"]:hover {
            background-color: #02b1d9;
            color: #fff;
        }
        @media screen and (max-width: 768px) {
        .container {
            width: 90%;
            margin: 20px auto;
            padding: 10px;
        }

        textarea, input[type="submit"] {
            width: 100%;
            box-sizing: border-box;
        }

        .custom-checkbox {
            width: 60px;
            height: 35px;
        }

    }
</style>

</head>
<body>

<div class="container">
    <h1><?php echo htmlspecialchars($survey['name']); ?></h1>

    <?php if ($message): ?>
    <p><?php echo $message; ?></p>
    <?php endif; ?>

    <form action="" method="post">
        <?php 
        $questionNumber = 1; 
        foreach ($questions as $question): 
        ?>
            <div class="question-item">

                <strong><?php echo $questionNumber . ". " . htmlspecialchars($question['question']); ?></strong><br>
                <br>
                <label class="custom-label">
                    <div class="custom-checkbox">
                        <span>Tak</span>
                        <input type="checkbox" name="question[<?php echo $question['id']; ?>]" value="Tak">
                    </div>
                </label>
                <label class="custom-label">
                    <div class="custom-checkbox">
                        <span>Nie</span>
                        <input type="checkbox" name="question[<?php echo $question['id']; ?>]" value="Nie">
                    </div>
                </label>
                <br>
                <br>
                <label>
                    <textarea placeholder="Jeśli nie, dlaczego?" name="comment[<?php echo $question['id']; ?>]"></textarea>
                </label>
            </div>
        
        <?php 
        $questionNumber++;
        endforeach; 
        ?>
        <br>
        <br>
        <input type="submit" value="Wyślij odpowiedzi">
        <br>
        <br>
    
    </form>
</div>


<?php include 'footer.php'; ?>

<script>
    document.querySelectorAll(".custom-checkbox input").forEach(function(checkbox) {
        checkbox.addEventListener("change", function() {
            var customCheckbox = this.parentElement;
            if (this.checked) {
                if (this.value === "Tak") {
                    customCheckbox.classList.add("checked-yes");
                } else {
                    customCheckbox.classList.add("checked-no");
                }
            } else {
                customCheckbox.classList.remove("checked-yes", "checked-no");
            }
        });
    });
</script>

</body>
</html>
