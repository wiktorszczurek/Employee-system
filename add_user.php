<?php
require 'db_config.php';

session_start();
include 'layout.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    echo '<script type="text/javascript">window.location = "login.php";</script>';
    exit;
}

function generate_password() {

    $x = '0123456789';


    return substr(str_shuffle($x), 0, 5);
}

$message = "";
$messageType = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['name']) && isset($_POST['category']) && isset($_POST['rfid'])) {
        $name = $_POST['name'];
        $category = $_POST['category'];
        $rfid = $_POST['rfid'];
        $is_admin = isset($_POST['is_admin']) ? 2 : 0; 

        $rfidCheckQuery = "SELECT id FROM users WHERE rfid = ?";
        $stmt = $conn->prepare($rfidCheckQuery);
        $stmt->bind_param("s", $rfid);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $message = "Numer RFID już istnieje w bazie danych!";
            $messageType = "error";
        } else {

            do {
                $password = generate_password();
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT); 
            } while ($conn->query("SELECT id FROM users WHERE password='$hashedPassword'")->num_rows > 0);

            // Dodawanie użytkownika, jeśli RFID jest unikalny
            $insertSql = "INSERT INTO users (name, category, password, rfid, is_admin) VALUES (?, ?, ?, ?, ?)";
            $insertStmt = $conn->prepare($insertSql);
            $insertStmt->bind_param("ssssi", $name, $category, $hashedPassword, $rfid, $is_admin); 
            if ($insertStmt->execute()) {
                $message = "Użytkownik " . $name . " został dodany. Hasło: " . $password; 
                $messageType = "success";
            } else {
                $message = "Błąd: " . $conn->error;
                $messageType = "error";
            }
        }
    }

    if (isset($_POST['new_category'])) {
        $newCategory = $_POST['new_category'];

        $checkSql = "SELECT id FROM categories WHERE name = ?";
        $stmt = $conn->prepare($checkSql);
        $stmt->bind_param("s", $newCategory);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $message = "Kategoria już istnieje!";
            $messageType = "error";
        } else {
            $sql = "INSERT INTO categories (name) VALUES (?)";
            $insertStmt = $conn->prepare($sql);
            $insertStmt->bind_param("s", $newCategory);
            if ($insertStmt->execute()) {
                $message = "Nowa kategoria została dodana!";
                $messageType = "success";
            } else {
                $message = "Error: " . $insertStmt->error;
                $messageType = "error";
            }
            $insertStmt->close();
        }
        $stmt->close();
    }
}

if (isset($_GET['delete_category_id'])) {
    $categoryIdToDelete = intval($_GET['delete_category_id']);
    $sql = "DELETE FROM categories WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $categoryIdToDelete);
    if ($stmt->execute()) {
        $message = "Kategoria została usunięta!";
        $messageType = "success";
    } else {
        $message = "Error: " . $stmt->error;
        $messageType = "error";
    }
}


$categoriesArray = array();
$categories = $conn->query("SELECT id, name FROM categories");
while ($row = $categories->fetch_assoc()) {
    $categoriesArray[] = $row;
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dodaj Użytkownika</title>
    <style>





.form-box, .category-box {
    background: transparent;
    padding: 20px;
    border-radius: 8px;

    margin-bottom: 20px;
}

.notification {
    padding: 10px;
    border-radius: 8px;
    margin-bottom: 20px;
    color: white;
    font-weight: bold;
}

.success {
    color: #02b1d9;
    font-size: 1.1em;
    text-align: center;
    padding: 5px;
    background-color: #e6f9ff;
    border: 1px solid #02b1d9;
    border-radius: 5px;
    margin-bottom: 1rem;
}

.error {
    color: #ff0000;
    font-size: 1.1em;
    text-align: center;
    padding: 5px;
    background-color: #fee;
    border: 1px solid #fcc;
    border-radius: 5px;
    margin-bottom: 1rem;
}


label, input, select {
    width: 100%;
    margin-bottom: 15px;
}

input[type="text"], select {
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    background: #fff;
    color: #333;
    box-sizing: border-box;
    transition: border-color 0.3s ease;
}

input[type="text"]:focus, select:focus {
    border-color: #02b1d9;
    outline: none;
}

input[type="submit"] {
    cursor: pointer;
    background-color: #02b1d9;
    color: #fff;
    padding: 10px 15px;
    border: none;
    border-radius: 5px;
    transition: background-color 0.3s ease;
}



ul {
    list-style: none;
    padding: 0;
}

li {
    margin-bottom: 10px;
    padding: 5px;
    border-radius: 5px;
    background: #fff;
    transition: background-color 0.3s ease;
    border: 1px solid #ddd;
}

li:hover {
    background: #f9f9f9;
}

a {
    color: red;
    text-decoration: none;
    margin-left: 10px;
    transition: color 0.3s ease;
}



</style>
    <script>
        function confirmDelete(id) {
            if (confirm("Czy na pewno chcesz usunąć tę kategorię?")) {
                window.location.href = "add_user.php?delete_category_id=" + id;
            }
        }
    </script>
</head>
<body>
<main>
<?php 
if($message != "") {
    echo "<p class='" . $messageType . "'>" . $message . "</p>";
}
?>
<div class="date-form">
    <h1>Dodawanie użytkownika</h1>
<form action="add_user.php" method="post" class="form-box">
    <label for="name">Imię i Nazwisko:</label>
    <input type="text" id="name" name="name" required><br><br>

    <label for="category">Kategoria:</label>
    <select name="category" id="category" required>
        <?php foreach ($categoriesArray as $row): ?>
        <option value="<?php echo $row['name']; ?>"><?php echo $row['name']; ?></option>
        <?php endforeach; ?>
    </select><br><br>
    <label for="rfid">Numer RFID:</label>
    <input type="text" id="rfid" name="rfid" required><br><br>
    <label for="is_admin">Dodaj funkcje administratora:</label>
    <input type="checkbox" id="is_admin" name="is_admin" value="2"><br><br>


    <input type="submit" value="Dodaj">

        </div>
</form>
<div class="date-form">
    <h1>Dodawanie kategorii</h1>
<form method="post" class="form-box">

    <input type="text" id="new_category" name="new_category">
    <input type="submit" value="Dodaj Nową Kategorię"><br><br>
</form>
        </div>
        <div class="date-form">
    <h1>Wszystkie kategorie</h1>
<ul>
    <?php foreach ($categoriesArray as $row): ?>
    <li>
        <?php echo $row['name']; ?>
        <a href="#" onclick="confirmDelete(<?php echo $row['id']; ?>)">Usuń</a>

    </li>
    <?php endforeach; ?>
</ul>
</div>
    </div>

</main>
<?php include 'footer.php'; ?>

</body>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var rfidInput = document.getElementById('rfid');
        rfidInput.addEventListener('keypress', function(event) {
            if (event.which == 13) {
                event.preventDefault();
            }
        });
    });
</script>

</html>
