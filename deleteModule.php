
<?php
include("conn.php");
session_start();

if (!isset($_SESSION['username'])) {
    $_SESSION['login_error'] = "Please login to access this page";
    header("Location:index.php");
    exit();
}

if (!isset($_GET['Module_Id']) || !ctype_digit($_GET['Module_Id'])) {
    $_SESSION['error'] = "Invalid module selected.";
    header("Location:listOfModule.php");
    exit();
}

$Module_Id = (int) $_GET['Module_Id'];

mysqli_begin_transaction($conn);

try {
    $stmtMarks = mysqli_prepare($conn, "DELETE FROM marks WHERE Module_Id = ?");

    if (!$stmtMarks) {
        throw new Exception("Unable to prepare marks deletion.");
    }

    mysqli_stmt_bind_param($stmtMarks, "i", $Module_Id);

    if (!mysqli_stmt_execute($stmtMarks)) {
        throw new Exception("Unable to delete related marks.");
    }

    mysqli_stmt_close($stmtMarks);

    $stmtModule = mysqli_prepare($conn, "DELETE FROM modules WHERE Module_Id = ?");

    if (!$stmtModule) {
        throw new Exception("Unable to prepare module deletion.");
    }

    mysqli_stmt_bind_param($stmtModule, "i", $Module_Id);

    if (!mysqli_stmt_execute($stmtModule)) {
        throw new Exception("Unable to delete the module.");
    }

    $deleted = mysqli_stmt_affected_rows($stmtModule);
    mysqli_stmt_close($stmtModule);

    if ($deleted === 0) {
        throw new Exception("The selected module was not found.");
    }

    mysqli_commit($conn);

    $_SESSION['success'] = "Module and its related marks deleted successfully.";

} catch (Throwable $e) {
    mysqli_rollback($conn);
    $_SESSION['error'] = $e->getMessage();
}

header("Location: listOfModule.php");
exit();
?>
