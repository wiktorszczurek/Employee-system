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

$stmt = $conn->prepare("SELECT id, name FROM surveys WHERE category = ?");
$stmt->bind_param("s", $user_category);
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
    <title>Moje ankiety</title>
    <style>


        .container {
            background-color: #f0f8ff; 
            border: 1px solid #d1e0e0;
            border-radius: 5px;
            padding: 10px;

            font-size: 1.1em;
            width: 500px;
            margin: 20px auto;
            
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

        .survey-item {
            padding: 15px 0;
            border-bottom: 1px solid #02b1d9;
        }

        .survey-item:last-child {
            border-bottom: none;
        }

        .survey-item strong {
            display: block;
            font-size: 18px;
            margin-bottom: 10px;
        }

        .survey-item a {
    color: #02b1d9;
    text-decoration: none;
    margin: 10px;
    padding: 10px;
    border: 1px solid #02b1d9;
    border-radius: 5px;

    display: inline-block; 
    width: 130px; 
    text-align: center; 
}
.my-answers a {
    color: #02b1d9;
    text-decoration: none;
    margin: 10px;
    padding: 10px;
    border: 1px solid #02b1d9;
    border-radius: 5px;
    transition: background-color 0.3s;
    display: inline-block; 
    width: 190px; 
    text-align: center; 
    cursor: pointer;
}
.my-answers a:hover {
            background-color: #02b1d9;
            color: #fff;
        }
@media screen and (max-width: 768px) {
        .container {
            width: 90%;
            margin: 20px auto;
            padding: 10px;
        }
    }


    </style>
</head>
<body>
    <div class="container">
    <h1>Checklist</h1>
    <div class="my-answers">
    <a href="my_answers.php">Moje odpowiedzi</a>
    </div>
    <br>
    <?php foreach ($surveys as $survey): ?>
        <div class="survey-item">
            <strong><?php echo htmlspecialchars($survey['name']); ?></strong>

            <a href="fill_survey.php?survey_id=<?php echo $survey['id']; ?>">Wypełnij</a>
            
            <a href="view_surveys.php?survey_id=<?php echo $survey['id']; ?>">Odpowiedzi</a>

        </div>
    <?php endforeach; ?>
</div>
<?php include 'footer.php'; ?>

</body>
</html>
