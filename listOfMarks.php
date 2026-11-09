
<?php
include("conn.php");
session_start();

if (!isset($_SESSION['username'])) {
    $_SESSION['login_error'] = "Please login to access this page";
    header("Location:login.php");
    exit();
}

$sql = "SELECT
            m.Mark_Id,
            m.Trainee_Id,
            CONCAT(t.Firstname, ' ', t.lastname) AS Trainee_Name,
            m.Module_Id,
            md.Module_Name,
            m.Formative_Assessment,
            m.Summative_Assessment,
            m.Total_Marks,
            m.Decision
        FROM marks m
        JOIN trainees t ON m.Trainee_Id = t.Trainee_Id
        JOIN modules md ON m.Module_Id = md.Module_Id
        ORDER BY m.Trainee_Id ASC";

$query = mysqli_query($conn, $sql);

if (!$query) {
    error_log("Marks query failed: " . mysqli_error($conn));
    $_SESSION['error'] = "Unable to load marks records.";
    header("Location:greetingUser.php");
    exit();
}

$totalRecords = mysqli_num_rows($query);
$competentCount = 0;
$notCompetentCount = 0;

$records = [];

while ($data = mysqli_fetch_assoc($query)) {
    $records[] = $data;

    if ((float)$data['Total_Marks'] >= 70) {
        $competentCount++;
    } else {
        $notCompetentCount++;
    }
}

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Marks Management | GIKONKO TSS</title>
    <link href="output.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="gikonko.png">
</head>

<body class="min-h-screen bg-slate-50 flex flex-col">

<header class="w-full fixed top-0 left-0 z-50 bg-white border-b border-slate-200 shadow-sm">
    <?php include("Dashboard.php"); ?>
</header>

