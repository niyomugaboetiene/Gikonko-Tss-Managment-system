
<?php
include("conn.php");
session_start();

if (!isset($_SESSION['username'])) {
    $_SESSION['login_error'] = "Please login to access this page";
    header("Location:login.php");
    exit();
}

$sql = "SELECT 
            m.Trainee_Id,
            CONCAT(t.Firstname, ' ', t.lastname) AS Trainee_Name,
            m.Module_Id,
            md.Module_Name,
            m.decision,
            m.Total_Marks,
            td.Trade_name
        FROM marks m
        JOIN trainees t ON t.Trainee_Id = m.Trainee_Id
        JOIN modules md ON m.Module_Id = md.Module_Id
        JOIN trades td ON td.Trade_id = t.Trade_id
        WHERE m.Total_Marks >= 70
        ORDER BY t.Firstname ASC";

$query = mysqli_query($conn, $sql);

if (!$query) {
    die("Unable to retrieve competent trainees: " . htmlspecialchars(mysqli_error($conn)));
}

$totalCompetent = mysqli_num_rows($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Competent Trainees | GIKONKO TSS</title>
    <link href="output.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="gikonko.png">
</head>

<body class="min-h-screen bg-slate-50 flex flex-col">

<header class="w-full fixed top-0 left-0 z-50 bg-white border-b border-slate-200 h-16 flex items-center">
    <?php include("Dashboard.php"); ?>
</header>

<main class="flex-grow pt-24 pb-10 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
            <div>
                <p class="text-sm font-medium text-blue-700 mb-2">ACADEMIC RECORDS</p>
                <h1 class="text-2xl sm:text-3xl font-bold text-slate-800">
                    Competent Trainees
                </h1>
                <p class="text-sm text-slate-500 mt-2">
                    View trainees who have achieved the required assessment marks.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <a href="greetingUser.php"
                   class="inline-flex items-center justify-center px-4 py-2.5 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-md hover:bg-slate-50 transition">
                    <span class="mr-2">&larr;</span>
                    Back
                </a>

                <a href="download_competent.php"
                   class="inline-flex items-center justify-center px-4 py-2.5 text-sm font-medium text-white bg-blue-700 rounded-md hover:bg-blue-800 transition shadow-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 3v12m0 0l4-4m-4 4l-4-4M5 17v3h14v-3"/>
                    </svg>
                    Download List
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <div class="bg-white border border-slate-200 rounded-lg p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-500 font-medium">Competent Records</p>
                        <h2 class="text-3xl font-bold text-slate-800 mt-2">
                            <?php echo $totalCompetent; ?>
                        </h2>
                    </div>
                    <div class="w-11 h-11 flex items-center justify-center bg-green-50 text-green-700 rounded-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                </div>
                <p class="text-xs text-slate-400 mt-3">Assessment results with 70 marks or above</p>
            </div>

            <div class="bg-white border border-slate-200 rounded-lg p-5">
                <p class="text-sm text-slate-500 font-medium">Required Marks</p>
                <h2 class="text-3xl font-bold text-slate-800 mt-2">70+</h2>
                <p class="text-xs text-slate-400 mt-3">Minimum marks for competency</p>
            </div>

            <div class="bg-white border border-slate-200 rounded-lg p-5">
                <p class="text-sm text-slate-500 font-medium">Academic Status</p>
                <div class="mt-3">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-50 text-green-700">
                        Competent
                    </span>
                </div>
                <p class="text-xs text-slate-400 mt-3">Trainees meeting the required marks</p>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-lg shadow-sm overflow-hidden">

            <div class="px-5 py-4 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div>
                    <h2 class="text-lg font-semibold text-slate-800">Competency Results</h2>
                    <p class="text-sm text-slate-500 mt-1">List of competent trainees by module.</p>
                </div>

                <span class="text-sm text-slate-500">
                    Total records:
                    <span class="font-semibold text-slate-800"><?php echo $totalCompetent; ?></span>
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Trainee Code</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Trainee Name</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Module Code</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Module Name</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Trade Name</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Decision</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        <?php if ($totalCompetent > 0): ?>
                            <?php while ($data = mysqli_fetch_assoc($query)): ?>
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-700">
                                        <?php echo htmlspecialchars($data['Trainee_Id']); ?>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-800">
                                        <?php echo htmlspecialchars($data['Trainee_Name']); ?>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                        <?php echo htmlspecialchars($data['Module_Id']); ?>
                                    </td>

                                    <td class="px-6 py-4 text-sm text-slate-600">
                                        <?php echo htmlspecialchars($data['Module_Name']); ?>
                                    </td>

                                    <td class="px-6 py-4 text-sm text-slate-600">
                                        <?php echo htmlspecialchars($data['Trade_name']); ?>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-50 text-green-700">
                                            <?php echo htmlspecialchars($data['decision']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center">
                                        <div class="w-12 h-12 flex items-center justify-center bg-slate-100 rounded-full mb-3">
                                            <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </div>
                                        <p class="text-slate-700 font-semibold">No competent trainees found</p>
                                        <p class="text-sm text-slate-500 mt-1">Competent assessment records will appear here.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="px-5 py-4 border-t border-slate-200 bg-white">
                <p class="text-xs text-slate-500">
                    GIKONKO TSS · Student Management System
                </p>
            </div>
        </div>

    </div>
</main>

<?php include("footer.php"); ?>

</body>
</html>
