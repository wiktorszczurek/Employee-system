<?php
require 'db_config.php';
session_start();
$message = "";
$max_attempts = 5;


if (isset($_SESSION['lockout_until']) && time() < $_SESSION['lockout_until']) {
    $remaining_time = $_SESSION['lockout_until'] - time();
    $message = "Przekroczyłeś liczbę prób logowania. Poczekaj jeszcze $remaining_time sekund.";
} else {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $password = $_POST['password'];
        $rfid = isset($_POST['rfid']) ? $_POST['rfid'] : null;

        if (!empty($rfid)) {
 
            $sql = "SELECT * FROM users WHERE rfid=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $rfid);
        } else {

            $sql = "SELECT * FROM users";
            $stmt = $conn->prepare($sql);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $user_authenticated = false;

        while ($user = $result->fetch_assoc()) {
            if (!empty($rfid) || password_verify($password, $user['password'])) {
    
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['is_admin'] = $user['is_admin'];
                $user_authenticated = true;

                $updateLoginTimeSql = "UPDATE users SET last_login = NOW() WHERE id = ?";
                $updateStmt = $conn->prepare($updateLoginTimeSql);
                $updateStmt->bind_param("i", $user['id']);
                $updateStmt->execute();
                $updateStmt->close();

                unset($_SESSION['login_attempts'], $_SESSION['lockout_until'], $_SESSION['lockout_level']);
                if (isset($_POST['remember_me'])) {
                    setcookie('user_id', $user['id'], time() + (86400 * 30)); 
                }

                if ($user['is_admin'] == 1) {
                    header('Location: admin_dashboard.php');
                } else {
                    if (!empty($rfid)) {
                        $_SESSION['rfid_login'] = true;
                        header('Location: hours.php');
                    } else {
                        header('Location: user_dashboard.php');
                    }
                }
                break; 
            }
        }

        if (!$user_authenticated) {

            if (!isset($_SESSION['login_attempts'])) {
                $_SESSION['login_attempts'] = 0;
            }
            $_SESSION['login_attempts']++;
            $remaining_attempts = $max_attempts - $_SESSION['login_attempts'];

            if ($_SESSION['login_attempts'] >= $max_attempts) {

                if (!isset($_SESSION['lockout_level'])) {
                    $_SESSION['lockout_level'] = 1;
                }

                switch ($_SESSION['lockout_level']) {
                    case 1:
                        $_SESSION['lockout_until'] = time() + 60; 
                        break;
                    case 2:
                        $_SESSION['lockout_until'] = time() + 300; 
                        break;
                    default:
                        $_SESSION['lockout_until'] = time() + 900; 
                        break;
                }

                $_SESSION['login_attempts'] = 0; 
                $_SESSION['lockout_level']++; 

                $remaining_time = $_SESSION['lockout_until'] - time();
                $message = "Przekroczyłeś liczbę prób logowania. Poczekaj jeszcze $remaining_time sekund.";
            } else {
                $message = "Niepoprawne hasło lub RFID! Pozostało prób: $remaining_attempts";
            }
        }

        $stmt->close();
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Willa Team</title>
    <link rel="icon" type="image/png" href="grupa.png">
    <style>
      body {
        font-family: Arial, sans-serif;
        background-color: #f4f4f4;
        margin: 0;
        padding: 0;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
      }
      .container {
        width: 100%;
        max-width: 250px;
        padding: 20px;
        background-color: white;
        border-radius: 5px;
        box-shadow: 0px 0px 10px 0px #aaa;
      }
      .container h2 {
        text-align: center;
        color: #02b1d9;
        margin-bottom: 1rem;
      }
      .form-control {
        width: 100%;
        padding: 10px;
        margin: 10px 0;
        border-radius: 5px;
        border: 1px solid #d1d1d1;
        box-sizing: border-box;
      }
      .btn {
        padding: 10px 15px;
        width: 100%;
        border-radius: 5px;
        border: none;
        color: white;
        background-color: #02b1d9;
        cursor: pointer;
      }
      .error-message {
        color: #ff0000;
        font-size: 0.9em;
        text-align: center;
        padding: 5px;
        background-color: #fee;
        border: 1px solid #fcc;
        border-radius: 5px;
        margin-top: 1rem;
      }
      .container img {
        display: block;
        width: 100px;
        height: auto;
        margin: 0 auto;
      }

      .hidden-input {
    opacity: 0; 
    position: absolute;
    left: -9999px;
}

.rfid-label {
    color: gray; 
    cursor: pointer;
}

.rfid-label.focused {
    color: blue; 
}
.rfid-status {
        color: #666;
        margin-bottom: 10px;

    }

    </style>
</head>
<body>
<div class="container">
    <img src="willa.png" alt="Twoje logo">

    <form action="login.php" method="post">
    <input type="password" class="form-control" id="password" name="password" placeholder="PIN"><br>
    <input type="checkbox" id="remember_me" name="remember_me">
    <label for="remember_me">Zapamiętaj mnie</label>
<br>
    <input type="text" id="rfid" name="rfid" class="hidden-input">


<br>
    <input type="submit" class="btn" value="Zaloguj">
    <div id="rfidStatus" class="rfid-status"></div>
<img id="rfidImage" src="rfid_on1.png" alt="RFID Status" onclick="toggleRFID()">

</form>

    <div id="timer" class='error-message' style='display:none;'></div>

<?php 
if (!isset($_SESSION['lockout_until']) && $message != "") {
    echo "<div class='error-message'>" . $message . "</div>";
}
?>
</div>
</body>
<script>
function countdown() {
    let currentTime = Math.floor(Date.now() / 1000);
    let timeLeft = lockoutUntil - currentTime;

    let minuty = Math.floor(timeLeft / 60);
    let sekundy = timeLeft % 60;

    if (timeLeft <= 0) {
        clearInterval(interval);
        fetch('clear_lockout.php', {
            method: 'POST',
        }).then(response => {
            window.location.href = 'login.php';
        });
    } else {
        document.getElementById('timer').innerHTML = "Przekroczyłeś liczbę prób logowania.<br> Poczekaj jeszcze " + minuty + "m " + sekundy + "s.";
    }
}



    <?php if (isset($_SESSION['lockout_until'])): ?>
        let lockoutUntil = <?php echo $_SESSION['lockout_until']; ?> - 2;

        let currentTime = Math.floor(Date.now() / 1000);
        let timeLeft = lockoutUntil - currentTime;
        document.getElementById('timer').style.display = 'block';

        let interval = setInterval(countdown, 1000);
        countdown();  
    <?php endif; ?>
</script>
<script>
    document.getElementById('rfid').addEventListener('input', function() {
    if(this.value.length >= <10>) {
        this.form.submit();
    }
});
</script>
<script>

 function focusRFID() {
        var rfidInput = document.getElementById('rfid');
        rfidInput.focus();
        rfidLabel.classList.add('focused');
        updateRFIDImage(true);
    }
    var lastKeypressTime = 0;

document.getElementById('rfid').addEventListener('keydown', function(event) {
    if (event.key === 'Enter') {
        var currentTime = new Date().getTime();

        if (currentTime - lastKeypressTime > 500) {

            event.preventDefault();
        }
    } else {

        lastKeypressTime = new Date().getTime();
    }
});
0007589117

    function updateRFIDImage(isFocused) {
        var rfidImage = document.getElementById('rfidImage');
        if (isFocused) {
            rfidImage.src = 'rfid_on1.png'; 
        } else {
            rfidImage.src = 'rfid_off7.png'; 
        }
    }

    function toggleRFID() {
    var rfidImage = document.getElementById('rfidImage');
    var rfidInput = document.getElementById('rfid');
    var rfidStatus = document.getElementById('rfidStatus');

    if (rfidImage.src.includes('rfid_off7.png')) {

        location.reload();
    } else {

        rfidImage.src = 'rfid_on1.png';
        rfidInput.focus(); 
        rfidStatus.innerText = "";
    }
}






    document.getElementById('rfid').addEventListener('focus', function() {
        updateRFIDImage(true);
        document.getElementById('rfidLabel').classList.add('focused');
    });

    document.getElementById('rfid').addEventListener('blur', function() {
        updateRFIDImage(false);
        document.getElementById('rfidLabel').classList.remove('focused');
    });


    window.onload = function() {
        updateRFIDImage(false);
        focusRFID(); 
    };
</script>
</html>