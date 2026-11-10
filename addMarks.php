
<?php
session_start();
include("conn.php");

if (!isset($_SESSION['username'])) {
    $_SESSION['login_error'] = "Please login to access this page";
    header("Location: index.php");
    exit();
}

$error = "";

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
        !ctype_digit((string)$selectedTrainee) ||
        !ctype_digit((string)$selectedModule) ||
        !is_numeric($formativeValue) ||
        !is_numeric($summativeValue) ||
        $formativeValue < 0 ||
        $formativeValue > 50 ||
        $summativeValue < 0 ||
        $summativeValue > 50
    ) {
        $error = "Please provide valid trainee, module and assessment values.";
    } else {

        $Formative = (float)$formativeValue;
        $Summative = (float)$summativeValue;

        $checkTrainee = mysqli_prepare(
            $conn,
            "SELECT Trainee_Id FROM trainees WHERE Trainee_Id = ?"
        );

        mysqli_stmt_bind_param($checkTrainee, "i", $selectedTrainee);
        mysqli_stmt_execute($checkTrainee);
        mysqli_stmt_store_result($checkTrainee);

        if (mysqli_stmt_num_rows($checkTrainee) == 0) {
            $error = "Selected trainee does not exist.";
        } else {

            $checkModule = mysqli_prepare(
                $conn,
                "SELECT Module_Id FROM modules WHERE Module_Id = ?"
            );

            mysqli_stmt_bind_param($checkModule, "i", $selectedModule);
            mysqli_stmt_execute($checkModule);
            mysqli_stmt_store_result($checkModule);

            if (mysqli_stmt_num_rows($checkModule) == 0) {
                $error = "Selected module does not exist.";
            } else {

                $checkMarks = mysqli_prepare(
                    $conn,
                    "SELECT Trainee_Id FROM marks
                     WHERE Trainee_Id = ? AND Module_Id = ?"
                );

                mysqli_stmt_bind_param(
                    $checkMarks,
                    "ii",
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

                    $insertMarks = mysqli_prepare(
                        $conn,
                        "INSERT INTO marks
                        (Trainee_Id, Module_Id, Formative_Assessment,
                        Summative_Assessment, Total_Marks, decision)
                        VALUES (?, ?, ?, ?, ?, ?)"
                    );

                    mysqli_stmt_bind_param(
                        $insertMarks,
                        "iiddds",
                        $selectedTrainee,
                        $selectedModule,
                        $Formative,
                        $Summative,
                        $Total,
                        $Decision
                    );

                    if (mysqli_stmt_execute($insertMarks)) {
                        mysqli_stmt_close($insertMarks);
                        mysqli_stmt_close($checkMarks);
                        mysqli_stmt_close($checkModule);
                        mysqli_stmt_close($checkTrainee);

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

$traineesQuery = mysqli_query(
    $conn,
    "SELECT Trainee_Id,
    CONCAT(Firstname, ' ', lastname) AS Trainee_Name
    FROM trainees ORDER BY Firstname ASC"
);

$modulesQuery = mysqli_query(
    $conn,
    "SELECT Module_Id, Module_Name
    FROM modules ORDER BY Module_Name ASC"
);

function escapeAddMarks($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Add Marks | GIKONKO TSS</title>

    <link rel="icon" href="gikonko.png" type="image/png">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            background: #f8fafc;
            color: #1e293b;
            font-family: Arial, Helvetica, sans-serif;
        }

        a {
            text-decoration: none;
        }

        button,
        input,
        select {
            font: inherit;
        }

        .page-container {
            width: 100%;
            max-width: 1280px;
            margin: 0 auto;
            padding: 35px 24px 50px;
        }

        .breadcrumb {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 22px;
            font-size: 13px;
            color: #64748b;
        }

        .breadcrumb a {
            color: #64748b;
            transition: color 0.2s;
        }

        .breadcrumb a:hover {
            color: #1d4ed8;
        }

        .breadcrumb-current {
            color: #1e293b;
            font-weight: 600;
        }

        .page-heading {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            margin-bottom: 30px;
        }

        .page-title {
            color: #0f172a;
            font-size: 29px;
            font-weight: 750;
            letter-spacing: -0.6px;
        }

        .page-description {
            color: #64748b;
            font-size: 14px;
            line-height: 1.6;
            margin-top: 9px;
        }

        .back-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            padding: 11px 16px;
            background: #ffffff;
            color: #334155;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            white-space: nowrap;
            transition: all 0.2s;
        }

        .back-button:hover {
            background: #f1f5f9;
            border-color: #94a3b8;
        }

        .form-wrapper {
            max-width: 850px;
        }

        .form-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 9px;
            box-shadow: 0 3px 12px rgba(15, 23, 42, 0.04);
            overflow: hidden;
        }

        .form-card-header {
            padding: 23px 26px;
            border-bottom: 1px solid #e2e8f0;
        }

        .form-card-header h2 {
            font-size: 17px;
            font-weight: 700;
            color: #0f172a;
        }

        .form-card-header p {
            margin-top: 7px;
            font-size: 13px;
            color: #64748b;
        }

        .error-message {
            margin: 20px 26px 0;
            padding: 13px 15px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 6px;
            color: #b91c1c;
            font-size: 13px;
            line-height: 1.5;
        }

        .error-icon {
            font-weight: 800;
        }

        .form-body {
            padding: 26px;
        }

        .form-section {
            margin-bottom: 25px;
        }

        .section-heading {
            margin-bottom: 17px;
        }

        .section-heading h3 {
            font-size: 14px;
            font-weight: 700;
            color: #1e293b;
        }

        .section-heading p {
            font-size: 12px;
            color: #64748b;
            margin-top: 6px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px;
        }

        .form-group {
            min-width: 0;
        }

        .form-label {
            display: block;
            margin-bottom: 9px;
            color: #334155;
            font-size: 13px;
            font-weight: 600;
        }

        .required {
            color: #dc2626;
        }

        .form-control {
            width: 100%;
            height: 44px;
            padding: 0 13px;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            color: #334155;
            font-size: 13px;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-control:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        }

        .form-control::placeholder {
            color: #94a3b8;
        }

        .form-control:disabled,
        .form-control[readonly] {
            background: #f1f5f9;
            border-color: #e2e8f0;
            color: #64748b;
            cursor: not-allowed;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper .form-control {
            padding-right: 55px;
        }

        .input-suffix {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 12px;
            color: #94a3b8;
            pointer-events: none;
        }

        .form-divider {
            height: 1px;
            background: #f1f5f9;
            margin: 25px 0;
        }

        .result-preview {
            padding: 21px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 7px;
            margin-bottom: 25px;
        }

        .preview-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
        }

        .preview-header h3 {
            font-size: 14px;
            font-weight: 700;
            color: #1e293b;
        }

        .preview-header span {
            font-size: 11px;
            color: #64748b;
        }

        .preview-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .preview-label {
            color: #64748b;
            font-size: 12px;
            margin-bottom: 7px;
        }

        .preview-total {
            color: #0f172a;
            font-size: 25px;
            font-weight: 750;
        }

        .preview-decision {
            display: inline-block;
            margin-top: 5px;
            font-size: 14px;
            font-weight: 700;
            color: #64748b;
        }

        .decision-competent {
            color: #15803d;
        }

        .decision-not-competent {
            color: #dc2626;
        }

        .preview-note {
            margin-top: 18px;
            color: #64748b;
            font-size: 12px;
            line-height: 1.6;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 12px;
            padding-top: 5px;
        }

        .cancel-button,
        .submit-button {
            min-height: 43px;
            padding: 0 21px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
        }

        .cancel-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #475569;
            background: #ffffff;
            border: 1px solid #cbd5e1;
        }

        .cancel-button:hover {
            background: #f1f5f9;
        }

        .submit-button {
            color: #ffffff;
            background: #1d4ed8;
            border: 1px solid #1d4ed8;
        }

        .submit-button:hover {
            background: #1e40af;
            border-color: #1e40af;
        }

        .information-note {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-top: 20px;
            color: #64748b;
            font-size: 12px;
            line-height: 1.7;
        }

        .information-icon {
            color: #2563eb;
            font-weight: 800;
        }

        @media (max-width: 700px) {
            .page-container {
                padding: 25px 16px 40px;
            }

            .page-heading {
                align-items: flex-start;
                flex-direction: column;
            }

            .page-title {
                font-size: 24px;
            }

            .back-button {
                width: 100%;
            }

            .form-grid {
                grid-template-columns: 1fr;
                gap: 17px;
            }

            .form-card-header,
            .form-body {
                padding: 20px;
            }

            .error-message {
                margin-left: 20px;
                margin-right: 20px;
            }
        }

        @media (max-width: 450px) {
            .preview-header {
                align-items: flex-start;
                flex-direction: column;
            }

            .preview-total {
                font-size: 22px;
            }

            .form-actions {
                flex-direction: column-reverse;
            }

            .cancel-button,
            .submit-button {
                width: 100%;
            }
        }
    </style>
</head>

<body>

    <header>
        <?php include("Dashboard.php"); ?>
    </header>

    <main class="page-container">

        <div class="breadcrumb">
            <a href="listOfMarks.php">Marks Management</a>
            <span>/</span>
            <span class="breadcrumb-current">Add Marks</span>
        </div>

        <div class="page-heading">

            <div>
                <h1 class="page-title">Add Student Marks</h1>

                <p class="page-description">
                    Record formative and summative assessment results for your trainees.
                </p>
            </div>

            <a href="listOfMarks.php" class="back-button">
                <span>&larr;</span>
                Back to Marks
            </a>

        </div>

        <div class="form-wrapper">

            <div class="form-card">

                <div class="form-card-header">
                    <h2>Assessment Information</h2>
                    <p>Enter the trainee's assessment details below.</p>
                </div>

                <?php if (!empty($error)): ?>

                    <div class="error-message">
                        <span class="error-icon">!</span>
                        <p><?php echo escapeAddMarks($error); ?></p>
                    </div>

                <?php endif; ?>

                <form method="POST" class="form-body">

                    <section class="form-section">

                        <div class="section-heading">
                            <h3>Trainee Details</h3>
                            <p>Select the trainee whose marks you want to record.</p>
                        </div>

                        <div class="form-grid">

                            <div class="form-group">

                                <label for="Trainee_Id_Select" class="form-label">
                                    Trainee Name <span class="required">*</span>
                                </label>

                                <select
                                    id="Trainee_Id_Select"
                                    class="form-control"
                                    required
                                >
                                    <option value="">Select a trainee</option>

                                    <?php if ($traineesQuery): ?>
                                        <?php while ($data = mysqli_fetch_assoc($traineesQuery)): ?>

                                            <option
                                                value="<?php echo escapeAddMarks($data['Trainee_Id']); ?>"
                                                <?php echo ($selectedTrainee == $data['Trainee_Id']) ? 'selected' : ''; ?>
                                            >
                                                <?php echo escapeAddMarks($data['Trainee_Name']); ?>
                                            </option>

                                        <?php endwhile; ?>
                                    <?php endif; ?>

                                </select>

                            </div>

                            <div class="form-group">

                                <label for="Trainee_Id" class="form-label">
                                    Trainee Code
                                </label>

                                <input
                                    type="text"
                                    name="Trainee_Id"
                                    id="Trainee_Id"
                                    value="<?php echo escapeAddMarks($selectedTrainee); ?>"
                                    placeholder="Automatically populated"
                                    readonly
                                    required
                                    class="form-control"
                                >

                            </div>

                        </div>

                    </section>

                    <div class="form-divider"></div>

                    <section class="form-section">

                        <div class="section-heading">
                            <h3>Module Information</h3>
                        </div>

                        <div class="form-group">

                            <label for="Module_Id" class="form-label">
                                Module Name <span class="required">*</span>
                            </label>

                            <select
                                id="Module_Id"
                                name="Module_Id"
                                required
                                class="form-control"
                            >
                                <option value="">Select a module</option>

                                <?php if ($modulesQuery): ?>
                                    <?php while ($data = mysqli_fetch_assoc($modulesQuery)): ?>

                                        <option
                                            value="<?php echo escapeAddMarks($data['Module_Id']); ?>"
                                            <?php echo ($selectedModule == $data['Module_Id']) ? 'selected' : ''; ?>
                                        >
                                            <?php echo escapeAddMarks($data['Module_Name']); ?>
                                        </option>

                                    <?php endwhile; ?>
                                <?php endif; ?>

                            </select>

                        </div>

                    </section>

                    <div class="form-divider"></div>

                    <section class="form-section">

                        <div class="section-heading">
                            <h3>Assessment Results</h3>
                            <p>Each assessment carries a maximum of 50 marks.</p>
                        </div>

                        <div class="form-grid">

                            <div class="form-group">

                                <label for="Formative_Assessment" class="form-label">
                                    Formative Assessment <span class="required">*</span>
                                </label>

                                <div class="input-wrapper">

                                    <input
                                        type="number"
                                        id="Formative_Assessment"
                                        name="Formative_Assessment"
                                        min="0"
                                        max="50"
                                        step="1"
                                        required
                                        value="<?php echo escapeAddMarks($formativeValue); ?>"
                                        placeholder="Enter marks"
                                        class="form-control"
                                    >

                                    <span class="input-suffix">/ 50</span>

                                </div>

                            </div>

                            <div class="form-group">

                                <label for="Summative_Assessment" class="form-label">
                                    Summative Assessment <span class="required">*</span>
                                </label>

                                <div class="input-wrapper">

                                    <input
                                        type="number"
                                        id="Summative_Assessment"
                                        name="Summative_Assessment"
                                        min="0"
                                        max="50"
                                        step="1"
                                        required
                                        value="<?php echo escapeAddMarks($summativeValue); ?>"
                                        placeholder="Enter marks"
                                        class="form-control"
                                    >

                                    <span class="input-suffix">/ 50</span>

                                </div>

                            </div>

                        </div>

                    </section>

                    <div class="result-preview">

                        <div class="preview-header">
                            <h3>Results Preview</h3>
                            <span>Calculated automatically</span>
                        </div>

                        <div class="preview-grid">

                            <div>
                                <p class="preview-label">Total Marks</p>
                                <p id="totalMarks" class="preview-total">0 / 100</p>
                            </div>

                            <div>
                                <p class="preview-label">Decision</p>
                                <p id="decision" class="preview-decision">
                                    Waiting for marks
                                </p>
                            </div>

                        </div>

                        <p class="preview-note">
                            Competent: 70 marks or above.
                            Not Competent: Below 70 marks.
                        </p>

                    </div>

                    <div class="form-actions">

                        <a href="listOfMarks.php" class="cancel-button">
                            Cancel
                        </a>

                        <button
                            type="submit"
                            name="addMarks"
                            class="submit-button"
                        >
                            Save Marks
                        </button>

                    </div>

                </form>

            </div>

            <div class="information-note">
                <span class="information-icon">i</span>
                <p>
                    Please verify the trainee and module before saving marks.
                    Each trainee can have only one marks record per module.
                </p>
            </div>

        </div>

    </main>

    <?php include("footer.php"); ?>

    <script>
        const traineeSelect = document.getElementById('Trainee_Id_Select');
        const traineeCode = document.getElementById('Trainee_Id');

        traineeSelect.addEventListener('change', function () {
            traineeCode.value = this.value;
        });

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
                decision.className = "preview-decision";
                return;
            }

            const total = Number(f) + Number(s);

            totalMarks.textContent = total + " / 100";

            if (total >= 70) {
                decision.textContent = "Competent";
                decision.className = "preview-decision decision-competent";
            } else {
                decision.textContent = "Not Competent";
                decision.className = "preview-decision decision-not-competent";
            }
        }

        formative.addEventListener('input', calculateMarks);
        summative.addEventListener('input', calculateMarks);

        calculateMarks();
    </script>

</body>
</html>
