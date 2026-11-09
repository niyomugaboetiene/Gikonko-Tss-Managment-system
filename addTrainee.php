
<?php
session_start();
include("conn.php");

if (!isset($_SESSION['username'])) {
    $_SESSION['login_error'] = "Please login to access this page";
    header("Location: login.php");
    exit();
}

$error = "";

$firstName = "";
$lastName = "";
$gender = "";
$tradeId = "";

if (isset($_POST['add'])) {

    $firstName = trim($_POST['Firstname'] ?? "");
    $lastName = trim($_POST['lastname'] ?? "");
    $gender = $_POST['gender'] ?? "";
    $tradeId = $_POST['Trade_id'] ?? "";

    if (
        $firstName === "" ||
        $lastName === "" ||
        $gender === "" ||
        $tradeId === ""
    ) {
        $error = "Please complete all required fields.";
    } elseif (!in_array($gender, ["male", "female"], true)) {
        $error = "Please select a valid gender.";
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

            $insertTrainee = mysqli_prepare(
                $conn,
                "INSERT INTO trainees
                (Firstname, lastname, gender, Trade_id)
                VALUES (?, ?, ?, ?)"
            );

            mysqli_stmt_bind_param(
                $insertTrainee,
                "ssss",
                $firstName,
                $lastName,
                $gender,
                $tradeId
            );

            if (mysqli_stmt_execute($insertTrainee)) {
                header("Location: listOfTrainee.php");
                exit();
            } else {
                $error = "Unable to register trainee. Please try again.";
            }

            mysqli_stmt_close($insertTrainee);
        }

        mysqli_stmt_close($checkTrade);
    }
}

$tradesQuery = mysqli_query(
    $conn,
    "SELECT Trade_Id, Trade_name FROM trades ORDER BY Trade_name ASC"
);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Add New Trainee | GIKONKO TSS</title>

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

                <a href="listOfTrainee.php"
                   class="hover:text-blue-700 transition">
                    Trainee Management
                </a>

                <span>/</span>

                <span class="text-slate-800 font-medium">
                    Add Trainee
                </span>

            </div>

            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

                <div>

                    <h1 class="text-2xl md:text-3xl font-bold tracking-tight text-slate-900">
                        Register New Trainee
                    </h1>

                    <p class="text-sm text-slate-500 mt-2">
                        Add a new trainee and assign them to an academic trade.
                    </p>

                </div>

                <a href="listOfTrainee.php"
                   class="inline-flex items-center justify-center gap-2 px-4 py-2.5 border border-slate-300 rounded-md text-sm font-semibold text-slate-700 bg-white hover:bg-slate-100 transition">

                    <span>&larr;</span>
                    Back to Trainees

                </a>

            </div>

        </div>


        <div class="max-w-3xl">

            <div class="bg-white border border-slate-200 rounded-lg shadow-sm">

                <div class="px-6 py-5 border-b border-slate-200">

                    <h2 class="text-lg font-semibold text-slate-900">
                        Trainee Information
                    </h2>

                    <p class="text-sm text-slate-500 mt-1">
                        Complete the personal and academic information below.
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

                        <h3 class="text-sm font-semibold text-slate-900 mb-1">
                            Personal Information
                        </h3>

                        <p class="text-xs text-slate-500 mb-5">
                            Enter the trainee's basic personal details.
                        </p>


                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                            <div>

                                <label for="Firstname"
                                       class="block text-sm font-medium text-slate-700 mb-2">

                                    First Name
                                    <span class="text-red-500">*</span>

                                </label>

                                <input
                                    type="text"
                                    id="Firstname"
                                    name="Firstname"
                                    value="<?php echo htmlspecialchars($firstName); ?>"
                                    placeholder="Enter first name"
                                    maxlength="100"
                                    required
                                    class="w-full px-3 py-2.5 border border-slate-300 rounded-md bg-white text-sm outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition">

                            </div>


                            <div>

                                <label for="lastname"
                                       class="block text-sm font-medium text-slate-700 mb-2">

                                    Last Name
                                    <span class="text-red-500">*</span>

                                </label>

                                <input
                                    type="text"
                                    id="lastname"
                                    name="lastname"
                                    value="<?php echo htmlspecialchars($lastName); ?>"
                                    placeholder="Enter last name"
                                    maxlength="100"
                                    required
                                    class="w-full px-3 py-2.5 border border-slate-300 rounded-md bg-white text-sm outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition">

                            </div>

                        </div>


                        <div class="mt-5">

                            <label for="gender"
                                   class="block text-sm font-medium text-slate-700 mb-2">

                                Gender
                                <span class="text-red-500">*</span>

                            </label>

                            <select
                                id="gender"
                                name="gender"
                                required
                                class="w-full px-3 py-2.5 border border-slate-300 rounded-md bg-white text-sm text-slate-700 outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition">

                                <option value="">Select gender</option>

                                <option value="male"
                                    <?php echo ($gender === "male") ? "selected" : ""; ?>>
                                    Male
                                </option>

                                <option value="female"
                                    <?php echo ($gender === "female") ? "selected" : ""; ?>>
                                    Female
                                </option>

                            </select>

                        </div>

                    </div>


                    <div class="border-t border-slate-100"></div>


                    <div>

                        <h3 class="text-sm font-semibold text-slate-900 mb-1">
                            Academic Information
                        </h3>

                        <p class="text-xs text-slate-500 mb-5">
                            Assign the trainee to the appropriate academic trade.
                        </p>


                        <label for="Trade_id"
                               class="block text-sm font-medium text-slate-700 mb-2">

                            Select Trade
                            <span class="text-red-500">*</span>

                        </label>

                        <select
                            id="Trade_id"
                            name="Trade_id"
                            required
                            class="w-full px-3 py-2.5 border border-slate-300 rounded-md bg-white text-sm text-slate-700 outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition">

                            <option value="">Select a trade</option>

                            <?php while ($data = mysqli_fetch_assoc($tradesQuery)): ?>

                                <option
                                    value="<?php echo htmlspecialchars($data['Trade_Id']); ?>"
                                    <?php echo ($tradeId == $data['Trade_Id']) ? "selected" : ""; ?>>

                                    <?php echo htmlspecialchars($data['Trade_name']); ?>

                                </option>

                            <?php endwhile; ?>

                        </select>

                    </div>


                    <div class="bg-slate-50 border border-slate-200 rounded-md p-4">

                        <div class="flex items-start gap-3">

                            <span class="text-blue-700 font-semibold">i</span>

                            <div>

                                <h3 class="text-sm font-semibold text-slate-800">
                                    Registration Information
                                </h3>

                                <p class="text-xs text-slate-500 mt-1">
                                    The trainee code will be managed by your system
                                    according to your database configuration.
                                </p>

                            </div>

                        </div>

                    </div>


                    <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 pt-2">

                        <a href="listOfTrainee.php"
                           class="px-5 py-2.5 border border-slate-300 rounded-md text-sm font-semibold text-slate-700 text-center hover:bg-slate-100 transition">

                            Cancel

                        </a>

                        <button
                            type="submit"
                            name="add"
                            class="px-6 py-2.5 bg-blue-700 text-white rounded-md text-sm font-semibold hover:bg-blue-800 focus:ring-4 focus:ring-blue-100 transition">

                            Register Trainee

                        </button>

                    </div>

                </form>

            </div>


            <div class="mt-5 text-sm text-slate-500">
                Ensure the trainee's personal details and academic trade are correct before registration.
            </div>

        </div>

    </main>

    <?php include("footer.php"); ?>

</body>
</html>
