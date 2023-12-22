<?php
require 'db_config.php';
session_start();
include 'layout.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    echo '<script type="text/javascript">window.location = "login.php";</script>';
    exit;
}

$userId = $_GET['user_id'] ?? null;

if (!$userId) {
    die("Nie podano ID użytkownika");
}

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Nie znaleziono użytkownika o podanym ID");
}

$user = $result->fetch_assoc();

function generateUniquePassword() {
    $x = '0123456789';
    return substr(str_shuffle(str_repeat($x, ceil(5/strlen($x)))), 0, 5);
}
$successMessage = "";
if (isset($_POST['reset_password'])) {
    $userId = $_POST['user_id'];

    $newPassword = generateUniquePassword();
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    $updateQuery = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $updateQuery->bind_param('si', $hashedPassword, $userId);
    $updateQuery->execute();

    if ($updateQuery->affected_rows > 0) {

        $successMessage = "Nowe hasło użytkownika: " . $newPassword;

    } else {
        echo "Błąd przy resetowaniu hasła.";
    }
}


if (isset($_POST['update_rfid'])) {
    $newRFID = $_POST['rfid'];
    $userId = $_POST['user_id'];

    $checkRFIDQuery = $conn->prepare("SELECT id FROM users WHERE rfid = ? AND id != ?");
    $checkRFIDQuery->bind_param('si', $newRFID, $userId);
    $checkRFIDQuery->execute();
    $checkRFIDResult = $checkRFIDQuery->get_result();

    if ($checkRFIDResult->num_rows > 0) {
        echo "Ten RFID jest już używany przez innego użytkownika.";
    } else {
        $updateRFIDQuery = $conn->prepare("UPDATE users SET rfid = ? WHERE id = ?");
        $updateRFIDQuery->bind_param('si', $newRFID, $userId);
        $updateRFIDQuery->execute();

        if ($updateRFIDQuery->affected_rows > 0) {
            echo "<script>window.location.href = 'user_profil.php?user_id=" . $userId . "';</script>";
        } else {
            echo "Błąd przy aktualizacji RFID.";
        }
    }
}


