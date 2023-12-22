<?php
require 'db_config.php';

session_start();

if (isset($_GET['id'])) {
    $userId = $_GET['id'];


    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    try {

        $conn->begin_transaction();


        $conn->query("DELETE FROM survey_answers WHERE user_id='$userId'");


        $conn->query("DELETE FROM working_hours WHERE user_id='$userId'");
        $conn->query("DELETE FROM schedules WHERE user_id='$userId'");


        $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
        $stmt->bind_param("i", $userId);

        if ($stmt->execute()) {

            $conn->commit();
            header("Location: users.php?deleted=1");
        } else {

            $conn->rollback();
            throw new Exception("Błąd przy usuwaniu użytkownika z bazy danych.");
        }
    } catch (mysqli_sql_exception $e) {

        $conn->rollback();
        error_log("Błąd MySQL: " . $e->getMessage());
        $_SESSION['error'] = "Błąd bazy danych: " . $e->getMessage();
        header("Location: users.php?error=1");
    } catch (Exception $e) {
        error_log("Błąd: " . $e->getMessage());
        $_SESSION['error'] = $e->getMessage();
        header("Location: users.php?error=1");
    } finally {
        if (isset($stmt)) {
            $stmt->close();
        }
        $conn->close();
    }
}
?>
