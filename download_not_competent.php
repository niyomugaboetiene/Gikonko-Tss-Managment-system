
<?php
include("conn.php");
session_start();

if (!isset($_SESSION['username'])) {
    $_SESSION['login_error'] = "Please login to access this page";
    header("Location:index.php");
    exit();
}

mysqli_set_charset($conn, "utf8mb4");

$sql = "SELECT
            m.Trainee_Id,
            CONCAT(t.Firstname, ' ', t.lastname) AS Trainee_Name,
            m.Module_Id,
            md.Module_Name,
            td.Trade_name,
            m.Decision,
            m.Total_Marks
        FROM marks m
        JOIN trainees t ON t.Trainee_Id = m.Trainee_Id
        JOIN modules md ON md.Module_Id = m.Module_Id
        JOIN trades td ON td.Trade_id = t.Trade_id
        WHERE m.Total_Marks < 70
        ORDER BY t.Firstname ASC";

$query = mysqli_query($conn, $sql);

if (!$query) {
    error_log("Not competent CSV export error: " . mysqli_error($conn));
    $_SESSION['error'] = "Unable to generate the not competent trainees report. Please try again.";
    header("Location:notCompetent.php");
    exit();
}

function protectCsvValue($value) {
    $value = (string) $value;

    if (preg_match('/^[\s]*[=+\-@]/', $value)) {
        return "'" . $value;
    }

    return $value;
}

if (ob_get_length()) {
    ob_end_clean();
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="not_competent_trainees_' . date('Y-m-d') . '.csv"');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

$output = fopen("php://output", "w");

if ($output === false) {
    exit();
}

fwrite($output, "\xEF\xBB\xBF");

fputcsv($output, [
    'Trainee Code',
    'Trainee Name',
    'Module Code',
    'Module Name',
    'Trade Name',
    'Decision'
], ',', '"', '');

while ($data = mysqli_fetch_assoc($query)) {
    fputcsv($output, [
        protectCsvValue($data['Trainee_Id']),
        protectCsvValue($data['Trainee_Name']),
        protectCsvValue($data['Module_Id']),
        protectCsvValue($data['Module_Name']),
        protectCsvValue($data['Trade_name']),
        protectCsvValue($data['Decision'])
    ], ',', '"', '');
}

fclose($output);
mysqli_free_result($query);
exit();
?>
