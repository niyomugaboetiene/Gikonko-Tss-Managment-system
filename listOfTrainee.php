
<?php
include("conn.php");
session_start();

if (!isset($_SESSION['username'])) {
    $_SESSION['login_error'] = "Please login to access this page";
    header("Location:login.php");
    exit();
}

$sql = "SELECT
            t.Trainee_Id,
            t.Firstname,
            t.lastname,
            t.Gender,
            t.Trade_Id,
            tr.Trade_name
        FROM trainees t
        JOIN trades tr ON t.Trade_Id = tr.Trade_id
        ORDER BY t.Trainee_Id ASC";

$query = mysqli_query($conn, $sql);

if (!$query) {
    error_log("Trainees query failed: " . mysqli_error($conn));
    $_SESSION['error'] = "Unable to load trainee records.";
    header("Location:greetingUser.php");
    exit();
}

$totalTrainees = mysqli_num_rows($query);
$trainees = [];
$maleCount = 0;
$femaleCount = 0;

while ($data = mysqli_fetch_assoc($query)) {
    $trainees[] = $data;

    if (strtolower($data['Gender']) === 'male') {
        $maleCount++;
    } elseif (strtolower($data['Gender']) === 'female') {
        $femaleCount++;
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
    <title>Trainee Management | GIKONKO TSS</title>
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
                    Trainee Management
                </h1>

                <p class="text-sm text-slate-500 mt-2">
                    Manage trainee information and their registered trades.
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

                <a href="addTrainee.php"
                   class="inline-flex items-center justify-center px-4 py-2.5 bg-blue-700 text-white text-sm font-semibold rounded-md hover:bg-blue-800 transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add New Trainee
                </a>

            </div>

        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 mb-8">

            <div class="bg-white border border-slate-200 rounded-lg p-5 shadow-sm">

                <div class="flex items-center justify-between">

                    <div>
                        <p class="text-sm font-medium text-slate-500">
                            Total Trainees
                        </p>

                        <h2 class="text-3xl font-bold text-slate-800 mt-2">
                            <?php echo $totalTrainees; ?>
                        </h2>
                    </div>

                    <div class="w-11 h-11 rounded-md bg-blue-50 text-blue-700 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.824-1M17 20H7m10 0v-2c0-.656-.126-1.283-.355-1.857M7 20H2v-2a3 3 0 015.824-1M7 20v-2c0-.656.126-1.283.355-1.857m0 0a5.002 5.002 0 019.29 0M15 6a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 9a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>

                </div>

                <p class="text-xs text-slate-400 mt-3">
                    All registered trainees
                </p>

            </div>

            <div class="bg-white border border-slate-200 rounded-lg p-5 shadow-sm">

                <div class="flex items-center justify-between">

                    <div>
                        <p class="text-sm font-medium text-slate-500">
                            Male Trainees
                        </p>

                        <h2 class="text-3xl font-bold text-blue-700 mt-2">
                            <?php echo $maleCount; ?>
                        </h2>
                    </div>

                    <div class="w-11 h-11 rounded-md bg-blue-50 text-blue-700 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  stroke-width="2" d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2m8-10a4 4 0 100-8 4 4 0 000 8z"/>
                        </svg>
                    </div>

                </div>

                <p class="text-xs text-slate-400 mt-3">
                    Registered male trainees
                </p>

            </div>

            <div class="bg-white border border-slate-200 rounded-lg p-5 shadow-sm">

                <div class="flex items-center justify-between">

                    <div>
                        <p class="text-sm font-medium text-slate-500">
                            Female Trainees
                        </p>

                        <h2 class="text-3xl font-bold text-purple-700 mt-2">
                            <?php echo $femaleCount; ?>
                        </h2>
                    </div>

                    <div class="w-11 h-11 rounded-md bg-purple-50 text-purple-700 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  stroke-width="2" d="M12 15v6m-3-3h6m3-9a6 6 0 11-12 0 6 6 0 0112 0z"/>
                        </svg>
                    </div>

                </div>

                <p class="text-xs text-slate-400 mt-3">
                    Registered female trainees
                </p>

            </div>

        </div>

        <section class="bg-white border border-slate-200 rounded-lg shadow-sm overflow-hidden">

            <div class="p-5 sm:p-6 border-b border-slate-200">

                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

                    <div>
                        <h2 class="text-lg font-bold text-slate-800">
                            Registered Trainees
                        </h2>

                        <p class="text-sm text-slate-500 mt-1">
                            View and manage trainee records.
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
                            id="traineeSearch"
                            placeholder="Search name, code, gender or trade..."
                            class="w-full pl-10 pr-4 py-2.5 text-sm border border-slate-300 rounded-md outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >

                    </div>

                </div>

            </div>

            <div class="overflow-x-auto">

                <table class="w-full text-sm text-left whitespace-nowrap" id="traineeTable">

                    <thead class="bg-slate-50 text-slate-600 uppercase text-xs tracking-wider">
                        <tr>
                            <th class="px-5 py-4 font-semibold">Trainee Code</th>
                            <th class="px-5 py-4 font-semibold">Trainee Name</th>
                            <th class="px-5 py-4 font-semibold">Gender</th>
                            <th class="px-5 py-4 font-semibold">Trade</th>
                            <th class="px-5 py-4 font-semibold text-center">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">

                    <?php if ($totalTrainees > 0): ?>

                        <?php foreach ($trainees as $data): ?>

                            <tr class="trainee-row hover:bg-slate-50 transition">

                                <td class="px-5 py-4">
                                    <span class="font-semibold text-slate-700">
                                        #<?php echo e($data['Trainee_Id']); ?>
                                    </span>
                                </td>

                                <td class="px-5 py-4">

                                    <div class="flex items-center gap-3">

                                        <div class="w-10 h-10 rounded-full bg-blue-50 text-blue-700 flex items-center justify-center font-bold">
                                            <?php
                                            echo e(
                                                strtoupper(
                                                    substr($data['Firstname'], 0, 1)
                                                )
                                            );
                                            ?>
                                        </div>

                                        <div>
                                            <p class="font-semibold text-slate-800">
                                                <?php echo e($data['Firstname'] . ' ' . $data['lastname']); ?>
                                            </p>

                                            <p class="text-xs text-slate-500 mt-1">
                                                Trainee ID: <?php echo e($data['Trainee_Id']); ?>
                                            </p>
                                        </div>

                                    </div>

                                </td>

                                <td class="px-5 py-4">

                                    <?php if (strtolower($data['Gender']) === 'male'): ?>

                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700">
                                            Male
                                        </span>

                                    <?php elseif (strtolower($data['Gender']) === 'female'): ?>

                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-purple-50 text-purple-700">
                                            Female
                                        </span>

                                    <?php else: ?>

                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600">
                                            <?php echo e($data['Gender']); ?>
                                        </span>

                                    <?php endif; ?>

                                </td>

                                <td class="px-5 py-4">

                                    <p class="font-medium text-slate-800">
                                        <?php echo e($data['Trade_name']); ?>
                                    </p>

                                    <p class="text-xs text-slate-500 mt-1">
                                        Trade ID: <?php echo e($data['Trade_Id']); ?>
                                    </p>

                                </td>

                                <td class="px-5 py-4">

                                    <div class="flex items-center justify-center gap-2">

                                        <a href="updateTrainee.php?Trainee_Id=<?php echo urlencode($data['Trainee_Id']); ?>"
                                           class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-blue-700 bg-blue-50 rounded-md hover:bg-blue-100 transition">
                                            Update
                                        </a>

                                        <a href="deleteTrainee.php?Trainee_Id=<?php echo urlencode($data['Trainee_Id']); ?>"
                                           onclick="return confirm('Are you sure you want to delete this trainee? Their related marks may also be deleted.')"
                                           class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-red-700 bg-red-50 rounded-md hover:bg-red-100 transition">
                                            Delete
                                        </a>

                                    </div>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="5" class="px-6 py-14 text-center">

                                <div class="flex flex-col items-center">

                                    <div class="w-14 h-14 bg-slate-100 rounded-full flex items-center justify-center text-slate-400">
                                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.824-1M17 20H7m10 0v-2c0-.656-.126-1.283-.355-1.857M7 20H2v-2a3 3 0 015.824-1M7 20v-2c0-.656.126-1.283.355-1.857m0 0a5.002 5.002 0 019.29 0M15 6a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                    </div>

                                    <h3 class="text-base font-semibold text-slate-800 mt-4">
                                        No trainees found
                                    </h3>

                                    <p class="text-sm text-slate-500 mt-1">
                                        There are currently no registered trainees.
                                    </p>

                                    <a href="addTrainee.php"
                                       class="mt-4 text-sm font-semibold text-blue-700 hover:underline">
                                        Register your first trainee
                                    </a>

                                </div>

                            </td>
                        </tr>

                    <?php endif; ?>

                    <tr id="noSearchResults" class="hidden">
                        <td colspan="5" class="px-6 py-8 text-center text-slate-500">
                            No matching trainees found.
                        </td>
                    </tr>

                    </tbody>

                </table>

            </div>

            <div class="px-5 py-4 border-t border-slate-200 bg-slate-50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">

                <p class="text-xs text-slate-500">
                    Showing <span id="visibleCount"><?php echo $totalTrainees; ?></span>
                    of <?php echo $totalTrainees; ?> trainees
                </p>

                <p class="text-xs text-slate-500">
                    GIKONKO TSS · Trainee Management
                </p>

            </div>

        </section>

    </div>

</main>

<?php include("footer.php"); ?>

<script>
const searchInput = document.getElementById("traineeSearch");
const rows = document.querySelectorAll(".trainee-row");
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
