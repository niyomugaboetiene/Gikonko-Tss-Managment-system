
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

$stmt = mysqli_prepare(
    $conn,
    "SELECT
        m.Mark_Id,
        m.Trainee_Id,
        m.Module_Id,
        m.Formative_Assessment,
        m.Summative_Assessment,
        m.Total_Marks,
        m.decision
    FROM marks m
    WHERE m.Mark_Id = ?"
);

if (!$stmt) {
    error_log("Marks query preparation failed: " . mysqli_error($conn));
    die("Unable to load assessment information.");
}

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

        $checkTrainee = mysqli_prepare(
            $conn,
            "SELECT Trainee_Id FROM trainees WHERE Trainee_Id = ?"
        );

        mysqli_stmt_bind_param($checkTrainee, "i", $trainee_code);
        mysqli_stmt_execute($checkTrainee);

        $traineeResult = mysqli_stmt_get_result($checkTrainee);
        $traineeExists = mysqli_num_rows($traineeResult) > 0;

        mysqli_stmt_close($checkTrainee);

        $checkModule = mysqli_prepare(
            $conn,
            "SELECT Module_Id FROM modules WHERE Module_Id = ?"
        );

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
            $update = mysqli_prepare(
                $conn,
                "UPDATE marks SET
                    Trainee_Id = ?,
                    Module_Id = ?,
                    Formative_Assessment = ?,
                    Summative_Assessment = ?,
                    Total_Marks = ?,
                    decision = ?
                WHERE Mark_Id = ?"
            );

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
    error_log("Unable to load trainee or module information: " . mysqli_error($conn));
    die("Unable to load trainee or module information.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Update Marks | GIKONKO TSS</title>

    <link rel="icon" type="image/png" href="gikonko.png">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background: #f8fafc;
            color: #1e293b;
            font-family: Arial, Helvetica, sans-serif;
        }

        .main-content {
            flex: 1;
            width: 100%;
            max-width: 1100px;
            margin: 0 auto;
            padding: 38px 24px 50px;
        }

        .page-heading {
            margin-bottom: 28px;
        }

        .page-eyebrow {
            display: inline-block;
            margin-bottom: 10px;
            color: #2563eb;
            font-size: 11px;
            font-weight: 750;
            letter-spacing: 1.2px;
            text-transform: uppercase;
        }

        .page-heading h1 {
            color: #1e293b;
            font-size: 28px;
            font-weight: 750;
            letter-spacing: -0.7px;
        }

        .page-heading p {
            margin-top: 9px;
            color: #64748b;
            font-size: 14px;
            line-height: 1.6;
        }

        .assessment-card {
            overflow: hidden;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 5px 18px rgba(15, 23, 42, 0.035);
        }

        .card-header {
            padding: 22px 26px;
            border-bottom: 1px solid #e2e8f0;
            background: #ffffff;
        }

        .card-header h2 {
            color: #1e293b;
            font-size: 17px;
            font-weight: 700;
        }

        .card-header p {
            margin-top: 7px;
            color: #64748b;
            font-size: 12px;
        }

        .record-id {
            display: inline-flex;
            align-items: center;
            margin-top: 11px;
            padding: 5px 10px;
            border-radius: 5px;
            background: #eff6ff;
            color: #1d4ed8;
            font-size: 12px;
            font-weight: 700;
        }

        .form-content {
            padding: 28px 26px;
        }

        .form-group {
            margin-bottom: 23px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 22px;
        }

        label {
            display: block;
            margin-bottom: 9px;
            color: #334155;
            font-size: 13px;
            font-weight: 650;
        }

        .label-hint {
            color: #94a3b8;
            font-weight: 400;
        }

        input,
        select {
            width: 100%;
            height: 47px;
            padding: 0 14px;
            border: 1px solid #cbd5e1;
            border-radius: 7px;
            outline: none;
            background: #ffffff;
            color: #334155;
            font-family: inherit;
            font-size: 13px;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        input:focus,
        select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12);
        }

        select {
            cursor: pointer;
        }

        .error-message {
            padding: 14px 16px;
            margin-bottom: 24px;
            border: 1px solid #fecaca;
            border-radius: 7px;
            background: #fef2f2;
            color: #b91c1c;
            font-size: 13px;
            line-height: 1.5;
        }

        .summary-panel {
            margin-top: 4px;
            margin-bottom: 28px;
            padding: 23px;
            border: 1px solid #e2e8f0;
            border-radius: 9px;
            background: #f8fafc;
        }

        .summary-panel h3 {
            margin-bottom: 21px;
            color: #334155;
            font-size: 14px;
            font-weight: 700;
        }

        .summary-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }

        .summary-label {
            color: #64748b;
            font-size: 11px;
            font-weight: 650;
            letter-spacing: 0.8px;
            text-transform: uppercase;
        }

        .total-value {
            margin-top: 8px;
            color: #1e293b;
            font-size: 32px;
            font-weight: 800;
            letter-spacing: -1px;
        }

        .total-value small {
            color: #94a3b8;
            font-size: 15px;
            font-weight: 500;
            letter-spacing: 0;
        }

        .decision-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-top: 9px;
            padding: 8px 13px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 700;
        }

        .decision-badge.competent {
            background: #dcfce7;
            color: #15803d;
        }

        .decision-badge.not-competent {
            background: #fee2e2;
            color: #b91c1c;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 12px;
            padding-top: 5px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 43px;
            padding: 0 21px;
            border-radius: 7px;
            font-family: inherit;
            font-size: 13px;
            font-weight: 650;
            text-decoration: none;
            cursor: pointer;
            transition: background 0.2s, border-color 0.2s;
        }

        .btn-cancel {
            border: 1px solid #cbd5e1;
            background: #ffffff;
            color: #475569;
        }

        .btn-cancel:hover {
            background: #f1f5f9;
            border-color: #94a3b8;
        }

        .btn-save {
            border: 1px solid #1d4ed8;
            background: #1d4ed8;
            color: #ffffff;
        }

        .btn-save:hover {
            background: #1e40af;
            border-color: #1e40af;
        }

        @media (max-width: 650px) {
            .main-content {
                padding: 28px 15px 38px;
            }

            .page-heading h1 {
                font-size: 23px;
            }

            .card-header,
            .form-content {
                padding: 21px 18px;
            }

            .form-grid {
                grid-template-columns: 1fr;
                gap: 0;
            }

            .summary-panel {
                padding: 19px;
            }
        }

        @media (max-width: 420px) {
            .summary-content {
                align-items: flex-start;
                flex-direction: column;
                gap: 22px;
            }

            .form-actions {
                flex-direction: column-reverse;
                align-items: stretch;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
</head>

<body>

    <header>
        <?php include("Dashboard.php"); ?>
    </header>

    <main class="main-content">

        <div class="page-heading">
            <span class="page-eyebrow">Academic Management</span>

            <h1>Update Assessment Marks</h1>

            <p>
                Edit trainee assessment information and update the competency result.
            </p>
        </div>

        <section class="assessment-card">

            <div class="card-header">
                <h2>Assessment Information</h2>

                <p>
                    Edit the assessment record and review the updated marks.
                </p>

                <span class="record-id">
                    Record #<?php echo escapeMarks($data['Mark_Id']); ?>
                </span>
            </div>

            <form method="POST" class="form-content">

                <?php if ($error !== ''): ?>
                    <div class="error-message">
                        <?php echo escapeMarks($error); ?>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="Trainee_Id_Select">Select Trainee</label>

                    <select
                        id="Trainee_Id_Select"
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

                <div class="form-group">
                    <label for="Module_Id">Select Module</label>

                    <select
                        id="Module_Id"
                        name="Module_Id"
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

                <div class="form-grid">

                    <div class="form-group">
                        <label for="Formative_Assessment">
                            Formative Assessment
                            <span class="label-hint">/ 50</span>
                        </label>

                        <input
                            type="number"
                            id="Formative_Assessment"
                            name="Formative_Assessment"
                            min="0"
                            max="50"
                            step="0.01"
                            value="<?php echo escapeMarks($data['Formative_Assessment']); ?>"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="Summative_Assessment">
                            Summative Assessment
                            <span class="label-hint">/ 50</span>
                        </label>

                        <input
                            type="number"
                            id="Summative_Assessment"
                            name="Summative_Assessment"
                            min="0"
                            max="50"
                            step="0.01"
                            value="<?php echo escapeMarks($data['Summative_Assessment']); ?>"
                            required
                        >
                    </div>

                </div>

                <div class="summary-panel">

                    <h3>Assessment Summary</h3>

                    <div class="summary-content">

                        <div>
                            <p class="summary-label">Total Marks</p>

                            <p class="total-value">
                                <span id="totalMarks"><?php echo escapeMarks($data['Total_Marks']); ?></span>
                                <small>/ 100</small>
                            </p>
                        </div>

                        <div>
                            <p class="summary-label">Competency Decision</p>

                            <span
                                id="decisionBadge"
                                class="decision-badge <?php echo $data['Total_Marks'] >= 70 ? 'competent' : 'not-competent'; ?>"
                            >
                                <?php echo escapeMarks($data['Total_Marks'] >= 70 ? 'Competent' : 'Not Competent'); ?>
                            </span>
                        </div>

                    </div>

                </div>

                <div class="form-actions">

                    <a href="listOfMarks.php" class="btn btn-cancel">
                        Cancel
                    </a>

                    <button
                        type="submit"
                        name="save"
                        class="btn btn-save"
                    >
                        Save Changes
                    </button>

                </div>

            </form>

        </section>

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

            totalMarks.textContent = total.toFixed(2).replace(/\.?0+$/, '');

            decisionBadge.textContent = competent
                ? 'Competent'
                : 'Not Competent';

            decisionBadge.className = competent
                ? 'decision-badge competent'
                : 'decision-badge not-competent';
        }

        formativeInput.addEventListener('input', updateAssessmentSummary);
        summativeInput.addEventListener('input', updateAssessmentSummary);

        updateAssessmentSummary();
    </script>

</body>
</html>
