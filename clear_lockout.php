<?php
session_start();

if (isset($_SESSION['lockout_until'])) {
    unset($_SESSION['lockout_until'], $_SESSION['lockout_level']);
}

echo "success";
?>
