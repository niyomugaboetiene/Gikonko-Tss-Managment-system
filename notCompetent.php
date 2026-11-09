
<?php
include("conn.php");
session_start();

if (!isset($_SESSION['username'])) {
    $_SESSION['login_error'] = "Please login to access this page";
    header("Location: login.php");
    exit();
}

$sql = "SELECT
            m.Trainee_Id,
            CONCAT(t.Firstname, ' ', t.lastname) AS Trainee_Name,
            m.Module_Id,
            md.Module_Name,
            m.Decision,
            m.Total_Marks,
            td.Trade_name
        FROM marks m
        JOIN trainees t ON t.Trainee_Id = m.Trainee_Id
        JOIN modules md ON m.Module_Id = md.Module_Id
        JOIN trades td ON td.Trade_id = t.Trade_id
        WHERE m.Total_Marks < 70
        ORDER BY t.Firstname ASC, md.Module_Name ASC";

$query = mysqli_query($conn, $sql);

if (!$query) {
    die("Unable to retrieve trainee records.");
}

$totalRecords = mysqli_num_rows($query);

function escapeOutput($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Not Yet Competent Trainees | GIKONKO TSS</title>
    <link href="output.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="gikonko.png">
</head>

<body class="min-h-screen bg-slate-50 flex flex-col">

    <header class="w-full sticky top-0 z-50 bg-white border-b border-slate-200 shadow-sm">
        <?php include("Dashboard.php"); ?>
    </header>

    <main class="flex-grow w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
            <div>
                <p class="text-sm font-semibold text-blue-700 uppercase tracking-wide">
                    Academic Performance
                </p>
                <h1 class="text-2xl sm:text-3xl font-bold text-slate-800 mt-1">
                    Not Yet Competent Trainees
                </h1>
                <p class="text-sm text-slate-500 mt-2">
                    Assessment records for trainees who scored below 70 marks.
                </p>
            </div>

            <a href="greetingUser.php"
               class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-white border border-slate-300 text-slate-700 font-medium rounded-md hover:bg-slate-100 transition">
                <span>&larr;</span>
                Back to Dashboard
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">

            <div class="bg-white border border-slate-200 rounded-lg p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">
                            Not Competent Records
                        </p>
                        <p class="text-3xl font-bold text-red-600 mt-2">
                            <?php echo $totalRecords; ?>
                        </p>
                    </div>

                    <div class="w-11 h-11 flex items-center justify-center rounded-lg bg-red-50 text-red-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v4m0 4h.01M10.3 3.9L1.8 18a2 2 0 001.7 3h17a2 2 0 001.7-3L13.7 3.9a2 2 0 00-3.4 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white border border-slate-200 rounded-lg p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">
                            Required Competency Mark
                        </p>
                        <p class="text-3xl font-bold text-slate-800 mt-2">
                            70%
                        </p>
                    </div>

                    <div class="w-11 h-11 flex items-center justify-center rounded-lg bg-blue-50 text-blue-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

        </div>

        <section class="bg-white border border-slate-200 rounded-lg shadow-sm overflow-hidden">

            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 p-5 border-b border-slate-200">
                <div>
                    <h2 class="text-lg font-semibold text-slate-800">
                        Trainee Assessment Records
                    </h2>
                    <p class="text-sm text-slate-500 mt-1">
                        Review and download the assessment results.
                    </p>
                </div>

                <a href="download_not_competent.php"
                   class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-blue-700 text-white text-sm font-semibold rounded-md hover:bg-blue-800 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"/>
                    </svg>
                    Download CSV
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">

                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-5 py-4 text-left font-semibold text-slate-600 whitespace-nowrap">Trainee Code</th>
                            <th class="px-5 py-4 text-left font-semibold text-slate-600 whitespace-nowrap">Trainee Name</th>
                            <th class="px-5 py-4 text-left font-semibold text-slate-600 whitespace-nowrap">Module Code</th>
                            <th class="px-5 py-4 text-left font-semibold text-slate-600 whitespace-nowrap">Module Name</th>
                            <th class="px-5 py-4 text-left font-semibold text-slate-600 whitespace-nowrap">Trade Name</th>
                            <th class="px-5 py-4 text-center font-semibold text-slate-600 whitespace-nowrap">Total Marks</th>
                            <th class="px-5 py-4 text-center font-semibold text-slate-600 whitespace-nowrap">Decision</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">

                        <?php if ($totalRecords > 0): ?>

                            <?php while ($data = mysqli_fetch_assoc($query)): ?>

                                <tr class="hover:bg-slate-50 transition">

                                    <td class="px-5 py-4 font-medium text-slate-700 whitespace-nowrap">
                                        <?php echo escapeOutput($data['Trainee_Id']); ?>
                                    </td>

                                    <td class="px-5 py-4 text-slate-800 whitespace-nowrap">
                                        <?php echo escapeOutput($data['Trainee_Name']); ?>
                                    </td>

                                    <td class="px-5 py-4 text-slate-600 whitespace-nowrap">
                                        <?php echo escapeOutput($data['Module_Id']); ?>
                                    </td>

                                    <td class="px-5 py-4 text-slate-700 whitespace-nowrap">
                                        <?php echo escapeOutput($data['Module_Name']); ?>
                                    </td>

                                    <td class="px-5 py-4 text-slate-600 whitespace-nowrap">
                                        <?php echo escapeOutput($data['Trade_name']); ?>
                                    </td>

                                    <td class="px-5 py-4 text-center font-semibold text-slate-800">
                                        <?php echo escapeOutput($data['Total_Marks']); ?>/100
                                    </td>

                                    <td class="px-5 py-4 text-center whitespace-nowrap">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-50 text-red-700 border border-red-100">
                                            <?php echo escapeOutput($data['Decision']); ?>
                                        </span>
                                    </td>

                                </tr>

                            <?php endwhile; ?>

                        <?php else: ?>

                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center">
                                        <div class="w-12 h-12 rounded-full bg-green-50 text-green-600 flex items-center justify-center mb-3">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </div>

                                        <p class="text-slate-800 font-semibold">
                                            No records found
                                        </p>

                                        <p class="text-sm text-slate-500 mt-1">
                                            There are currently no trainees below the competency threshold.
                                        </p>
                                    </div>
                                </td>
                            </tr>

                        <?php endif; ?>

                    </tbody>
                </table>
            </div>

            <div class="px-5 py-4 bg-slate-50 border-t border-slate-200">
                <p class="text-xs text-slate-500">
                    Showing <?php echo $totalRecords; ?> assessment record(s).
                </p>
            </div>

        </section>

    </main>

    <?php include("footer.php"); ?>

</body>
</html>
