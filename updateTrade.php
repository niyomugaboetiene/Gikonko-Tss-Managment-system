
<?php
include("conn.php");
session_start();

if (!isset($_SESSION['username'])) {
    $_SESSION['login_error'] = "Please login to access this page";
    header("Location: login.php");
    exit();
}

$error = "";

if (!isset($_GET['Trade_id']) || !ctype_digit((string)$_GET['Trade_id'])) {
    header("Location: listOfTrades.php");
    exit();
}

$Trade_Id = (int)$_GET['Trade_id'];

$stmt = mysqli_prepare($conn, "SELECT Trade_id, Trade_name FROM trades WHERE Trade_id = ?");
mysqli_stmt_bind_param($stmt, "i", $Trade_Id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    mysqli_stmt_close($stmt);
    header("Location: listOfTrades.php");
    exit();
}

$Data = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (isset($_POST['save'])) {
    $Trade_Name = trim($_POST['Trade_name'] ?? '');

    if ($Trade_Name === '') {
        $error = "Please enter the trade name.";
    } else {
        $checkDuplicate = mysqli_prepare(
            $conn,
            "SELECT Trade_id FROM trades
             WHERE Trade_name = ? AND Trade_id != ?"
        );

        mysqli_stmt_bind_param($checkDuplicate, "si", $Trade_Name, $Trade_Id);
        mysqli_stmt_execute($checkDuplicate);
        $duplicateResult = mysqli_stmt_get_result($checkDuplicate);
        $duplicateExists = mysqli_num_rows($duplicateResult) > 0;
        mysqli_stmt_close($checkDuplicate);

        if ($duplicateExists) {
            $error = "A trade with this name already exists.";
        } else {
            $update = mysqli_prepare(
                $conn,
                "UPDATE trades SET Trade_name = ? WHERE Trade_id = ?"
            );

            mysqli_stmt_bind_param($update, "si", $Trade_Name, $Trade_Id);

            if (mysqli_stmt_execute($update)) {
                mysqli_stmt_close($update);
                header("Location: listOfTrades.php");
                exit();
            } else {
                $error = "Unable to update the trade. Please try again.";
            }

            mysqli_stmt_close($update);
        }
    }

    $Data['Trade_name'] = $Trade_Name;
}

function escapeTrade($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Trade | GIKONKO TSS</title>
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
                Update Trade
            </h1>

            <p class="text-sm text-slate-500 mt-2">
                Modify the trade information in the system.
            </p>
        </div>

        <section class="bg-white border border-slate-200 rounded-lg shadow-sm overflow-hidden">

            <div class="px-6 py-5 border-b border-slate-200">
                <h2 class="text-lg font-semibold text-slate-800">
                    Trade Information
                </h2>

                <p class="text-sm text-slate-500 mt-1">
                    Trade record #<?php echo escapeTrade($Data['Trade_id']); ?>
                </p>
            </div>

            <form method="POST" class="p-6 space-y-6">

                <?php if ($error !== ''): ?>
                    <div class="p-4 rounded-md bg-red-50 border border-red-200 text-red-700 text-sm">
                        <?php echo escapeTrade($error); ?>
                    </div>
                <?php endif; ?>

                <div>
                    <label for="Trade_id" class="block text-sm font-semibold text-slate-700 mb-2">
                        Trade Code
                    </label>

                    <input
                        type="text"
                        id="Trade_id"
                        value="<?php echo escapeTrade($Data['Trade_id']); ?>"
                        readonly
                        class="w-full px-4 py-3 bg-slate-100 border border-slate-200 rounded-md text-slate-500 cursor-not-allowed"
                    >
                </div>

                <div>
                    <label for="Trade_name" class="block text-sm font-semibold text-slate-700 mb-2">
                        Trade Name
                    </label>

                    <input
                        type="text"
                        id="Trade_name"
                        name="Trade_name"
                        value="<?php echo escapeTrade($Data['Trade_name']); ?>"
                        placeholder="Enter trade name"
                        maxlength="255"
                        required
                        class="w-full px-4 py-3 bg-white border border-slate-300 rounded-md text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                </div>

                <div class="bg-slate-50 border border-slate-200 rounded-md p-4">
                    <p class="text-sm text-slate-600">
                        <span class="font-semibold text-slate-800">Note:</span>
                        Updating a trade name will also change how that trade is displayed wherever it is referenced in the system.
                    </p>
                </div>

                <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3 pt-2">

                    <a href="listOfTrades.php"
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
