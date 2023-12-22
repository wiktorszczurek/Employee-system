<?php
require 'db_config.php';
session_start();
include 'layout.php';

if (!isset($_SESSION['user_id'])) {
    echo '<script type="text/javascript">window.location = "login.php";</script>';
    exit;
}

$userId = $_SESSION['user_id'];


$userCategorySql = "SELECT category FROM users WHERE id='$userId'";
$userCategoryResult = $conn->query($userCategorySql);
$userCategory = ($userCategoryResult->num_rows > 0) ? $userCategoryResult->fetch_assoc()['category'] : '';


$usersInCategorySql = "SELECT id, name FROM users WHERE category = '$userCategory'";
$usersInCategoryResult = $conn->query($usersInCategorySql);
$usersInCategory = $usersInCategoryResult->fetch_all(MYSQLI_ASSOC);



if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['message'])) {
    $messageText = $conn->real_escape_string($_POST['message']);
    $currentDate = date("Y-m-d H:i:s");


    $insertMessageSql = "INSERT INTO messages (sender_id, category, message, sent_date) VALUES ('$userId', '$userCategory', '$messageText', '$currentDate')";
    $conn->query($insertMessageSql);
    echo '<script type="text/javascript">window.location = "messages.php";</script>';
    exit;
}


$messagesSql = "SELECT m.*, u.name as sender_name FROM messages m JOIN users u ON m.sender_id = u.id WHERE m.category = '$userCategory' ORDER BY m.sent_date DESC";
$messagesResult = $conn->query($messagesSql);
$messages = $messagesResult->fetch_all(MYSQLI_ASSOC);


$updateLastMessageCheckSql = "UPDATE users SET last_message_check = NOW() WHERE id = '$userId'";
$conn->query($updateLastMessageCheckSql);


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Wiadomości</title>
    <style>


    .chat-users h2 {
        margin-top: 0;
        text-align: center;
    }

    .chat-users ul {
        list-style: none;
        padding: 0;
        display: flex; 
        flex-wrap: wrap; 
        align-items: center;
        justify-content: center; 
        gap: 10px; 
        text-align: center;
    }

    .chat-users li {
        padding: 2px 5px;
        font-size: 14px;
        color: grey;
        background-color: transparent;
        text-align: center;
        
    }

    .message-form {
        margin-bottom: 20px;
    }



    textarea {
        width: 100%;
        height: 100px;
        padding: 10px;
        border-radius: 4px;
        border: 1px solid #ddd;
        margin-bottom: 10px;
        resize: none;
    }

   .message-form button {
        background-color: #4CAF50;
        color: white;
        padding: 10px 15px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }


    .messages {
        background-color: transparent;
 
        padding: 10px 20px;
        border-radius: 5px;
    }

    .message {
        border-bottom: 1px solid grey;
        padding: 10px 0;
    }

    .message:last-child {
        border-bottom: none;
    }

    .message p {
        margin: 5px 0;
        color: grey;
    }
    .text p {
        color: black;
    }

    .message a {
        color: #d9534f;
        text-decoration: none;
    }

   .message a:hover {
        text-decoration: underline;
    }
    .date-form h2{

color: #333;
text-align: center;

font-size: 1.1em;
border-bottom: 1px solid #02b1d9;
padding-bottom: 10px;
letter-spacing: 1px; 
}
.chat-users h3{

color: #333;
text-align: center;

font-size: 1em;

padding-bottom: 10px;
letter-spacing: 1px; 
}
</style>
</head>
<body>
    <div class="date-form">
        <h1>Wiadomości</h1>
<div class="chat-users">
    <h3>Użytkownicy czatu:</h3>
    <ul>
        <?php foreach ($usersInCategory as $user): ?>
            <li><?php echo htmlspecialchars($user['name']); ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<br>
    <div class="message-form">
        <form action="messages.php" method="post">
            <textarea name="message" required placeholder="Wprowadź wiadomość tutaj"></textarea>
            <button type="submit">Wyślij Wiadomość</button>
        </form>
    </div>
    <?php if (isset($_GET['message']) && $_GET['message'] == 'MessageDeleted'): ?>
    <p style="color:#4CAF50;">Wiadomość została usunięta.</p>
<?php elseif (isset($_GET['error']) && $_GET['error'] == 'DeleteFailed'): ?>
    <p>Błąd podczas usuwania wiadomości.</p>
<?php endif; ?>
    <div class="messages">
        <h2>Odebrane wiadomości</h2>

        <?php foreach ($messages as $message): ?>
    <div class="message">
        <p><?php echo htmlspecialchars($message['sender_name']); ?></p>
        
        <p style="color: black; margin: 15px;">Treść: <?php echo nl2br(htmlspecialchars($message['message'])); ?></p>
        <p><?php echo $message['sent_date']; ?></p>
        
        <?php if ($message['sender_id'] == $userId): ?>

            <a href="delete_message.php?id=<?php echo $message['id']; ?>" onclick="return confirm('Czy na pewno chcesz usunąć tę wiadomość?');">Usuń Wiadomość</a>
        <?php endif; ?>
    </div>
<?php endforeach; ?>



    </div>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>
