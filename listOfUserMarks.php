
<?php
include("conn.php");
session_start();

if (!isset($_SESSION['Firstname']) || !isset($_SESSION['Trainee_Id'])) {
    $_SESSION['login_error'] = "Please login to access this page";
    header("Location:login.php");
    exit();
}

$Trainee_Id = $_SESSION['Trainee_Id'];

$sql = "SELECT
            t.Trainee_Id,
            CONCAT(t.Firstname, ' ', t.lastname) AS Trainee_Name,
            m.Module_Id,
            md.Module_Name,
            m.Formative_Assessment,
            m.Summative_Assessment,
            m.Total_Marks,
            m.decision
        FROM trainees t
        JOIN marks m ON t.Trainee_Id = m.Trainee_Id
        JOIN modules md ON m.Module_Id = md.Module_Id
        WHERE t.Trainee_Id = ?
        ORDER BY md.Module_Name ASC";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    error_log("Student marks prepare failed: " . mysqli_error($conn));
    $_SESSION['error'] = "Unable to retrieve your marks.";
    header("Location:userPage.php");
    exit();
}

mysqli_stmt_bind_param($stmt, "i", $Trainee_Id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (!$result) {
    error_log("Student marks query failed: " . mysqli_stmt_error($stmt));
    $_SESSION['error'] = "Unable to retrieve your marks.";
    header("Location:userPage.php");
    exit();
}

$marks = [];
$totalRecords = 0;
$competentCount = 0;
$notCompetentCount = 0;
$totalMarks = 0;

while ($data = mysqli_fetch_assoc($result)) {
    $marks[] = $data;
    $totalRecords++;
    $totalMarks += (float)$data['Total_Marks'];

    if ((float)$data['Total_Marks'] >= 70) {
        $competentCount++;
    } else {
        $notCompetentCount++;
    }
}

$averageMarks = $totalRecords > 0 ? $totalMarks / $totalRecords : 0;

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

mysqli_stmt_close($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Academic Results | GIKONKO TSS</title>
    <link href="output.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="gikonko.png">
</head>

<body class="min-h-screen bg-slate-50 flex flex-col">

<header class="w-full fixed top-0 left-0 z-50 bg-white border-b border-slate-200 shadow-sm">
    <?php include("userDashboard.php"); ?>
</header>

<main class="flex-grow pt-28 pb-12 px-4 sm:px-6 lg:px-8">

    <div class="max-w-7xl mx-auto">

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">

            <div>
                <p class="text-sm font-medium text-blue-700 uppercase tracking-wider">
                    Student Academic Portal
                </p>

                <h1 class="text-2xl sm:text-3xl font-bold text-slate-800 mt-2">
                    My Academic Results
                </h1>

                <p class="text-sm text-slate-500 mt-2">
                    View your assessment marks and competency results.
                </p>
            </div>

            <a href="userPage.php"
               class="inline-flex items-center justify-center px-4 py-2.5 border border-slate-300 bg-white text-slate-700 text-sm font-semibold rounded-md hover:bg-slate-50 transition">

                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>

                Back to Dashboard
            </a>

        </div>

        <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-5 sm:p-6 mb-8">

            <div class="flex flex-col sm:flex-row sm:items-center gap-4">

                <div class="w-14 h-14 rounded-full bg-blue-50 text-blue-700 flex items-center justify-center text-xl font-bold">
                    <?php echo e(strtoupper(substr($_SESSION['Firstname'], 0, 1))); ?>
                </div>

                <div>
                    <p class="text-xs uppercase tracking-wider font-semibold text-slate-500">
                        Student Profile
                    </p>

                    <h2 class="text-xl font-bold text-slate-800 mt-1">
                        <?php echo e($_SESSION['Firstname']); ?>
                    </h2>

                    <p class="text-sm text-slate-500 mt-1">
                        Trainee Code: #<?php echo e($Trainee_Id); ?>
                    </p>
                </div>

            </div>

        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">

            <div class="bg-white border border-slate-200 rounded-lg p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">
                    Modules Assessed
                </p>

                <h2 class="text-3xl font-bold text-slate-800 mt-2">
                    <?php echo $totalRecords; ?>
                </h2>

                <p class="text-xs text-slate-400 mt-3">
                    Available assessment records
                </p>
            </div>

            <div class="bg-white border border-slate-200 rounded-lg p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">
                    Average Marks
                </p>

                <h2 class="text-3xl font-bold text-blue-700 mt-2">
                    <?php echo number_format($averageMarks, 1); ?>%
                </h2>

                <p class="text-xs text-slate-400 mt-3">
                    Average across assessed modules
                </p>
            </div>

            <div class="bg-white border border-slate-200 rounded-lg p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">
                    Competent
                </p>

                <h2 class="text-3xl font-bold text-emerald-700 mt-2">
                    <?php echo $competentCount; ?>
                </h2>

                <p class="text-xs text-slate-400 mt-3">
                    Modules with 70 marks or above
                </p>
            </div>

            <div class="bg-white border border-slate-200 rounded-lg p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">
                    Not Competent
                </p>

                <h2 class="text-3xl font-bold text-red-600 mt-2">
                    <?php echo $notCompetentCount; ?>
                </h2>

                <p class="text-xs text-slate-400 mt-3">
                    Modules below 70 marks
                </p>
            </div>

        </div>

        <section class="bg-white border border-slate-200 rounded-lg shadow-sm overflow-hidden">

            <div class="p-5 sm:p-6 border-b border-slate-200">

                <div>
                    <h2 class="text-lg font-bold text-slate-800">
                        Assessment Records
                    </h2>

                    <p class="text-sm text-slate-500 mt-1">
                        Your formative, summative and final assessment results.
                    </p>
                </div>

            </div>

            <div class="overflow-x-auto">

                <table class="w-full text-sm text-left whitespace-nowrap">

                    <thead class="bg-slate-50 text-slate-600 uppercase text-xs tracking-wider">

                        <tr>
                            <th class="px-5 py-4 font-semibold">Module Code</th>
                            <th class="px-5 py-4 font-semibold">Module Name</th>
                            <th class="px-5 py-4 font-semibold text-center">Formative /50</th>
                            <th class="px-5 py-4 font-semibold text-center">Summative /50</th>
                            <th class="px-5 py-4 font-semibold text-center">Total</th>
                            <th class="px-5 py-4 font-semibold text-center">Decision</th>
                        </tr>

                    </thead>

                    <tbody class="divide-y divide-slate-100">

                    <?php if ($totalRecords > 0): ?>

                        <?php foreach ($marks as $data): ?>

                            <tr class="hover:bg-slate-50 transition">

                                <td class="px-5 py-4 font-semibold text-slate-700">
                                    #<?php echo e($data['Module_Id']); ?>
                                </td>

                                <td class="px-5 py-4">
                                    <p class="font-semibold text-slate-800">
                                        <?php echo e($data['Module_Name']); ?>
                                    </p>
                                </td>

                                <td class="px-5 py-4 text-center text-slate-600">
                                    <?php echo e($data['Formative_Assessment']); ?>
                                </td>

                                <td class="px-5 py-4 text-center text-slate-600">
                                    <?php echo e($data['Summative_Assessment']); ?>
                                </td>

                                <td class="px-5 py-4 text-center">
                                    <span class="font-bold text-slate-800">
                                        <?php echo e($data['Total_Marks']); ?>/100
                                    </span>
                                </td>

                                <td class="px-5 py-4 text-center">

                                    <?php if ((float)$data['Total_Marks'] >= 70): ?>

                                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700">
                                            Competent
                                        </span>

                                    <?php else: ?>

                                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-red-50 text-red-700">
                                            Not Competent
                                        </span>

                                    <?php endif; ?>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center">

                                <div class="flex flex-col items-center">

                                    <div class="w-14 h-14 rounded-full bg-slate-100 flex items-center justify-center text-slate-400">

                                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>

                                    </div>

                                    <h3 class="text-base font-semibold text-slate-800 mt-4">
                                        No assessment results yet
                                    </h3>

                                    <p class="text-sm text-slate-500 mt-1">
                                        Your marks will appear here once they have been recorded.
                                    </p>

                                </div>

                            </td>
                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

            <div class="px-5 py-4 border-t border-slate-200 bg-slate-50 flex flex-col sm:flex-row sm:justify-between gap-2">

                <p class="text-xs text-slate-500">
                    Showing <?php echo $totalRecords; ?> assessment records
                </p>

                <p class="text-xs text-slate-500">
                    GIKONKO TSS · Student Academic Portal
                </p>

            </div>

        </section>

    </div>

</main>

<?php include("footer.php"); ?>

</body>
</html>
