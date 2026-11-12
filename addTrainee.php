<?php
session_start();
include("conn.php");

if (!isset($_SESSION['username'])) {
    $_SESSION['login_error'] = "Please login to access this page";
    header("Location: index.php");
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
    } elseif (!ctype_digit((string)$tradeId)) {
        $error = "Please select a valid trade.";
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

    <link rel="icon" type="image/png" href="gikonko.png">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            background: #f8fafc;
            color: #334155;
            font-family: Arial, Helvetica, sans-serif;
        }

        .main-container {
            width: 100%;
            max-width: 1280px;
            margin: 0 auto;
            padding: 35px 24px 50px;
        }

        .page-heading {
            margin-bottom: 30px;
        }

        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
            font-size: 13px;
            color: #64748b;
        }

        .breadcrumb a {
            color: #64748b;
            text-decoration: none;
            transition: color 0.2s;
        }

        .breadcrumb a:hover {
            color: #1d4ed8;
        }

        .breadcrumb span:last-child {
            color: #1e293b;
            font-weight: 600;
        }

        .heading-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
        }

        .heading-content h1 {
            color: #0f172a;
            font-size: 29px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .heading-content p {
            margin-top: 9px;
            color: #64748b;
            font-size: 14px;
        }

        .back-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            padding: 11px 17px;
            background: #ffffff;
            color: #334155;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            transition: 0.2s;
            white-space: nowrap;
        }

        .back-button:hover {
            background: #f1f5f9;
            border-color: #94a3b8;
        }

        .form-container {
            max-width: 850px;
        }

        .form-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 9px;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
            overflow: hidden;
        }

        .form-header {
            padding: 22px 26px;
            border-bottom: 1px solid #e2e8f0;
        }

        .form-header h2 {
            color: #0f172a;
            font-size: 18px;
            font-weight: 700;
        }

        .form-header p {
            color: #64748b;
            font-size: 13px;
            margin-top: 7px;
        }

        .error-message {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin: 22px 26px 0;
            padding: 13px 15px;
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 6px;
            color: #b91c1c;
            font-size: 13px;
            line-height: 1.5;
        }

        .error-icon {
            font-weight: 700;
            font-size: 15px;
        }

        .form-body {
            padding: 26px;
        }

        .section-title {
            color: #0f172a;
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .section-description {
            color: #64748b;
            font-size: 12px;
            margin-bottom: 22px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px;
        }

        .form-group {
            width: 100%;
        }

        .form-group label {
            display: block;
            color: #334155;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 9px;
        }

        .required {
            color: #dc2626;
        }

        .form-control {
            width: 100%;
            padding: 12px 13px;
            background: #ffffff;
            color: #334155;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            outline: none;
            font-size: 13px;
            font-family: inherit;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-control::placeholder {
            color: #94a3b8;
        }

        .form-control:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.10);
        }

        select.form-control {
            cursor: pointer;
        }

        .form-divider {
            height: 1px;
            background: #e2e8f0;
            margin: 28px 0;
        }

        .academic-section {
            margin-bottom: 26px;
        }

        .information-box {
            display: flex;
            align-items: flex-start;
            gap: 13px;
            padding: 17px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            margin-top: 28px;
        }

        .information-icon {
            width: 21px;
            height: 21px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            background: #dbeafe;
            color: #1d4ed8;
            border-radius: 50%;
            font-size: 12px;
            font-weight: 700;
        }

        .information-box h3 {
            color: #1e293b;
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .information-box p {
            color: #64748b;
            font-size: 12px;
            line-height: 1.6;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 28px;
        }

        .cancel-button,
        .submit-button {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            padding: 12px 21px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            font-family: inherit;
            text-decoration: none;
            cursor: pointer;
            transition: 0.2s;
        }

        .cancel-button {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            color: #475569;
        }

        .cancel-button:hover {
            background: #f1f5f9;
        }

        .submit-button {
            background: #1d4ed8;
            border: 1px solid #1d4ed8;
            color: #ffffff;
        }

        .submit-button:hover {
            background: #1e40af;
            border-color: #1e40af;
        }

        .bottom-note {
            margin-top: 18px;
            color: #64748b;
            font-size: 13px;
            line-height: 1.6;
        }

        @media (max-width: 650px) {
            .main-container {
                padding: 25px 16px 40px;
            }

            .heading-content {
                align-items: flex-start;
                flex-direction: column;
            }

            .heading-content h1 {
                font-size: 24px;
            }

            .back-button {
                width: 100%;
            }

            .form-grid {
                grid-template-columns: 1fr;
                gap: 18px;
            }

            .form-header,
            .form-body {
                padding: 20px;
            }

            .error-message {
                margin-left: 20px;
                margin-right: 20px;
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

    <main class="main-container">

        <div class="page-heading">

            <div class="breadcrumb">
                <a href="listOfTrainee.php">Trainee Management</a>
                <span>/</span>
                <span>Add Trainee</span>
            </div>

            <div class="heading-content">

                <div>
                    <h1>Register New Trainee</h1>
                    <p>Add a new trainee and assign them to an academic trade.</p>
                </div>

                <a href="listOfTrainee.php" class="back-button">
                    <span>&larr;</span>
                    Back to Trainees
                </a>

            </div>

        </div>

        <div class="form-container">

            <div class="form-card">

                <div class="form-header">
                    <h2>Trainee Information</h2>
                    <p>Complete the personal and academic information below.</p>
                </div>

                <?php if (!empty($error)): ?>

                    <div class="error-message">
                        <span class="error-icon">!</span>
                        <p><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>

                <?php endif; ?>

                <form method="POST" action="" class="form-body">

                    <section>

                        <h3 class="section-title">Personal Information</h3>

                        <p class="section-description">
                            Enter the trainee's basic personal details.
                        </p>

                        <div class="form-grid">

                            <div class="form-group">

                                <label for="Firstname">
                                    First Name <span class="required">*</span>
                                </label>

                                <input
                                    type="text"
                                    id="Firstname"
                                    name="Firstname"
                                    value="<?php echo htmlspecialchars($firstName, ENT_QUOTES, 'UTF-8'); ?>"
                                    placeholder="Enter first name"
                                    maxlength="100"
                                    class="form-control"
                                    required>

                            </div>

                            <div class="form-group">

                                <label for="lastname">
                                    Last Name <span class="required">*</span>
                                </label>

                                <input
                                    type="text"
                                    id="lastname"
                                    name="lastname"
                                    value="<?php echo htmlspecialchars($lastName, ENT_QUOTES, 'UTF-8'); ?>"
                                    placeholder="Enter last name"
                                    maxlength="100"
                                    class="form-control"
                                    required>

                            </div>

                        </div>

                        <div class="form-group" style="margin-top: 20px;">

                            <label for="gender">
                                Gender <span class="required">*</span>
                            </label>

                            <select
                                id="gender"
                                name="gender"
                                class="form-control"
                                required>

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

                    </section>

                    <div class="form-divider"></div>

                    <section class="academic-section">

                        <h3 class="section-title">Academic Information</h3>

                        <p class="section-description">
                            Assign the trainee to the appropriate academic trade.
                        </p>

                        <div class="form-group">

                            <label for="Trade_id">
                                Select Trade <span class="required">*</span>
                            </label>

                            <select
                                id="Trade_id"
                                name="Trade_id"
                                class="form-control"
                                required>

                                <option value="">Select a trade</option>

                                <?php if ($tradesQuery): ?>

                                    <?php while ($data = mysqli_fetch_assoc($tradesQuery)): ?>

                                        <option
                                            value="<?php echo htmlspecialchars($data['Trade_Id'], ENT_QUOTES, 'UTF-8'); ?>"
                                            <?php echo ($tradeId == $data['Trade_Id']) ? "selected" : ""; ?>>

                                            <?php echo htmlspecialchars($data['Trade_name'], ENT_QUOTES, 'UTF-8'); ?>

                                        </option>

                                    <?php endwhile; ?>

                                <?php endif; ?>

                            </select>

                        </div>

                    </section>

                    <div class="information-box">

                        <div class="information-icon">i</div>

                        <div>
                            <h3>Registration Information</h3>

                            <p>
                                The trainee code will be managed by your system
                                according to your database configuration.
                            </p>
                        </div>

                    </div>

                    <div class="form-actions">

                        <a href="listOfTrainee.php" class="cancel-button">
                            Cancel
                        </a>

                        <button type="submit" name="add" class="submit-button">
                            Register Trainee
                        </button>

                    </div>

                </form>

            </div>

            <p class="bottom-note">
                Ensure the trainee's personal details and academic trade are correct before registration.
            </p>

        </div>

    </main>

    <?php include("footer.php"); ?>

</body>
</html>
