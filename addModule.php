
<?php
session_start();
include("conn.php");

if (!isset($_SESSION['username'])) {
    $_SESSION['login_error'] = "Please login to access this page";
    header("Location: login.php");
    exit();
}

$error = "";

$moduleName = "";
$tradeId = "";

if (isset($_POST['addModule'])) {

    $moduleName = trim($_POST['Module_Name'] ?? "");
    $tradeId = trim($_POST['Trade_Id'] ?? "");

    if ($moduleName === "" || $tradeId === "") {
        $error = "Please complete all fields.";
    } else {

        $checkModule = mysqli_prepare(
            $conn,
            "SELECT Module_Id FROM modules WHERE Module_Name = ?"
        );

        mysqli_stmt_bind_param($checkModule, "s", $moduleName);
        mysqli_stmt_execute($checkModule);
        mysqli_stmt_store_result($checkModule);

        if (mysqli_stmt_num_rows($checkModule) > 0) {
            $error = "This module already exists.";
        } else {

            $checkTrade = mysqli_prepare(
                $conn,
                "SELECT Trade_Id FROM trades WHERE Trade_Id = ?"
            );

            mysqli_stmt_bind_param($checkTrade, "s", $tradeId);
            mysqli_stmt_execute($checkTrade);
            mysqli_stmt_store_result($checkTrade);

            if (mysqli_stmt_num_rows($checkTrade) == 0) {
                $error = "Selected trade does not exist.";
            } else {

                $insertModule = mysqli_prepare(
                    $conn,
                    "INSERT INTO modules (Module_Name, Trade_Id)
                     VALUES (?, ?)"
                );

                mysqli_stmt_bind_param(
                    $insertModule,
                    "ss",
                    $moduleName,
                    $tradeId
                );

                if (mysqli_stmt_execute($insertModule)) {
                    header("Location: listOfModule.php");
                    exit();
                } else {
                    $error = "Unable to add module. Please try again.";
                }

                mysqli_stmt_close($insertModule);
            }

            mysqli_stmt_close($checkTrade);
        }

        mysqli_stmt_close($checkModule);
    }
}

$tradesQuery = mysqli_query(
    $conn,
    "SELECT Trade_Id, Trade_Name FROM trades ORDER BY Trade_Name ASC"
);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Add Module | GIKONKO TSS</title>

    <link href="output.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="gikonko.png">
</head>

<body class="min-h-screen bg-slate-50 text-slate-800">

    <header class="sticky top-0 z-30 bg-white border-b border-slate-200">
        <?php include("Dashboard.php"); ?>
    </header>

    <main class="max-w-7xl mx-auto px-5 py-8 md:px-8">

        <div class="mb-8">

            <div class="flex items-center gap-2 text-sm text-slate-500 mb-3">
                <a href="listOfModule.php" class="hover:text-blue-700 transition">
                    Module Management
                </a>

                <span>/</span>

                <span class="text-slate-800 font-medium">
                    Add Module
                </span>
            </div>

            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

                <div>
                    <h1 class="text-2xl md:text-3xl font-bold tracking-tight text-slate-900">
                        Add New Module
                    </h1>

                    <p class="text-sm text-slate-500 mt-2">
                        Create and assign a new learning module to a trade.
                    </p>
                </div>

                <a href="listOfModule.php"
                   class="inline-flex items-center justify-center gap-2 px-4 py-2.5 border border-slate-300 rounded-md text-sm font-semibold text-slate-700 bg-white hover:bg-slate-100 transition">

                    <span>&larr;</span>
                    Back to Modules

                </a>

            </div>

        </div>


        <div class="max-w-3xl">

            <div class="bg-white border border-slate-200 rounded-lg shadow-sm">

                <div class="px-6 py-5 border-b border-slate-200">

                    <h2 class="text-lg font-semibold text-slate-900">
                        Module Information
                    </h2>

                    <p class="text-sm text-slate-500 mt-1">
                        Provide the module name and select its corresponding trade.
                    </p>

                </div>


                <?php if (!empty($error)): ?>

                    <div class="mx-6 mt-5 flex items-start gap-3 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-md">

                        <span class="font-bold">!</span>

                        <p class="text-sm font-medium">
                            <?php echo htmlspecialchars($error); ?>
                        </p>

                    </div>

                <?php endif; ?>


                <form method="POST" action="" class="p-6 space-y-6">

                    <div>

                        <label for="Module_Name"
                               class="block text-sm font-medium text-slate-700 mb-2">

                            Module Name
                            <span class="text-red-500">*</span>

                        </label>

                        <input
                            type="text"
                            id="Module_Name"
                            name="Module_Name"
                            value="<?php echo htmlspecialchars($moduleName); ?>"
                            placeholder="Enter module name"
                            required
                            maxlength="150"
                            class="w-full px-3 py-2.5 border border-slate-300 rounded-md bg-white text-sm text-slate-700 outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition">

                        <p class="text-xs text-slate-500 mt-2">
                            Enter the official name of the learning module.
                        </p>

                    </div>


                    <div class="border-t border-slate-100"></div>


                    <div>

                        <label for="Trade_Id"
                               class="block text-sm font-medium text-slate-700 mb-2">

                            Select Trade
                            <span class="text-red-500">*</span>

                        </label>

                        <select
                            id="Trade_Id"
                            name="Trade_Id"
                            required
                            class="w-full px-3 py-2.5 border border-slate-300 rounded-md bg-white text-sm text-slate-700 outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition">

                            <option value="">Select a trade</option>

                            <?php while ($data = mysqli_fetch_assoc($tradesQuery)): ?>

                                <option
                                    value="<?php echo htmlspecialchars($data['Trade_Id']); ?>"
                                    <?php echo ($tradeId == $data['Trade_Id']) ? 'selected' : ''; ?>>

                                    <?php echo htmlspecialchars($data['Trade_Name']); ?>

                                </option>

                            <?php endwhile; ?>

                        </select>

                        <p class="text-xs text-slate-500 mt-2">
                            Choose the trade where this module belongs.
                        </p>

                    </div>


                    <div class="bg-slate-50 border border-slate-200 rounded-md p-4">

                        <div class="flex items-start gap-3">

                            <span class="text-blue-700 font-semibold">i</span>

                            <div>
                                <h3 class="text-sm font-semibold text-slate-800">
                                    Before saving
                                </h3>

                                <p class="text-xs text-slate-500 mt-1">
                                    Make sure the module name is correct and the appropriate trade has been selected.
                                </p>
                            </div>

                        </div>

                    </div>


                    <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 pt-2">

                        <a href="listOfModule.php"
                           class="px-5 py-2.5 border border-slate-300 rounded-md text-sm font-semibold text-slate-700 text-center hover:bg-slate-100 transition">

                            Cancel

                        </a>

                        <button
                            type="submit"
                            name="addModule"
                            class="px-6 py-2.5 bg-blue-700 text-white rounded-md text-sm font-semibold hover:bg-blue-800 focus:ring-4 focus:ring-blue-100 transition">

                            Save Module

                        </button>

                    </div>

                </form>

            </div>


            <div class="mt-5 text-sm text-slate-500">
                Modules are organized under their respective trades for easier academic management.
            </div>

        </div>

    </main>
<?php include("footer.php"); ?>

</body>
</html>
