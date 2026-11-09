
<?php
session_start();
include("conn.php");

if (!isset($_SESSION['username'])) {
    $_SESSION['login_error'] = "Please login to access this page";
    header("Location: login.php");
    exit();
}

$error = "";
$tradeName = "";

if (isset($_POST['add'])) {

    $tradeName = trim($_POST['Trade_name'] ?? "");

    if ($tradeName === "") {
        $error = "Please enter a trade name.";
    } else {

        $checkTrade = mysqli_prepare(
            $conn,
            "SELECT Trade_Id FROM trades WHERE Trade_name = ?"
        );

        mysqli_stmt_bind_param($checkTrade, "s", $tradeName);
        mysqli_stmt_execute($checkTrade);
        mysqli_stmt_store_result($checkTrade);

        if (mysqli_stmt_num_rows($checkTrade) > 0) {
            $error = "This trade already exists.";
        } else {

            $insertTrade = mysqli_prepare(
                $conn,
                "INSERT INTO trades (Trade_name) VALUES (?)"
            );

            mysqli_stmt_bind_param($insertTrade, "s", $tradeName);

            if (mysqli_stmt_execute($insertTrade)) {
                header("Location: listOfTrades.php");
                exit();
            } else {
                $error = "Unable to add trade. Please try again.";
            }

            mysqli_stmt_close($insertTrade);
        }

        mysqli_stmt_close($checkTrade);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Add Trade | GIKONKO TSS</title>

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

                <a href="listOfTrades.php"
                   class="hover:text-blue-700 transition">
                    Trade Management
                </a>

                <span>/</span>

                <span class="text-slate-800 font-medium">
                    Add Trade
                </span>

            </div>

            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

                <div>

                    <h1 class="text-2xl md:text-3xl font-bold tracking-tight text-slate-900">
                        Add New Trade
                    </h1>

                    <p class="text-sm text-slate-500 mt-2">
                        Create a new academic trade in your institution.
                    </p>

                </div>

                <a href="listOfTrades.php"
                   class="inline-flex items-center justify-center gap-2 px-4 py-2.5 border border-slate-300 rounded-md text-sm font-semibold text-slate-700 bg-white hover:bg-slate-100 transition">

                    <span>&larr;</span>
                    Back to Trades

                </a>

            </div>

        </div>


        <div class="max-w-3xl">

            <div class="bg-white border border-slate-200 rounded-lg shadow-sm">

                <div class="px-6 py-5 border-b border-slate-200">

                    <h2 class="text-lg font-semibold text-slate-900">
                        Trade Information
                    </h2>

                    <p class="text-sm text-slate-500 mt-1">
                        Enter the official name of the new trade below.
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

                        <label for="Trade_name"
                               class="block text-sm font-medium text-slate-700 mb-2">

                            Trade Name
                            <span class="text-red-500">*</span>

                        </label>

                        <input
                            type="text"
                            id="Trade_name"
                            name="Trade_name"
                            value="<?php echo htmlspecialchars($tradeName); ?>"
                            placeholder="Enter trade name"
                            maxlength="150"
                            required
                            class="w-full px-3 py-2.5 border border-slate-300 rounded-md bg-white text-sm text-slate-700 outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition">

                        <p class="text-xs text-slate-500 mt-2">
                            Example: Software Development, Networking or Electrical Technology.
                        </p>

                    </div>


                    <div class="bg-slate-50 border border-slate-200 rounded-md p-4">

                        <div class="flex items-start gap-3">

                            <span class="text-blue-700 font-semibold">i</span>

                            <div>

                                <h3 class="text-sm font-semibold text-slate-800">
                                    Important Information
                                </h3>

                                <p class="text-xs text-slate-500 mt-1">
                                    A trade represents an academic specialization.
                                    Modules can be assigned to a trade after it has
                                    been created.
                                </p>

                            </div>

                        </div>

                    </div>


                    <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 pt-2">

                        <a href="listOfTrades.php"
                           class="px-5 py-2.5 border border-slate-300 rounded-md text-sm font-semibold text-slate-700 text-center hover:bg-slate-100 transition">

                            Cancel

                        </a>

                        <button
                            type="submit"
                            name="add"
                            class="px-6 py-2.5 bg-blue-700 text-white rounded-md text-sm font-semibold hover:bg-blue-800 focus:ring-4 focus:ring-blue-100 transition">

                            Add Trade

                        </button>

                    </div>

                </form>

            </div>


            <div class="mt-5 text-sm text-slate-500">
                Organize your institution's academic programs by creating and managing trades.
            </div>

        </div>

    </main>

    <?php include("footer.php"); ?>

</body>
</html>
