<?php
require 'db_config.php';
session_start();
include 'layout.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    echo '<script type="text/javascript">window.location = "login.php";</script>';
    exit;
}


$category = $_GET['category'] ?? null;

if (isset($_GET['delete_survey_id'])) {
    $delete_survey_id = $_GET['delete_survey_id'];


    $deleteAnswersStmt = $conn->prepare("DELETE FROM survey_answers WHERE question_id IN (SELECT id FROM survey_questions WHERE survey_id = ?)");
    $deleteAnswersStmt->bind_param('i', $delete_survey_id);
    $deleteAnswersStmt->execute();
    $deleteAnswersStmt->close();


    $stmt = $conn->prepare("DELETE FROM surveys WHERE id = ?");
    $stmt->bind_param('i', $delete_survey_id);
    
    if ($stmt->execute()) {
        $message = "Ankieta została pomyślnie usunięta!";
    } else {
        $message = "Błąd podczas usuwania ankiety.";
    }
    $stmt->close();
}
$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['survey_name'])) {
    $survey_name = $_POST['survey_name'];

    $stmt = $conn->prepare("INSERT INTO surveys (category, name) VALUES (?, ?)");
    $stmt->bind_param('ss', $category, $survey_name);

    if ($stmt->execute()) {
        $message = "Ankieta została pomyślnie utworzona!";
    } else {
        $message = "Błąd podczas tworzenia ankiety.";
    }
    $stmt->close();
}


$query = "SELECT * FROM surveys WHERE category = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('s', $category);
$stmt->execute();
$result = $stmt->get_result();
$surveys = [];
while ($row = $result->fetch_assoc()) {
    $surveys[] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Ankiety dla <?php echo $category; ?></title>
    <style>
    .survey-card {
        width: 700px;
        margin: 20px auto;
        background-color: white;
        padding: 20px;
        border-radius: 5px;
        box-shadow: 0px 0px 10px 0px #aaa;
    }
    .survey-card a {
        text-decoration: none;
        margin-right: 15px;
    }
    .btn {
        display: inline-block;
        padding: 5px 10px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        background-color: #02b1d9;
        color: white;
        margin: 5px;
        width: 150px;
    }
    .btn-delete {
    background-color: #e74c3c;
}

.custom-form {
    width: 500px;
    margin: 0 auto 40px auto;
    background-color: white;
    padding: 20px;
    border-radius: 5px;
    box-shadow: 0px 0px 10px 0px #aaa;
}

.custom-input {
    width: 100%;
    padding: 8px;
    margin-bottom: 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
}

.custom-button {
    background-color: #02b1d9;
    color: white;
    border: none;
    cursor: pointer;
    padding: 8px 15px;
    border-radius: 4px;
    transition: background-color 0.2s;
}

.custom-button:hover {
    background-color: #0188a6;
}
.date-form a {
    text-decoration: none;
}

</style>


</head>
<body>
<div class="date-form">
    <h1>Utwórz checkliste dla <?php echo $category; ?></h1>


<form action="surveys.php?category=<?php echo urlencode($category); ?>" method="post">
    <label for="survey_name">Nazwa ankiety:</label>
    <input type="text" name="survey_name" required class="custom-input">
    <input type="submit" value="Utwórz ankietę" class="custom-button">
</form>
</div>

<h3>Dostępne checklisty:</h3>

<?php foreach ($surveys as $survey): ?>
    <div class="date-form">
        <strong><?php echo $survey['name']; ?></strong>
        <br>
        <br>
        <a href="survey_details.php?survey_id=<?php echo $survey['id']; ?>" class="btn">Edytuj</a>
        
        <a href="view_surveys.php?survey_id=<?php echo $survey['id']; ?>" class="btn">Odpowiedzi</a>
        <br>
        <a href="surveys.php?delete_survey_id=<?php echo $survey['id']; ?>&category=<?php echo urlencode($category); ?>" class="btn btn-delete">Usuń</a>

    </div>
<?php endforeach; ?>

<?php
if ($message) {
    echo "<p>" . $message . "</p>";
}
?>
<?php include 'footer.php'; ?>

</body>
<script>
    document.querySelectorAll('.btn-delete').forEach(function(button) {
    button.addEventListener('click', function(e) {
        if (!confirm('Czy na pewno chcesz usunąć tę ankietę?')) {
            e.preventDefault();
        }
    });
});
</script>
</html>
