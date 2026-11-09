
<?php
include("conn.php");
session_start();

if (!isset($_SESSION['username'])) {
    $_SESSION['login_error'] = "Please login to access this page";
    header("Location:login.php");
    exit();
}

if (!isset($_GET['Trade_id']) || !ctype_digit($_GET['Trade_id'])) {
    $_SESSION['error'] = "Invalid trade selected.";
    header("Location:listOfTrades.php");
    exit();
}

$Trade_Id = (int) $_GET['Trade_id'];

$checkStmt = mysqli_prepare(
    $conn,
    "SELECT COUNT(*) AS total FROM trainees WHERE Trade_id = ?"
);

if (!$checkStmt) {
    $_SESSION['error'] = "Unable to check trade dependencies.";
    header("Location:listOfTrades.php");
    exit();
}

mysqli_stmt_bind_param($checkStmt, "i", $Trade_Id);
mysqli_stmt_execute($checkStmt);

$checkResult = mysqli_stmt_get_result($checkStmt);
$traineeData = mysqli_fetch_assoc($checkResult);

mysqli_stmt_close($checkStmt);

if ($traineeData['total'] > 0) {
    $_SESSION['error'] = "This trade cannot be deleted because trainees are assigned to it. Reassign or remove those trainees first.";
    header("Location:listOfTrades.php");
    exit();
}

$stmt = mysqli_prepare($conn, "DELETE FROM trades WHERE Trade_id = ?");

if (!$stmt) {
    $_SESSION['error'] = "Unable to prepare trade deletion.";
    header("Location:listOfTrades.php");
    exit();
}

mysqli_stmt_bind_param($stmt, "i", $Trade_Id);

if (mysqli_stmt_execute($stmt)) {
    if (mysqli_stmt_affected_rows($stmt) > 0) {
        $_SESSION['success'] = "Trade deleted successfully.";
    } else {
        $_SESSION['error'] = "The selected trade was not found.";
    }
} else {
    $_SESSION['error'] = "Failed to delete trade. It may still be connected to other records.";
}

mysqli_stmt_close($stmt);

header("Location:listOfTrades.php");
exit();
?>
