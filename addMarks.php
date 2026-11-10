
<?php
session_start();
include("conn.php");

if (!isset($_SESSION['username'])) {
    $_SESSION['login_error'] = "Please login to access this page";
    header("Location: login.php");
    exit();
}

$error = "";
$success = "";

$selectedTrainee = $_POST['Trainee_Id'] ?? "";
$selectedModule = $_POST['Module_Id'] ?? "";
$formativeValue = $_POST['Formative_Assessment'] ?? "";
$summativeValue = $_POST['Summative_Assessment'] ?? "";

if (isset($_POST['addMarks'])) {

    if (
        empty($selectedTrainee) ||
        empty($selectedModule) ||
        $formativeValue === "" ||
        $summativeValue === ""
    ) {
        $error = "Please complete all fields.";
    } elseif (
        !is_numeric($formativeValue) ||
        !is_numeric($summativeValue) ||
        $formativeValue < 0 ||
        $formativeValue > 50 ||
        $summativeValue < 0 ||
        $summativeValue > 50
    ) {
        $error = "Marks must be between 0 and 50.";
    } else {

        $Formative = (float)$formativeValue;
        $Summative = (float)$summativeValue;

        // Check whether trainee exists
        $checkTrainee = mysqli_prepare(
            $conn,
            "SELECT Trainee_Id FROM trainees WHERE Trainee_Id = ?"
        );

        mysqli_stmt_bind_param($checkTrainee, "s", $selectedTrainee);
        mysqli_stmt_execute($checkTrainee);
        mysqli_stmt_store_result($checkTrainee);

        if (mysqli_stmt_num_rows($checkTrainee) == 0) {
            $error = "Selected trainee does not exist.";
        } else {

            // Check whether module exists
            $checkModule = mysqli_prepare(
                $conn,
                "SELECT Module_Id FROM modules WHERE Module_Id = ?"
            );

            mysqli_stmt_bind_param($checkModule, "s", $selectedModule);
            mysqli_stmt_execute($checkModule);
            mysqli_stmt_store_result($checkModule);

            if (mysqli_stmt_num_rows($checkModule) == 0) {
                $error = "Selected module does not exist.";
            } else {

                // Prevent duplicate marks
                $checkMarks = mysqli_prepare(
                    $conn,
                    "SELECT Trainee_Id FROM marks
                     WHERE Trainee_Id = ? AND Module_Id = ?"
                );

                mysqli_stmt_bind_param(
                    $checkMarks,
                    "ss",
                    $selectedTrainee,
                    $selectedModule
                );

                mysqli_stmt_execute($checkMarks);
                mysqli_stmt_store_result($checkMarks);

                if (mysqli_stmt_num_rows($checkMarks) > 0) {
                    $error = "Marks already exist for this trainee and module. Update them instead.";
                } else {

                    $Total = $Formative + $Summative;

                    $Decision = ($Total >= 70)
                        ? "Competent"
                        : "Not Competent";

                    // Insert marks
                    $insertMarks = mysqli_prepare(
                        $conn,
                        "INSERT INTO marks
                        (Trainee_Id, Module_Id, Formative_Assessment,
                        Summative_Assessment, Total_Marks, decision)
                        VALUES (?, ?, ?, ?, ?, ?)"
                    );

                    mysqli_stmt_bind_param(
                        $insertMarks,
                        "ssddds",
                        $selectedTrainee,
                        $selectedModule,
                        $Formative,
                        $Summative,
                        $Total,
                        $Decision
                    );

                    if (mysqli_stmt_execute($insertMarks)) {
                        header("Location: listOfMarks.php");
                        exit();
                    } else {
                        $error = "Unable to save marks. Please try again.";
                    }

                    mysqli_stmt_close($insertMarks);
                }

                mysqli_stmt_close($checkMarks);
            }

            mysqli_stmt_close($checkModule);
        }

        mysqli_stmt_close($checkTrainee);
    }
}

// Fetch trainees
$traineesQuery = mysqli_query(
    $conn,
    "SELECT Trainee_Id,
    CONCAT(Firstname, ' ', Lastname) AS Trainee_Name
    FROM trainees ORDER BY Firstname ASC"
);

