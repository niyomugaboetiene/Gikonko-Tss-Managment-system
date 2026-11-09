
<?php
include("conn.php");
session_start();

if (!isset($_SESSION['username'])) {
    $_SESSION['login_error'] = "Please login to access this page";
    header("Location: login.php");
    exit();
}

$error = "";

if (!isset($_GET['Module_Id']) || !ctype_digit((string)$_GET['Module_Id'])) {
    header("Location: listOfModule.php");
    exit();
}

$Module_Id = (int)$_GET['Module_Id'];

$stmt = mysqli_prepare($conn, "SELECT
    m.Module_Id,
    m.Module_Name,
    m.Trade_Id,
    t.Trade_name
    FROM modules m
    JOIN trades t ON t.Trade_id = m.Trade_Id
    WHERE m.Module_Id = ?");

mysqli_stmt_bind_param($stmt, "i", $Module_Id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    mysqli_stmt_close($stmt);
    header("Location: listOfModule.php");
    exit();
}

$Module = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (isset($_POST['save'])) {
    $Module_Name = trim($_POST['Module_Name'] ?? '');
    $Trade_Id = trim($_POST['Trade_Id'] ?? '');

    if ($Module_Name === '' || $Trade_Id === '') {
        $error = "Please fill in all required fields.";
    } elseif (!ctype_digit($Trade_Id)) {
        $error = "Please select a valid trade.";
    } else {
        $checkTrade = mysqli_prepare($conn, "SELECT Trade_id FROM trades WHERE Trade_id = ?");
        mysqli_stmt_bind_param($checkTrade, "i", $Trade_Id);
        mysqli_stmt_execute($checkTrade);
        $tradeResult = mysqli_stmt_get_result($checkTrade);
        $tradeExists = mysqli_num_rows($tradeResult) > 0;
        mysqli_stmt_close($checkTrade);

        if (!$tradeExists) {
            $error = "The selected trade does not exist.";
        } else {
            $checkDuplicate = mysqli_prepare(
                $conn,
                "SELECT Module_Id FROM modules
                 WHERE Module_Name = ? AND Module_Id != ?"
            );

            mysqli_stmt_bind_param($checkDuplicate, "si", $Module_Name, $Module_Id);
            mysqli_stmt_execute($checkDuplicate);
            $duplicateResult = mysqli_stmt_get_result($checkDuplicate);
            $duplicateExists = mysqli_num_rows($duplicateResult) > 0;
            mysqli_stmt_close($checkDuplicate);

            if ($duplicateExists) {
                $error = "A module with this name already exists.";
            } else {
                $update = mysqli_prepare(
                    $conn,
                    "UPDATE modules SET Module_Name = ?, Trade_Id = ?
                     WHERE Module_Id = ?"
                );

                mysqli_stmt_bind_param(
                    $update,
                    "sii",
                    $Module_Name,
                    $Trade_Id,
                    $Module_Id
                );

                if (mysqli_stmt_execute($update)) {
                    mysqli_stmt_close($update);
                    header("Location: listOfModule.php");
                    exit();
                } else {
                    $error = "Unable to update the module. Please try again.";
                }

                mysqli_stmt_close($update);
            }
        }
    }

    $Module['Module_Name'] = $Module_Name;
    $Module['Trade_Id'] = $Trade_Id;
}

function escapeModule($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$trades = mysqli_query(
    $conn,
    "SELECT Trade_id, Trade_name FROM trades ORDER BY Trade_name ASC"
);

if (!$trades) {
    die("Unable to load trade information.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Module | GIKONKO TSS</title>
    <link href="output.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="gikonko.png">
</head>

<body class="min-h-screen bg-slate-50 flex flex-col">

    <header class="w-full sticky top-0 z-50 bg-white border-b border-slate-200 shadow-sm">
        <?php include("Dashboard.php"); ?>
    </header>

    <main class="flex-grow w-full max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <div class="mb-8">
            <p class="text-sm font-semibold text-blue-700 uppercase tracking-wide">
                Academic Management
            </p>

            <h1 class="text-2xl sm:text-3xl font-bold text-slate-800 mt-1">
                Update Module
            </h1>

            <p class="text-sm text-slate-500 mt-2">
                Modify module information and its assigned trade.
            </p>
        </div>

        <section class="bg-white border border-slate-200 rounded-lg shadow-sm overflow-hidden">

            <div class="px-6 py-5 border-b border-slate-200">
                <h2 class="text-lg font-semibold text-slate-800">
                    Module Information
                </h2>

                <p class="text-sm text-slate-500 mt-1">
                    Module record #<?php echo escapeModule($Module['Module_Id']); ?>
                </p>
            </div>

            <form method="POST" class="p-6 space-y-6">

                <?php if ($error !== ''): ?>
                    <div class="p-4 rounded-md bg-red-50 border border-red-200 text-red-700 text-sm">
                        <?php echo escapeModule($error); ?>
                    </div>
                <?php endif; ?>

                <div>
                    <label for="Module_Id" class="block text-sm font-semibold text-slate-700 mb-2">
                        Module Code
                    </label>

                    <input
                        type="text"
                        id="Module_Id"
                        value="<?php echo escapeModule($Module['Module_Id']); ?>"
                        readonly
                        class="w-full px-4 py-3 bg-slate-100 border border-slate-200 rounded-md text-slate-500 cursor-not-allowed"
                    >
                </div>

                <div>
                    <label for="Module_Name" class="block text-sm font-semibold text-slate-700 mb-2">
                        Module Name
                    </label>

                    <input
                        type="text"
                        id="Module_Name"
                        name="Module_Name"
                        value="<?php echo escapeModule($Module['Module_Name']); ?>"
                        placeholder="Enter module name"
                        maxlength="255"
                        required
                        class="w-full px-4 py-3 bg-white border border-slate-300 rounded-md text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                </div>

                <div>
                    <label for="Trade_Id" class="block text-sm font-semibold text-slate-700 mb-2">
                        Assigned Trade
                    </label>

                    <select
                        id="Trade_Id"
                        name="Trade_Id"
                        required
                        class="w-full px-4 py-3 bg-white border border-slate-300 rounded-md text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="">Select a trade</option>

                        <?php while ($trade = mysqli_fetch_assoc($trades)): ?>
                            <option
                                value="<?php echo escapeModule($trade['Trade_id']); ?>"
                                <?php echo (string)$Module['Trade_Id'] === (string)$trade['Trade_id'] ? 'selected' : ''; ?>
                            >
                                <?php echo escapeModule($trade['Trade_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="bg-slate-50 border border-slate-200 rounded-md p-4">
                    <p class="text-sm text-slate-600">
                        <span class="font-semibold text-slate-800">Note:</span>
                        Changing the assigned trade will update the trade associated with this module.
                    </p>
                </div>

                <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3 pt-2">

                    <a href="listOfModule.php"
                       class="inline-flex justify-center items-center px-5 py-2.5 border border-slate-300 text-slate-700 font-semibold rounded-md hover:bg-slate-100 transition">
                        Cancel
                    </a>

                    <button
                        type="submit"
                        name="save"
                        class="inline-flex justify-center items-center px-5 py-2.5 bg-blue-700 text-white font-semibold rounded-md hover:bg-blue-800 transition"
                    >
                        Save Changes
                    </button>

                </div>

            </form>
        </section>

    </main>

    <?php include("footer.php"); ?>

</body>
</html>
