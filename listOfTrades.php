
<?php
include("conn.php");
session_start();

if (!isset($_SESSION['username'])) {
    $_SESSION['login_error'] = "Please login to access this page";
    header("Location:login.php");
    exit();
}

$sql = "SELECT Trade_id, Trade_name
        FROM trades
        ORDER BY Trade_id ASC";

$query = mysqli_query($conn, $sql);

if (!$query) {
    error_log("Trades query failed: " . mysqli_error($conn));
    $_SESSION['error'] = "Unable to load trade records.";
    header("Location:greetingUser.php");
    exit();
}

$totalTrades = mysqli_num_rows($query);
$trades = [];

while ($data = mysqli_fetch_assoc($query)) {
    $trades[] = $data;
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
    <title>Trade Management | GIKONKO TSS</title>
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
                    Trade Management
                </h1>

                <p class="text-sm text-slate-500 mt-2">
                    Manage and organize the training trades offered by GIKONKO TSS.
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

                <a href="addTrade.php"
                   class="inline-flex items-center justify-center px-4 py-2.5 bg-blue-700 text-white text-sm font-semibold rounded-md hover:bg-blue-800 transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add New Trade
                </a>

            </div>

        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-8">

            <div class="bg-white border border-slate-200 rounded-lg p-5 shadow-sm">

                <div class="flex items-center justify-between">

                    <div>
                        <p class="text-sm font-medium text-slate-500">
                            Total Trades
                        </p>

                        <h2 class="text-3xl font-bold text-slate-800 mt-2">
                            <?php echo $totalTrades; ?>
                        </h2>
                    </div>

                    <div class="w-11 h-11 rounded-md bg-blue-50 text-blue-700 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  stroke-width="2" d="M12 6v12m-8-6h16"/>
                        </svg>
                    </div>

                </div>

                <p class="text-xs text-slate-400 mt-3">
                    All registered training trades
                </p>

            </div>

            <div class="bg-white border border-slate-200 rounded-lg p-5 shadow-sm">

                <div class="flex items-center justify-between">

                    <div>
                        <p class="text-sm font-medium text-slate-500">
                            Trade Categories
                        </p>

                        <h2 class="text-3xl font-bold text-slate-800 mt-2">
                            <?php echo $totalTrades; ?>
                        </h2>
                    </div>

                    <div class="w-11 h-11 rounded-md bg-purple-50 text-purple-700 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </div>

                </div>

                <p class="text-xs text-slate-400 mt-3">
                    Available trade classifications
                </p>

            </div>

        </div>

        <section class="bg-white border border-slate-200 rounded-lg shadow-sm overflow-hidden">

            <div class="p-5 sm:p-6 border-b border-slate-200">

                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

                    <div>
                        <h2 class="text-lg font-bold text-slate-800">
                            Registered Trades
                        </h2>

                        <p class="text-sm text-slate-500 mt-1">
                            View, update, and manage registered trades.
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
                            id="tradeSearch"
                            placeholder="Search by trade name or code..."
                            class="w-full pl-10 pr-4 py-2.5 text-sm border border-slate-300 rounded-md outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >

                    </div>

                </div>

            </div>

            <div class="overflow-x-auto">

                <table class="w-full text-sm text-left whitespace-nowrap" id="tradeTable">

                    <thead class="bg-slate-50 text-slate-600 uppercase text-xs tracking-wider">
                        <tr>
                            <th class="px-6 py-4 font-semibold">Trade Code</th>
                            <th class="px-6 py-4 font-semibold">Trade Name</th>
                            <th class="px-6 py-4 font-semibold text-center">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">

                    <?php if ($totalTrades > 0): ?>

                        <?php foreach ($trades as $data): ?>

                            <tr class="trade-row hover:bg-slate-50 transition">

                                <td class="px-6 py-4">
                                    <span class="font-semibold text-slate-700">
                                        #<?php echo e($data['Trade_id']); ?>
                                    </span>
                                </td>

                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">

                                        <div class="w-10 h-10 rounded-md bg-blue-50 text-blue-700 flex items-center justify-center font-bold">
                                            <?php echo e(strtoupper(substr($data['Trade_name'], 0, 1))); ?>
                                        </div>

                                        <div>
                                            <p class="font-semibold text-slate-800">
                                                <?php echo e($data['Trade_name']); ?>
                                            </p>

                                            <p class="text-xs text-slate-500 mt-1">
                                                Training category
                                            </p>
                                        </div>

                                    </div>
                                </td>

                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-center gap-2">

                                        <a href="updateTrade.php?Trade_id=<?php echo urlencode($data['Trade_id']); ?>"
                                           class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-blue-700 bg-blue-50 rounded-md hover:bg-blue-100 transition">
                                            Update
                                        </a>

                                        <a href="deleteTrade.php?Trade_id=<?php echo urlencode($data['Trade_id']); ?>"
                                           onclick="return confirm('Are you sure you want to delete this trade?')"
                                           class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-red-700 bg-red-50 rounded-md hover:bg-red-100 transition">
                                            Delete
                                        </a>

                                    </div>
                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="3" class="px-6 py-14 text-center">

                                <div class="flex flex-col items-center">

                                    <div class="w-14 h-14 bg-slate-100 rounded-full flex items-center justify-center text-slate-400">
                                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                                        </svg>
                                    </div>

                                    <h3 class="text-base font-semibold text-slate-800 mt-4">
                                        No trades found
                                    </h3>

                                    <p class="text-sm text-slate-500 mt-1">
                                        There are currently no registered trades.
                                    </p>

                                    <a href="addTrade.php"
                                       class="mt-4 text-sm font-semibold text-blue-700 hover:underline">
                                        Add your first trade
                                    </a>

                                </div>

                            </td>
                        </tr>

                    <?php endif; ?>

                    <tr id="noSearchResults" class="hidden">
                        <td colspan="3" class="px-6 py-8 text-center text-slate-500">
                            No matching trades found.
                        </td>
                    </tr>

                    </tbody>

                </table>

            </div>

            <div class="px-5 py-4 border-t border-slate-200 bg-slate-50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">

                <p class="text-xs text-slate-500">
                    Showing <span id="visibleCount"><?php echo $totalTrades; ?></span>
                    of <?php echo $totalTrades; ?> trades
                </p>

                <p class="text-xs text-slate-500">
                    GIKONKO TSS · Trade Management
                </p>

            </div>

        </section>

    </div>

</main>

<?php include("footer.php"); ?>

<script>
const searchInput = document.getElementById("tradeSearch");
const rows = document.querySelectorAll(".trade-row");
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