// Fetch modules
$modulesQuery = mysqli_query(
    $conn,
    "SELECT Module_Id, Module_Name
    FROM modules ORDER BY Module_Name ASC"
);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Add Marks | GIKONKO TSS</title>

<link href="output.css" rel="stylesheet">
 <link rel="icon" type="image/png" href="gikonko.png">
</head>

<body class="min-h-screen bg-slate-50 text-slate-800">

    <!-- Navigation -->
    <header class="sticky top-0 z-30 bg-white border-b border-slate-200">
        <?php include("Dashboard.php"); ?>
    </header>

    <!-- Main content -->
    <main class="max-w-7xl mx-auto px-5 py-8 md:px-8">

        <!-- Page heading -->
        <div class="mb-8">

            <div class="flex items-center gap-2 text-sm text-slate-500 mb-3">
                <a href="listOfMarks.php"
                   class="hover:text-blue-700 transition">
                    Marks Management
                </a>

                <span>/</span>

                <span class="text-slate-800 font-medium">
                    Add Marks
                </span>
            </div>

            <div class="flex flex-col md:flex-row md:items-center
                        md:justify-between gap-4">

                <div>
                    <h1 class="text-2xl md:text-3xl font-bold
                               tracking-tight text-slate-900">
                        Add Student Marks
                    </h1>

                    <p class="text-sm text-slate-500 mt-2">
                        Record formative and summative assessment
                        results for your trainees.
                    </p>
                </div>

                <a href="listOfMarks.php"
                   class="inline-flex items-center justify-center gap-2
                          px-4 py-2.5 border border-slate-300
                          rounded-md text-sm font-semibold
                          text-slate-700 bg-white
                          hover:bg-slate-100 transition">

                    <span>&larr;</span>
                    Back to Marks
                </a>

            </div>
        </div>


        <!-- Form layout -->
        <div class="max-w-3xl">

            <div class="bg-white border border-slate-200
                        rounded-lg shadow-sm">

                <!-- Form header -->
                <div class="px-6 py-5 border-b border-slate-200">

                    <h2 class="text-lg font-semibold text-slate-900">
                        Assessment Information
                    </h2>

                    <p class="text-sm text-slate-500 mt-1">
                        Enter the trainee's assessment details below.
                    </p>

                </div>


                <!-- Error message -->
                <?php if (!empty($error)): ?>

                    <div class="mx-6 mt-5 flex items-start gap-3
                                bg-red-50 border border-red-200
                                text-red-700 px-4 py-3 rounded-md">

                        <span class="font-bold">!</span>

                        <p class="text-sm font-medium">
                            <?php echo htmlspecialchars($error); ?>
                        </p>

                    </div>

                <?php endif; ?>


                <form method="POST" class="p-6 space-y-6">

                    <!-- Trainee section -->
                    <div>

                        <div class="mb-4">
                            <h3 class="text-sm font-semibold
                                       text-slate-900">
                                Trainee Details
                            </h3>

                            <p class="text-xs text-slate-500 mt-1">
                                Select the trainee whose marks you
                                want to record.
                            </p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                            <!-- Trainee name -->
                            <div>

                                <label for="Trainee_Id_Select"
                                       class="block text-sm font-medium
                                              text-slate-700 mb-2">
                                    Trainee Name
                                    <span class="text-red-500">*</span>
                                </label>

                                <select
                                    id="Trainee_Id_Select"
                                    class="w-full px-3 py-2.5
                                           border border-slate-300
                                           rounded-md bg-white
                                           text-sm text-slate-700
                                           outline-none
                                           focus:border-blue-600
                                           focus:ring-2
                                           focus:ring-blue-100
                                           transition"
                                    required>

                                    <option value="">
                                        Select a trainee
                                    </option>

                                    <?php while ($data = mysqli_fetch_assoc($traineesQuery)): ?>

                                        <option
                                            value="<?php echo htmlspecialchars($data['Trainee_Id']); ?>"
                                            <?php echo ($selectedTrainee == $data['Trainee_Id']) ? 'selected' : ''; ?>>

                                            <?php echo htmlspecialchars($data['Trainee_Name']); ?>

                                        </option>

                                    <?php endwhile; ?>

                                </select>

                            </div>


                            <!-- Trainee code -->
                            <div>

                                <label for="Trainee_Id"
                                       class="block text-sm font-medium
                                              text-slate-700 mb-2">
                                    Trainee Code
                                </label>

                                <input
                                    type="text"
                                    name="Trainee_Id"
                                    id="Trainee_Id"
                                    value="<?php echo htmlspecialchars($selectedTrainee); ?>"
                                    placeholder="Automatically populated"
                                    readonly
                                    required
                                    class="w-full px-3 py-2.5
                                           border border-slate-200
                                           rounded-md bg-slate-100
                                           text-sm text-slate-600
                                           outline-none cursor-not-allowed">

                            </div>

                        </div>

                    </div>


                    <div class="border-t border-slate-100"></div>


                    <!-- Module section -->
                    <div>

                        <h3 class="text-sm font-semibold text-slate-900 mb-4">
                            Module Information
                        </h3>

                        <label for="Module_Id"
                               class="block text-sm font-medium
                                      text-slate-700 mb-2">

                            Module Name
                            <span class="text-red-500">*</span>

                        </label>

                        <select
                            id="Module_Id"
                            name="Module_Id"
                            required
                            class="w-full px-3 py-2.5
                                   border border-slate-300
                                   rounded-md bg-white
                                   text-sm text-slate-700
                                   outline-none
                                   focus:border-blue-600
                                   focus:ring-2
                                   focus:ring-blue-100
                                   transition">

                            <option value="">
                                Select a module
                            </option>

                            <?php while ($data = mysqli_fetch_assoc($modulesQuery)): ?>

                                <option
                                    value="<?php echo htmlspecialchars($data['Module_Id']); ?>"
                                    <?php echo ($selectedModule == $data['Module_Id']) ? 'selected' : ''; ?>>

                                    <?php echo htmlspecialchars($data['Module_Name']); ?>

                                </option>

                            <?php endwhile; ?>

                        </select>

                    </div>


                    <div class="border-t border-slate-100"></div>


                    <!-- Marks section -->
                    <div>

                        <div class="mb-4">
                            <h3 class="text-sm font-semibold text-slate-900">
                                Assessment Results
                            </h3>

                            <p class="text-xs text-slate-500 mt-1">
                                Each assessment carries a maximum of 50 marks.
                            </p>
                        </div>


                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                            <!-- Formative -->
                            <div>

                                <label for="Formative_Assessment"
                                       class="block text-sm font-medium
                                              text-slate-700 mb-2">

                                    Formative Assessment
                                    <span class="text-red-500">*</span>

                                </label>

                                <div class="relative">

                                    <input
                                        type="number"
                                        id="Formative_Assessment"
                                        name="Formative_Assessment"
                                        min="0"
                                        max="50"
                                        step="1"
                                        required
                                        value="<?php echo htmlspecialchars($formativeValue); ?>"
                                        placeholder="Enter marks"
                                        class="w-full px-3 py-2.5 pr-14
                                               border border-slate-300
                                               rounded-md bg-white
                                               text-sm outline-none
                                               focus:border-blue-600
                                               focus:ring-2
                                               focus:ring-blue-100">

                                    <span class="absolute right-3 top-1/2
                                                 -translate-y-1/2
                                                 text-xs text-slate-400">
                                        / 50
                                    </span>

                                </div>

                            </div>


                            <!-- Summative -->
                            <div>

                                <label for="Summative_Assessment"
                                       class="block text-sm font-medium
                                              text-slate-700 mb-2">

                                    Summative Assessment
                                    <span class="text-red-500">*</span>

                                </label>

                                <div class="relative">

                                    <input
                                        type="number"
                                        id="Summative_Assessment"
                                        name="Summative_Assessment"
                                        min="0"
                                        max="50"
                                        step="1"
                                        required
                                        value="<?php echo htmlspecialchars($summativeValue); ?>"
                                        placeholder="Enter marks"
                                        class="w-full px-3 py-2.5 pr-14
                                               border border-slate-300
                                               rounded-md bg-white
                                               text-sm outline-none
                                               focus:border-blue-600
                                               focus:ring-2
                                               focus:ring-blue-100">

                                    <span class="absolute right-3 top-1/2
                                                 -translate-y-1/2
                                                 text-xs text-slate-400">
                                        / 50
                                    </span>

                                </div>

                            </div>

                        </div>

                    </div>


                    <!-- Automatic result preview -->
                    <div class="bg-slate-50 border border-slate-200
                                rounded-md p-5">

                        <div class="flex justify-between items-center
                                    mb-4">

                            <h3 class="text-sm font-semibold text-slate-800">
                                Results Preview
                            </h3>

                            <span class="text-xs text-slate-500">
                                Calculated automatically
                            </span>

                        </div>

                        <div class="grid grid-cols-2 gap-4">

                            <div>
                                <p class="text-xs text-slate-500 mb-1">
                                    Total Marks
                                </p>

                                <p id="totalMarks"
                                   class="text-2xl font-bold text-slate-900">
                                    0 / 100
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-slate-500 mb-1">
                                    Decision
                                </p>

                                <p id="decision"
                                   class="text-sm font-semibold text-slate-500 mt-2">
                                    Waiting for marks
                                </p>
                            </div>

                        </div>

                        <p class="text-xs text-slate-500 mt-4">
                            Competent: 70 marks or above.
                            Not Competent: Below 70 marks.
                        </p>

                    </div>


                    <!-- Form buttons -->
                    <div class="flex flex-col-reverse sm:flex-row
                                justify-end gap-3 pt-2">

                        <a href="listOfMarks.php"
                           class="px-5 py-2.5 border
                                  border-slate-300 rounded-md
                                  text-sm font-semibold
                                  text-slate-700 text-center
                                  hover:bg-slate-100 transition">

                            Cancel

                        </a>

                        <button
                            type="submit"
                            name="addMarks"
                            class="px-6 py-2.5 bg-blue-700
                                   text-white rounded-md
                                   text-sm font-semibold
                                   hover:bg-blue-800
                                   focus:ring-4
                                   focus:ring-blue-100
                                   transition">

                            Save Marks

                        </button>

                    </div>

                </form>

            </div>


            <!-- Additional information -->
            <div class="mt-5 flex items-start gap-3
                        text-sm text-slate-500">

                <span class="text-blue-700 font-semibold">i</span>

                <p>
                    Please verify the trainee and module before
                    saving marks. Each trainee can have only one
                    marks record per module.
                </p>

            </div>

        </div>

    </main>


