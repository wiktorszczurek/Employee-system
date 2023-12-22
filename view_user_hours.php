<?php
require 'db_config.php';
include 'layout.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    echo '<script type="text/javascript">window.location = "login.php";</script>';
    exit;
}

$userId = $_GET['user_id'];


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['entry_id']) && isset($_POST['hours'])) {
    $entryId = $_POST['entry_id'];
    $updatedHours = $_POST['hours'];
    $updateSql = "UPDATE working_hours SET hours='$updatedHours' WHERE id='$entryId'";
    if ($conn->query($updateSql) === TRUE) {
        echo "Godziny zostały zaktualizowane";
    } else {
        echo "Błąd: " . $conn->error;
    }
}
if(isset($_POST['add_hours'])) {
    $date = $_POST['date'];
    $hours = $_POST['hours'];
    $userId = $_GET['user_id'];

    $sql = "INSERT INTO working_hours (user_id, date, hours) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $userId, $date, $hours);  

    if($stmt->execute()) {
        echo "<p>Godziny zostały pomyślnie dodane.</p>";
    } else {
        echo "<p>Wystąpił błąd podczas dodawania godzin: " . $stmt->error . "</p>";
    }
}


$sql = "SELECT id, date, hours FROM working_hours WHERE user_id='$userId' ORDER BY date DESC"; 
$result = $conn->query($sql);
?>

<main>
    <h1>Godziny pracy użytkownika</h1>
    <br>
    <h2>Dodaj godziny pracy dla użytkownika</h2>
    <br>
<form action="" method="post">
    <label for="date">Data:</label>
    <input type="date" name="date" required>
    
    <label for="hours">Godziny:</label>
    <input type="number" name="hours" step="0.01" required>

    <input type="submit" name="add_hours" value="Dodaj godziny">
</form>
<br>

    <?php 
    if ($result->num_rows > 0) {
        echo '<table border="1">';
        echo '<tr><th>Data</th><th>Godziny</th><th>Akcja</th></tr>';
        while ($row = $result->fetch_assoc()) {
            echo '<form action="view_user_hours.php?user_id=' . $userId . '" method="post">';
            echo '<tr>';
            echo '<td>' . $row['date'] . '</td>';
            echo '<td><input type="number" step="0.5" name="hours" value="' . $row['hours'] . '"></td>'; 
            echo '<td>';
            echo '<input type="hidden" name="entry_id" value="' . $row['id'] . '">';
            echo '<input type="submit" value="Zaktualizuj">';
            echo '</td>';
            echo '</tr>';
            echo '</form>';
        }
        echo '</table>';
    } else {
        echo "<p>Brak godzin pracy dla tego użytkownika.</p>";
    }
    ?>

</main>
<?php include 'footer.php'; ?>