<main class="flex-grow pt-28 pb-12 px-4 sm:px-6 lg:px-8">

    <div class="max-w-7xl mx-auto">

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">

            <div>
                <p class="text-sm font-medium text-blue-700 uppercase tracking-wider">
                    Academic Administration
                </p>

                <h1 class="text-2xl sm:text-3xl font-bold text-slate-800 mt-2">
                    Marks Management
                </h1>

                <p class="text-sm text-slate-500 mt-2">
                    View and manage trainee assessment records.
                </p>
            </div>

            <div class="flex flex-wrap gap-3">
                <a href="greetingUser.php"
                   class="inline-flex items-center justify-center px-4 py-2.5 border border-slate-300 bg-white text-slate-700 text-sm font-semibold rounded-md hover:bg-slate-50 transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Back
                </a>

                <a href="addMarks.php"
                   class="inline-flex items-center justify-center px-4 py-2.5 bg-blue-700 text-white text-sm font-semibold rounded-md hover:bg-blue-800 transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add New Marks
                </a>
            </div>

        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 mb-8">

            <div class="bg-white border border-slate-200 rounded-lg p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-500 font-medium">Total Assessments</p>
                        <h2 class="text-3xl font-bold text-slate-800 mt-2">
                            <?php echo $totalRecords; ?>
                        </h2>
                    </div>

                    <div class="w-11 h-11 rounded-md bg-blue-50 text-blue-700 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-xs text-slate-400 mt-3">All recorded trainee assessments</p>
            </div>

            <div class="bg-white border border-slate-200 rounded-lg p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-500 font-medium">Competent</p>
                        <h2 class="text-3xl font-bold text-green-700 mt-2">
                            <?php echo $competentCount; ?>
                        </h2>
                    </div>

                    <div class="w-11 h-11 rounded-md bg-green-50 text-green-700 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                </div>
                <p class="text-xs text-slate-400 mt-3">Assessments with 70 marks or above</p>
            </div>

            <div class="bg-white border border-slate-200 rounded-lg p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-500 font-medium">Not Competent</p>
                        <h2 class="text-3xl font-bold text-red-700 mt-2">
                            <?php echo $notCompetentCount; ?>
                        </h2>
                    </div>

                    <div class="w-11 h-11 rounded-md bg-red-50 text-red-700 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </div>
                </div>
                <p class="text-xs text-slate-400 mt-3">Assessments below 70 marks</p>
            </div>

        </div>

        <section class="bg-white border border-slate-200 rounded-lg shadow-sm overflow-hidden">

            <div class="p-5 sm:p-6 border-b border-slate-200">

                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

                    <div>
                        <h2 class="text-lg font-bold text-slate-800">
                            Assessment Records
                        </h2>

                        <p class="text-sm text-slate-500 mt-1">
                            A complete list of trainee marks and decisions.
                        </p>
                    </div>

                    <div class="relative w-full md:w-80">
                        <svg class="w-5 h-5 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <circle cx="11" cy="11" r="7" stroke-width="2"/>
                            <path stroke-linecap="round" stroke-width="2" d="m16 16 4 4"/>
                        </svg>

                        <input
                            type="text"
                            id="marksSearch"
                            placeholder="Search trainee, module or mark code..."
                            class="w-full pl-10 pr-4 py-2.5 text-sm border border-slate-300 rounded-md outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                    </div>

                </div>

            </div>

            <div class="overflow-x-auto">

                <table class="w-full text-sm text-left whitespace-nowrap" id="marksTable">

                    <thead class="bg-slate-50 text-slate-600 uppercase text-xs tracking-wider">
                        <tr>
                            <th class="px-5 py-4 font-semibold">Mark Code</th>
                            <th class="px-5 py-4 font-semibold">Trainee</th>
                            <th class="px-5 py-4 font-semibold">Module</th>
                            <th class="px-5 py-4 font-semibold text-center">Formative</th>
                            <th class="px-5 py-4 font-semibold text-center">Summative</th>
                            <th class="px-5 py-4 font-semibold text-center">Total</th>
                            <th class="px-5 py-4 font-semibold text-center">Decision</th>
                            <th class="px-5 py-4 font-semibold text-center">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">

                    <?php if ($totalRecords > 0): ?>

                        <?php foreach ($records as $data): ?>

                            <?php
                            $isCompetent = (float)$data['Total_Marks'] >= 70;
                            ?>

                            <tr class="marks-row hover:bg-slate-50 transition">

                                <td class="px-5 py-4 font-semibold text-slate-700">
                                    #<?php echo e($data['Mark_Id']); ?>
                                </td>

                                <td class="px-5 py-4">
                                    <p class="font-semibold text-slate-800">
                                        <?php echo e($data['Trainee_Name']); ?>
                                    </p>
                                    <p class="text-xs text-slate-500 mt-1">
                                        Trainee ID: <?php echo e($data['Trainee_Id']); ?>
                                    </p>
                                </td>

                                <td class="px-5 py-4">
                                    <p class="font-medium text-slate-800">
                                        <?php echo e($data['Module_Name']); ?>
                                    </p>
                                    <p class="text-xs text-slate-500 mt-1">
                                        Module ID: <?php echo e($data['Module_Id']); ?>
                                    </p>
                                </td>

                                <td class="px-5 py-4 text-center text-slate-600">
                                    <?php echo e($data['Formative_Assessment']); ?>/50
                                </td>

                                <td class="px-5 py-4 text-center text-slate-600">
                                    <?php echo e($data['Summative_Assessment']); ?>/50
                                </td>

                                <td class="px-5 py-4 text-center">
                                    <span class="font-bold text-slate-800">
                                        <?php echo e($data['Total_Marks']); ?>/100
                                    </span>
                                </td>

                                <td class="px-5 py-4 text-center">
                                    <?php if ($isCompetent): ?>
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-green-50 text-green-700">
                                            Competent
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-red-50 text-red-700">
                                            Not Competent
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <td class="px-5 py-4">
                                    <div class="flex items-center justify-center gap-2">

                                        <a href="updateMarks.php?Mark_Id=<?php echo urlencode($data['Mark_Id']); ?>"
                                           class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-blue-700 bg-blue-50 rounded-md hover:bg-blue-100 transition">
                                            Update
                                        </a>

                                        <a href="deleteMarks.php?Mark_Id=<?php echo urlencode($data['Mark_Id']); ?>"
                                           onclick="return confirm('Are you sure you want to delete these marks?')"
                                           class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-red-700 bg-red-50 rounded-md hover:bg-red-100 transition">
                                            Delete
                                        </a>

                                    </div>
                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="8" class="px-5 py-14 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-14 h-14 bg-slate-100 rounded-full flex items-center justify-center text-slate-400">
                                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </div>

                                    <h3 class="text-base font-semibold text-slate-800 mt-4">
                                        No marks records found
                                    </h3>

                                    <p class="text-sm text-slate-500 mt-1">
                                        Start by adding the first assessment record.
                                    </p>

                                    <a href="addMarks.php"
                                       class="mt-4 text-sm font-semibold text-blue-700 hover:underline">
                                        Add marks
                                    </a>
                                </div>
                            </td>
                        </tr>

                    <?php endif; ?>

                    <tr id="noSearchResults" class="hidden">
                        <td colspan="8" class="px-5 py-8 text-center text-slate-500">
                            No matching assessment records found.
                        </td>
                    </tr>

                    </tbody>

                </table>

            </div>

            <div class="px-5 py-4 border-t border-slate-200 bg-slate-50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <p class="text-xs text-slate-500">
                    Showing <span id="visibleCount"><?php echo $totalRecords; ?></span>
                    of <?php echo $totalRecords; ?> assessment records
                </p>

                <p class="text-xs text-slate-500">
                    GIKONKO TSS · Marks Management
                </p>
            </div>

        </section>

    </div>

</main>

<?php include("footer.php"); ?>

<script>
const searchInput = document.getElementById("marksSearch");
const rows = document.querySelectorAll(".marks-row");
const visibleCount = document.getElementById("visibleCount");
const noSearchResults = document.getElementById("noSearchResults");

searchInput.addEventListener("input", function () {
    const searchValue = this.value.toLowerCase().trim();
    let count = 0;

    rows.forEach(function (row) {
        const matches = row.textContent.toLowerCase().includes(searchValue);

        row.classList.toggle("hidden", !matches);

        if (matches) {
            count++;
        }
    });

    visibleCount.textContent = count;
    noSearchResults.classList.toggle("hidden", count !== 0 || rows.length === 0);
});
</script>

</body>
</html>