if (isset($_POST['delete_user'])) {
    $deleteMessagesQuery = $conn->prepare("DELETE FROM messages WHERE sender_id = ?");
    $deleteMessagesQuery->bind_param('i', $userId);
    $deleteMessagesQuery->execute();

    $deleteSchedulesQuery = $conn->prepare("DELETE FROM schedules WHERE user_id = ?");
    $deleteSchedulesQuery->bind_param('i', $userId);
    $deleteSchedulesQuery->execute();

    $deleteSurveyAnswersQuery = $conn->prepare("DELETE FROM survey_answers WHERE user_id = ?");
    $deleteSurveyAnswersQuery->bind_param('i', $userId);
    $deleteSurveyAnswersQuery->execute();

    $deleteRemovedUsersQuery = $conn->prepare("DELETE FROM removed_users WHERE user_id = ?");
    $deleteRemovedUsersQuery->bind_param('i', $userId);
    $deleteRemovedUsersQuery->execute();

    $deleteVacationQuery = $conn->prepare("DELETE FROM vacation WHERE user_id = ?");
    $deleteVacationQuery->bind_param('i', $userId);
    $deleteVacationQuery->execute();

    $deleteExactStartTimesQuery = $conn->prepare("
    DELETE FROM exact_start_times WHERE working_hours_id IN (
        SELECT id FROM working_hours WHERE user_id = ?
    )
");
    $deleteExactStartTimesQuery->bind_param('i', $userId);
    $deleteExactStartTimesQuery->execute();

    $deleteWorkingHoursQuery = $conn->prepare("DELETE FROM working_hours WHERE user_id = ?");
    $deleteWorkingHoursQuery->bind_param('i', $userId);
    $deleteWorkingHoursQuery->execute();

    $deleteUserQuery = $conn->prepare("DELETE FROM users WHERE id = ?");
    $deleteUserQuery->bind_param('i', $userId);
    if ($deleteUserQuery->execute()) {
        echo '<script type="text/javascript">window.location = "users.php";</script>';
    } else {
        echo "Błąd przy usuwaniu użytkownika: " . $conn->error;
    }
}


if (isset($_POST['change_admin_status'])) {
    $userIdToChange = $_POST['user_id_to_change'];

    if ($_POST['change_admin_status'] == 'remove') {
        $updateAdminStatusQuery = $conn->prepare("UPDATE users SET is_admin = 0 WHERE id = ?");
        $updateAdminStatusQuery->bind_param('i', $userIdToChange);
        $updateAdminStatusQuery->execute();
        echo "Użytkownikowi zostały usunięte uprawnienia administratora.";
    } elseif ($_POST['change_admin_status'] == 'confirm') {
        $updateAdminStatusQuery = $conn->prepare("UPDATE users SET is_admin = 2 WHERE id = ?");
        $updateAdminStatusQuery->bind_param('i', $userIdToChange);
        $updateAdminStatusQuery->execute();
        echo "Użytkownikowi zostały nadane uprawnienia administratora.";
    }
}

?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel użytkownika</title>
    <style>
        .container {
            background-color: #f0f8ff; 
            border: 1px solid #d1e0e0;
            border-radius: 5px;
            padding: 10px;
            font-size: 1.1em;
            width: 350px;
            margin: 20px auto;
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

        .show-password-btn {
            color: #02b1d9;
            text-decoration: none;
            margin: 0 10px;
            padding: 5px 0; 
            border: 1px solid #02b1d9;
            border-radius: 5px;
            transition: background-color 0.3s;
            display: inline-block; 
            width: 150px; 
            text-align: center;
            cursor: pointer;
            background-color: transparent;
        }

        .show-password-btn:hover {
            background-color: #02b1d9;
            color: #fff;
        }

        form {
            margin-top: 20px;
        }

        input[type="submit"] {
            color: #02b1d9;
            text-decoration: none;
            margin: 0 10px;
            padding: 5px 0; 
            border: 1px solid #02b1d9;
            border-radius: 5px;
            transition: background-color 0.3s;
            display: inline-block; 
            width: 150px;
            text-align: center; 
            cursor: pointer;
            background-color: transparent;
        }

        input[type="submit"]:hover {
            background-color: #02b1d9;
            color: #fff;
        }


        form {
            margin-top: 20px;
        }


        input[type="text"] {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
        }

        input[type="submit"].delete-user-btn {
            background-color: #ff0000;
            color: #fff;
            text-decoration: none;
            margin: 0 10px;
            padding: 5px 0; 
            border: 1px solid #ff0000;
            border-radius: 5px;
            transition: background-color 0.3s;
            display: inline-block; 
            width: 150px; 
            text-align: center; 
            cursor: pointer;
        }

        input[type="submit"].delete-user-btn:hover {
            background-color: #ff0000;
            color: #fff;
        }
        input[type="submit"].adm-btn {
            background-color: #4CAF50;
            color: #fff;
            text-decoration: none;
            margin: 0 10px;
            padding: 5px 0; 
            border: 1px solid #4CAF50;
            border-radius: 5px;
            transition: background-color 0.3s;
            display: inline-block; 
            width: 250px; 
            text-align: center; 
            cursor: pointer;
        }

    </style>
</head>
<body>
    <div class="container">
        <h1><?php echo $user['name']; ?></h1>


        
        <?php if ($successMessage != ""): ?>
    <p style="    color: #02b1d9;
    font-size: 1.1em;
    text-align: center;
    padding: 5px;
    background-color: #e6f9ff;
    border: 1px solid #02b1d9;
    border-radius: 5px;
    margin-bottom: 1rem;
    margin-top: 1rem;"><?php echo $successMessage; ?></p>
<?php endif; ?>

        <form action="" method="post">
            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
            <input type="submit" name="reset_password" value="Resetuj Hasło" onclick="return confirm('Czy na pewno chcesz zrestartować hasło tego użytkownika?');">
        </form>

        <form action="" method="post">
            <p>RFID:</p>
            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
            <input type="text" name="rfid" value="<?php echo $user['rfid']; ?>">
            <br>
            <br>
            <input type="submit" name="update_rfid" value="Aktualizuj RFID" onclick="return confirm('Czy na pewno chcesz zaktualizować RFID tego użytkownika?');">
        </form>
        <br>
        <br>
        <?php
if ($user['is_admin'] == 2) {
    echo '<p style="color: red;">Użytkownik jest administratorem.</p>';
    echo '<form action="" method="post">
            <input type="hidden" name="user_id_to_change" value="' . $user['id'] . '">
            <input type="hidden" name="change_admin_status" value="remove">
            <input type="submit" value="Usuń uprawnienia administratora" class="delete-user-btn" style="width: 250px;" onclick="return confirm(\'Czy na pewno chcesz usunąć uprawnienia administratora temu użytkownikowi?\');">
        </form>';
} else {

    echo '<form action="" method="post">
            <input type="hidden" name="user_id_to_change" value="' . $user['id'] . '">
            <input type="hidden" name="change_admin_status" value="confirm">
            <input type="submit" value="Nadaj uprawnienia administratora" class="adm-btn" onclick="return confirm(\'Czy na pewno chcesz nadać uprawnienia administratora temu użytkownikowi?\');">
        </form>';
}
?>
        <br>
        <br>
        <form action="" method="post">
            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
            <input type="submit" name="delete_user" class="delete-user-btn" value="Usuń użytkownika" onclick="return confirm('Czy na pewno chcesz usunąć tego użytkownika?');">
        </form>
    </div>
</body>

<?php include 'footer.php'; ?>

<script type="text/javascript">
    function togglePassword() {
        var passwordText = document.getElementById("password");
        var buttonText = document.getElementById("toggleButton");
        if (passwordText.style.display === "none") {
            passwordText.style.display = "inline";
            buttonText.textContent = "Ukryj Hasło";
        } else {
            passwordText.style.display = "none";
            buttonText.textContent = "Pokaż Hasło";
        }
    }
</script>
</html>
