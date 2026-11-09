
<?php
include("conn.php");
session_start();

if (!isset($_SESSION['username'])) {
    $_SESSION['login_error'] = "Please login to access this page";
    header("Location:login.php");
    exit();
}

if (!isset($_GET['Trainee_Id']) || !ctype_digit($_GET['Trainee_Id'])) {
    $_SESSION['error'] = "Invalid trainee selected.";
    header("Location:listOfTrainee.php");
    exit();
}

$Trainee_Id = (int) $_GET['Trainee_Id'];

mysqli_begin_transaction($conn);

try {
    $stmtMarks = mysqli_prepare(
        $conn,
        "DELETE FROM marks WHERE Trainee_Id = ?"
    );

    if (!$stmtMarks) {
        throw new Exception("Unable to prepare marks deletion.");
    }

    mysqli_stmt_bind_param($stmtMarks, "i", $Trainee_Id);

    if (!mysqli_stmt_execute($stmtMarks)) {
        throw new Exception("Unable to delete the trainee's marks.");
    }

    mysqli_stmt_close($stmtMarks);

    $stmtTrainee = mysqli_prepare(
        $conn,
        "DELETE FROM trainees WHERE Trainee_Id = ?"
    );

    if (!$stmtTrainee) {
        throw new Exception("Unable to prepare trainee deletion.");
    }

    mysqli_stmt_bind_param($stmtTrainee, "i", $Trainee_Id);

    if (!mysqli_stmt_execute($stmtTrainee)) {
        throw new Exception("Unable to delete the trainee.");
    }

    $deleted = mysqli_stmt_affected_rows($stmtTrainee);

    mysqli_stmt_close($stmtTrainee);

    if ($deleted === 0) {
        throw new Exception("The selected trainee was not found.");
    }

    mysqli_commit($conn);

    $_SESSION['success'] = "Trainee and associated marks deleted successfully.";

} catch (Throwable $e) {
    mysqli_rollback($conn);
    $_SESSION['error'] = $e->getMessage();
}

header("Location:listOfTrainee.php");
exit();
?>
