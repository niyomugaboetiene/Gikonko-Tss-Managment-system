
<?php
include("conn.php");
session_start();

if (!isset($_SESSION['username'])) {
    $_SESSION['login_error'] = "Please login to access this page";
    header("Location:login.php");
    exit();
}

if (!isset($_GET['Mark_Id']) || !ctype_digit($_GET['Mark_Id'])) {
    $_SESSION['error'] = "Invalid mark record selected.";
    header("Location:listOfMarks.php");
    exit();
}

$Mark_Id = (int) $_GET['Mark_Id'];

$stmt = mysqli_prepare($conn, "DELETE FROM marks WHERE Mark_Id = ?");

if (!$stmt) {
    $_SESSION['error'] = "Unable to prepare the deletion request.";
    header("Location:listOfMarks.php");
    exit();
}

mysqli_stmt_bind_param($stmt, "i", $Mark_Id);

if (mysqli_stmt_execute($stmt)) {
    if (mysqli_stmt_affected_rows($stmt) > 0) {
        $_SESSION['success'] = "Mark record deleted successfully.";
    } else {
        $_SESSION['error'] = "The selected mark record was not found.";
    }
} else {
    $_SESSION['error'] = "Failed to delete the mark record. Please try again.";
}

mysqli_stmt_close($stmt);

header("Location:listOfMarks.php");
exit();
?>