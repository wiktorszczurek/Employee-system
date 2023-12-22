<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $password = $_POST["password"];


    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);


    echo "Zahashowane hasło: " . $hashedPassword;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <h2>Wprowadź Hasło do Zahashowania</h2>
    <form method="post">
        Hasło: <input type="password" name="password">
        <input type="submit" value="Hashuj">
    </form>
</body>
</html>

    
</body>
</html>