<?php include("footer.php"); ?>


    <!-- JavaScript -->
    <script>

        // Automatically populate trainee code
        const traineeSelect = document.getElementById('Trainee_Id_Select');

        const traineeCode = document.getElementById('Trainee_Id');

        traineeSelect.addEventListener('change', function () {
            traineeCode.value = this.value;
        });


        // Assessment marks calculation
        const formative = document.getElementById('Formative_Assessment');

        const summative = document.getElementById('Summative_Assessment');

        const totalMarks = document.getElementById('totalMarks');

        const decision = document.getElementById('decision');


        function calculateMarks() {

            const f = formative.value;
            const s = summative.value;

            if (
                f === "" ||
                s === "" ||
                Number(f) < 0 ||
                Number(f) > 50 ||
                Number(s) < 0 ||
                Number(s) > 50
            ) {
                totalMarks.textContent = "0 / 100";

                decision.textContent = "Waiting for marks";

                decision.className =
                    "text-sm font-semibold text-slate-500 mt-2";

                return;
            }

            const total = Number(f) + Number(s);

            totalMarks.textContent = total + " / 100";

            if (total >= 70) {

                decision.textContent = "Competent";

                decision.className =
                    "text-sm font-semibold text-green-700 mt-2";

            } else {

                decision.textContent = "Not Competent";

                decision.className =
                    "text-sm font-semibold text-red-600 mt-2";

            }

        }


        formative.addEventListener('input', calculateMarks);

        summative.addEventListener('input', calculateMarks);

        // Calculate again when validation fails and values are restored
        calculateMarks();

    </script>

</body>
</html>
