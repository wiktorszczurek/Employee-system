<?php
require 'db_config.php';
session_start();
include 'layout.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    echo '<script type="text/javascript">window.location = "login.php";</script>';
    exit;
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['delete'])) {
        $noteId = $_POST['note_id'];
        $stmt = $conn->prepare("DELETE FROM notes WHERE id = ?");
        $stmt->bind_param("i", $noteId);
        $stmt->execute();
        $stmt->close();
        $message = "Notatka została usunięta!";
    } else {
        $title = $_POST['title'];
        $content = $_POST['content'];
        $category = $_POST['category'];
        $signature = isset($_POST['signature']) ? $_POST['signature'] : null;


        $check = $conn->prepare("SELECT * FROM notes WHERE title = ? AND content = ?");
        $check->bind_param("ss", $title, $content);
        $check->execute();
        $resultCheck = $check->get_result();

        if ($resultCheck->num_rows > 0) {
            $message = "Ta notatka już istnieje w bazie danych!";
        } else {
            $stmt = $conn->prepare("INSERT INTO notes (title, content, category, signature) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $title, $content, $category, $signature);
            if ($stmt->execute()) {
                $message = "Notatka została dodana pomyślnie!";
            } else {
                $message = "Wystąpił błąd podczas dodawania notatki.";
            }
            $stmt->close();
        }
        $check->close();
    }
}





$sql = "SELECT * FROM notes ORDER BY created_at DESC";
$result = $conn->query($sql);
$notes = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Notatki</title>
    <style>


        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
        }

        form {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 5px;
        }

        input[type="text"],
        textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 3px;
        }

        select {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 3px;
        }

        input[type="submit"] {
            background-color: #02b1d9;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }




        .note {
            border: 1px solid #ccc;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 3px;
        }

        .info {
    border: 1px solid blue;
    background-color: #e6f7ff; 
}

.important {
    border: 2px solid red;
    background-color: #ffeeee; 
}

        .note h3 {
            margin-top: 0;
        }

        .delete-btn {
            background-color: red;
            color: #fff;
            padding: 5px ;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }



        h3[style="color: red;"] {
            color: red;
        }
        .signature-style {
        font-family: 'Your Font Name', sans-serif;  

        display: inline-block;  
        margin-top: 10px;
        font-style: italic;  
    }
    </style>
</head>
<body>
<div class="date-form">
    <h1>Informacje</h1>
    <?php if($_SESSION['is_admin'] == 1): ?>
    <form action="notes.php" method="post">
        <label for="title">Tytuł:</label>
        <input type="text" id="title" name="title" required>
        <label for="content">Treść:</label>
        <textarea id="content" name="content" required></textarea>
        <label for="signature">Podpis: (nieobowiązkowe)</label>
        <input type="text" id="signature" name="signature">


        <label for="category">Kategoria:</label>
        <select id="category" name="category">
            <option value="info">Zwykła informacja</option>
            <option value="important">Ważne</option>
        </select>
        
        <input type="submit" value="Dodaj notatkę">
    </form>
    <?php endif; ?>


    <?php if ($message != ""): ?>
        <p><?php echo $message; ?></p>
    <?php endif; ?>

 
    <section>

    <?php foreach($notes as $note): ?>
    <div class="note <?php echo $note['category']; ?>">
        <?php if ($note['category'] == 'important'): ?>
            <h3 style="color: red;">WAŻNE!</h3>
        <?php endif; ?>
        <h3><?php echo htmlspecialchars($note['title']); ?></h3>
        <br>
        <p><?php echo nl2br(htmlspecialchars($note['content'])); ?></p>
        <?php if (!empty($note['signature'])): ?>
            <br>
            <p class="signature-style"><?php echo htmlspecialchars($note['signature']); ?></p>
    <?php endif; ?>
        
        

        
        <form method="post" style="display: inline;">
            <input type="hidden" name="note_id" value="<?php echo $note['id']; ?>">
            <br>
            <input type="submit" name="delete" value="Usuń" style="background-color: red;" class="delete-btn" onclick="return confirm('Czy na pewno chcesz usunąć tę notatkę?');">

        </form>
    </div>
<?php endforeach; ?>

    </section>
</div>
        </div>
<?php include 'footer.php'; ?>
</body>
</html>

