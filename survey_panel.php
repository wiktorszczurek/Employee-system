<?php
require 'db_config.php';
session_start();
include 'layout.php';

if (!isset($_SESSION['user_id'])) {
    echo '<script type="text/javascript">window.location = "login.php";</script>';
    exit;
}

$survey_id = $_GET['survey_id'];


$stmt = $conn->prepare("SELECT name FROM surveys WHERE id = ?");
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
    <title>Panel ankiety: <?php echo htmlspecialchars($survey['name']); ?></title>
</head>
<body>
    <div class="container">
        <h2><?php echo htmlspecialchars($survey['name']); ?></h2>
        <p>
            <a href="fill_survey.php?survey_id=<?php echo $survey_id; ?>">Wypełnij ankietę</a>
        </p>
        <p>
            <a href="view_surveys.php?survey_id=<?php echo $survey_id; ?>">Zobacz odpowiedzi</a>
        </p>
    </div>
    <?php include 'footer.php'; ?>

</body>
</html>
