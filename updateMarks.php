
<?php
include("conn.php");
session_start();

if (!isset($_SESSION['username'])) {
    $_SESSION['login_error'] = "Please login to access this page";
    header("Location: login.php");
    exit();
}

$error = "";
$data = null;

if (!isset($_GET['Mark_Id']) || !ctype_digit((string)$_GET['Mark_Id'])) {
    header("Location: listOfMarks.php");
    exit();
}

$Mark_Id = (int)$_GET['Mark_Id'];

$stmt = mysqli_prepare($conn, "SELECT
    m.Mark_Id,
    m.Trainee_Id,
    m.Module_Id,
    m.Formative_Assessment,
    m.Summative_Assessment,
    m.Total_Marks,
    m.decision
    FROM marks m
    WHERE m.Mark_Id = ?");

mysqli_stmt_bind_param($stmt, "i", $Mark_Id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    mysqli_stmt_close($stmt);
    header("Location: listOfMarks.php");
    exit();
}

$data = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (isset($_POST['save'])) {
    $trainee_code = trim($_POST['Trainee_Id'] ?? '');
    $Module_Id = trim($_POST['Module_Id'] ?? '');
    $Formative = trim($_POST['Formative_Assessment'] ?? '');
    $Summative = trim($_POST['Summative_Assessment'] ?? '');

    if (
        $trainee_code === '' ||
        $Module_Id === '' ||
        $Formative === '' ||
        $Summative === ''
    ) {
        $error = "Please fill in all required fields.";
    } elseif (
        !ctype_digit($trainee_code) ||
        !ctype_digit($Module_Id) ||
        !is_numeric($Formative) ||
        !is_numeric($Summative)
    ) {
        $error = "Please enter valid trainee, module and mark values.";
    } elseif (
        (float)$Formative < 0 ||
        (float)$Formative > 50 ||
        (float)$Summative < 0 ||
        (float)$Summative > 50
    ) {
        $error = "Each assessment mark must be between 0 and 50.";
    } else {
        $Formative = (float)$Formative;
        $Summative = (float)$Summative;
        $Total = $Formative + $Summative;
        $Decision = $Total >= 70 ? "Competent" : "Not Competent";

        $checkTrainee = mysqli_prepare($conn, "SELECT Trainee_Id FROM trainees WHERE Trainee_Id = ?");
        mysqli_stmt_bind_param($checkTrainee, "i", $trainee_code);
        mysqli_stmt_execute($checkTrainee);
        $traineeResult = mysqli_stmt_get_result($checkTrainee);
        $traineeExists = mysqli_num_rows($traineeResult) > 0;
        mysqli_stmt_close($checkTrainee);

        $checkModule = mysqli_prepare($conn, "SELECT Module_Id FROM modules WHERE Module_Id = ?");
        mysqli_stmt_bind_param($checkModule, "i", $Module_Id);
        mysqli_stmt_execute($checkModule);
        $moduleResult = mysqli_stmt_get_result($checkModule);
        $moduleExists = mysqli_num_rows($moduleResult) > 0;
        mysqli_stmt_close($checkModule);

        if (!$traineeExists) {
            $error = "The selected trainee does not exist.";
        } elseif (!$moduleExists) {
            $error = "The selected module does not exist.";
        } else {
            $update = mysqli_prepare($conn, "UPDATE marks SET
                Trainee_Id = ?,
                Module_Id = ?,
                Formative_Assessment = ?,
                Summative_Assessment = ?,
                Total_Marks = ?,
                decision = ?
                WHERE Mark_Id = ?");

            mysqli_stmt_bind_param(
                $update,
                "iidddsi",
                $trainee_code,
                $Module_Id,
                $Formative,
                $Summative,
                $Total,
                $Decision,
                $Mark_Id
            );

            if (mysqli_stmt_execute($update)) {
                mysqli_stmt_close($update);
                header("Location: listOfMarks.php");
                exit();
            } else {
                $error = "Unable to update marks. Please try again.";
            }

            mysqli_stmt_close($update);
        }
    }

    $data['Trainee_Id'] = $trainee_code;
    $data['Module_Id'] = $Module_Id;
    $data['Formative_Assessment'] = $Formative;
    $data['Summative_Assessment'] = $Summative;
    $data['Total_Marks'] = is_numeric($Formative) && is_numeric($Summative)
        ? (float)$Formative + (float)$Summative
        : $data['Total_Marks'];
}

function escapeMarks($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$trainees = mysqli_query(
    $conn,
    "SELECT Trainee_Id, CONCAT(Firstname, ' ', lastname) AS Trainee_Name
     FROM trainees
     ORDER BY Firstname ASC"
);

$modules = mysqli_query(
    $conn,
    "SELECT Module_Id, Module_Name
     FROM modules
     ORDER BY Module_Name ASC"
);

if (!$trainees || !$modules) {
    die("Unable to load trainee or module information.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Marks | GIKONKO TSS</title>
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
                Update Assessment Marks
            </h1>

            <p class="text-sm text-slate-500 mt-2">
                Edit trainee assessment information and update the competency result.
            </p>
        </div>

        <div class="bg-white border border-slate-200 rounded-lg shadow-sm overflow-hidden">

            <div class="px-6 py-5 border-b border-slate-200">
                <h2 class="text-lg font-semibold text-slate-800">
                    Assessment Information
                </h2>

                <p class="text-sm text-slate-500 mt-1">
                    Mark record #<?php echo escapeMarks($data['Mark_Id']); ?>
                </p>
            </div>

            <form method="POST" class="p-6 space-y-6">

                <?php if ($error !== ''): ?>
                    <div class="p-4 rounded-md bg-red-50 border border-red-200 text-red-700 text-sm">
                        <?php echo escapeMarks($error); ?>
                    </div>
                <?php endif; ?>

                <div>
                    <label for="Trainee_Id_Select" class="block text-sm font-semibold text-slate-700 mb-2">
                        Select Trainee
                    </label>

                    <select
                        id="Trainee_Id_Select"
                        class="w-full px-4 py-3 bg-white border border-slate-300 rounded-md text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        required
                    >
                        <option value="">Select a trainee</option>

                        <?php while ($trainee = mysqli_fetch_assoc($trainees)): ?>
                            <option
                                value="<?php echo escapeMarks($trainee['Trainee_Id']); ?>"
                                <?php echo (string)$data['Trainee_Id'] === (string)$trainee['Trainee_Id'] ? 'selected' : ''; ?>
                            >
                                <?php echo escapeMarks($trainee['Trainee_Name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>

                    <input
                        type="hidden"
                        name="Trainee_Id"
                        id="Trainee_Id"
                        value="<?php echo escapeMarks($data['Trainee_Id']); ?>"
                    >
                </div>

                <div>
                    <label for="Module_Id" class="block text-sm font-semibold text-slate-700 mb-2">
                        Select Module
                    </label>

                    <select
                        id="Module_Id"
                        name="Module_Id"
                        class="w-full px-4 py-3 bg-white border border-slate-300 rounded-md text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        required
                    >
                        <option value="">Select a module</option>

                        <?php while ($module = mysqli_fetch_assoc($modules)): ?>
                            <option
                                value="<?php echo escapeMarks($module['Module_Id']); ?>"
                                <?php echo (string)$data['Module_Id'] === (string)$module['Module_Id'] ? 'selected' : ''; ?>
                            >
                                <?php echo escapeMarks($module['Module_Name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

                    <div>
                        <label for="Formative_Assessment" class="block text-sm font-semibold text-slate-700 mb-2">
                            Formative Assessment
                            <span class="text-slate-400 font-normal">/ 50</span>
                        </label>

                        <input
                            type="number"
                            id="Formative_Assessment"
                            name="Formative_Assessment"
                            min="0"
                            max="50"
                            step="0.01"
                            value="<?php echo escapeMarks($data['Formative_Assessment']); ?>"
                            class="w-full px-4 py-3 bg-white border border-slate-300 rounded-md text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            required
                        >
                    </div>

                    <div>
                        <label for="Summative_Assessment" class="block text-sm font-semibold text-slate-700 mb-2">
                            Summative Assessment
                            <span class="text-slate-400 font-normal">/ 50</span>
                        </label>

                        <input
                            type="number"
                            id="Summative_Assessment"
                            name="Summative_Assessment"
                            min="0"
                            max="50"
                            step="0.01"
                            value="<?php echo escapeMarks($data['Summative_Assessment']); ?>"
                            class="w-full px-4 py-3 bg-white border border-slate-300 rounded-md text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            required
                        >
                    </div>

                </div>

                <div class="bg-slate-50 border border-slate-200 rounded-lg p-5">

                    <p class="text-sm font-semibold text-slate-700 mb-3">
                        Assessment Summary
                    </p>

                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

                        <div>
                            <p class="text-xs text-slate-500 uppercase tracking-wide">
                                Total Marks
                            </p>

                            <p class="text-3xl font-bold text-slate-800 mt-1">
                                <span id="totalMarks"><?php echo escapeMarks($data['Total_Marks']); ?></span>
                                <span class="text-base font-medium text-slate-400">/ 100</span>
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-slate-500 uppercase tracking-wide mb-2">
                                Competency Decision
                            </p>

                            <span
                                id="decisionBadge"
                                class="inline-flex px-3 py-1.5 rounded-full text-sm font-semibold"
                            >
                                <?php echo escapeMarks($data['Total_Marks'] >= 70 ? 'Competent' : 'Not Competent'); ?>
                            </span>
                        </div>

                    </div>
                </div>

                <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3 pt-2">

                    <a href="listOfMarks.php"
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
        </div>

    </main>

    <?php include("footer.php"); ?>

    <script>
        const traineeSelect = document.getElementById('Trainee_Id_Select');
        const traineeInput = document.getElementById('Trainee_Id');

        const formativeInput = document.getElementById('Formative_Assessment');
        const summativeInput = document.getElementById('Summative_Assessment');

        const totalMarks = document.getElementById('totalMarks');
        const decisionBadge = document.getElementById('decisionBadge');

        traineeSelect.addEventListener('change', function () {
            traineeInput.value = this.value;
        });

        function updateAssessmentSummary() {
            const formative = parseFloat(formativeInput.value) || 0;
            const summative = parseFloat(summativeInput.value) || 0;

            const total = formative + summative;
            const competent = total >= 70;

            totalMarks.textContent = total.toFixed(2).replace(/\.00$/, '');

            decisionBadge.textContent = competent ? 'Competent' : 'Not Competent';

            decisionBadge.className = competent
                ? 'inline-flex px-3 py-1.5 rounded-full text-sm font-semibold bg-green-50 text-green-700'
                : 'inline-flex px-3 py-1.5 rounded-full text-sm font-semibold bg-red-50 text-red-700';
        }

        formativeInput.addEventListener('input', updateAssessmentSummary);
        summativeInput.addEventListener('input', updateAssessmentSummary);

        updateAssessmentSummary();
    </script>

</body>
</html>
