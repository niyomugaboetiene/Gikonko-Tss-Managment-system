
<?php
include("conn.php");
session_start();

if (!isset($_SESSION['username'])) {
    $_SESSION['login_error'] = "Please login to access this page";
    header("Location: index.php");
    exit();
}

$error = "";

if (!isset($_GET['Trainee_Id']) || !ctype_digit((string)$_GET['Trainee_Id'])) {
    header("Location: listOfTrainee.php");
    exit();
}

$Trainee_Id = (int)$_GET['Trainee_Id'];

$stmt = mysqli_prepare(
    $conn,
    "SELECT
        t.Trainee_Id,
        t.Firstname,
        t.lastname,
        t.Gender,
        t.Trade_Id,
        td.Trade_name
    FROM trainees t
    JOIN trades td ON t.Trade_Id = td.Trade_id
    WHERE t.Trainee_Id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $Trainee_Id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    mysqli_stmt_close($stmt);
    header("Location: listOfTrainee.php");
    exit();
}

$trainee = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (isset($_POST['update'])) {
    $FirstName = trim($_POST['Firstname'] ?? '');
    $lastname = trim($_POST['lastname'] ?? '');
    $Gender = trim($_POST['Gender'] ?? '');
    $Trade_Id = trim($_POST['Trade_Id'] ?? '');

    if (
        $FirstName === '' ||
        $lastname === '' ||
        $Gender === '' ||
        $Trade_Id === ''
    ) {
        $error = "Please fill in all required fields.";
    } elseif (!in_array($Gender, ['male', 'female'], true)) {
        $error = "Please select a valid gender.";
    } elseif (!ctype_digit($Trade_Id)) {
        $error = "Please select a valid trade.";
    } else {
        $checkTrade = mysqli_prepare(
            $conn,
            "SELECT Trade_id FROM trades WHERE Trade_id = ?"
        );

        mysqli_stmt_bind_param($checkTrade, "i", $Trade_Id);
        mysqli_stmt_execute($checkTrade);

        $tradeResult = mysqli_stmt_get_result($checkTrade);
        $tradeExists = mysqli_num_rows($tradeResult) > 0;

        mysqli_stmt_close($checkTrade);

        if (!$tradeExists) {
            $error = "The selected trade does not exist.";
        } else {
            $update = mysqli_prepare(
                $conn,
                "UPDATE trainees SET
                    Firstname = ?,
                    lastname = ?,
                    Gender = ?,
                    Trade_Id = ?
                WHERE Trainee_Id = ?"
            );

            mysqli_stmt_bind_param(
                $update,
                "sssii",
                $FirstName,
                $lastname,
                $Gender,
                $Trade_Id,
                $Trainee_Id
            );

            if (mysqli_stmt_execute($update)) {
                mysqli_stmt_close($update);
                header("Location: listOfTrainee.php");
                exit();
            } else {
                $error = "Unable to update trainee information. Please try again.";
            }

            mysqli_stmt_close($update);
        }
    }

    $trainee['Firstname'] = $FirstName;
    $trainee['lastname'] = $lastname;
    $trainee['Gender'] = $Gender;
    $trainee['Trade_Id'] = $Trade_Id;
}

function escapeTrainee($value) {
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
    <title>Update Trainee | GIKONKO TSS</title>
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

        header {
            position: sticky;
            top: 0;
            z-index: 50;
            width: 100%;
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            box-shadow: 0 2px 5px rgba(15, 23, 42, 0.04);
        }

        main {
            flex: 1;
            width: 100%;
            max-width: 1100px;
            margin: 0 auto;
            padding: 42px 24px 55px;
        }

        .page-heading {
            margin-bottom: 30px;
        }

        .eyebrow {
            color: #1d4ed8;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 9px;
        }

        .page-heading h1 {
            color: #0f172a;
            font-size: 30px;
            font-weight: 700;
            letter-spacing: -0.7px;
        }

        .page-description {
            color: #64748b;
            font-size: 14px;
            margin-top: 9px;
            line-height: 1.6;
        }

        .form-card {
            max-width: 800px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(15, 23, 42, 0.035);
        }

        .card-header {
            padding: 23px 28px;
            border-bottom: 1px solid #e2e8f0;
            background: #ffffff;
        }

        .card-header h2 {
            color: #1e293b;
            font-size: 18px;
            font-weight: 700;
        }

        .record-id {
            color: #64748b;
            font-size: 13px;
            margin-top: 7px;
        }

        .form-body {
            padding: 28px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 22px;
        }

        .form-group {
            margin-bottom: 23px;
        }

        .form-group label {
            display: block;
            color: #334155;
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 9px;
        }

        .form-control {
            display: block;
            width: 100%;
            height: 48px;
            padding: 0 14px;
            border: 1px solid #cbd5e1;
            border-radius: 7px;
            background: #ffffff;
            color: #1e293b;
            font-size: 14px;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-control::placeholder {
            color: #94a3b8;
        }

        .form-control:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        }

        .form-control[readonly] {
            background: #f1f5f9;
            border-color: #e2e8f0;
            color: #64748b;
            cursor: not-allowed;
        }

        select.form-control {
            cursor: pointer;
        }

        .error-message {
            padding: 14px 16px;
            margin-bottom: 23px;
            color: #b91c1c;
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 7px;
            font-size: 13px;
            line-height: 1.5;
        }

        .info-note {
            padding: 16px;
            margin-top: 5px;
            margin-bottom: 25px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
        }

        .info-note p {
            color: #64748b;
            font-size: 13px;
            line-height: 1.7;
        }

        .info-note strong {
            color: #334155;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            padding-top: 5px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 44px;
            padding: 0 21px;
            border-radius: 7px;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            transition: background 0.2s, border-color 0.2s;
        }

        .btn-cancel {
            color: #475569;
            background: #ffffff;
            border: 1px solid #cbd5e1;
        }

        .btn-cancel:hover {
            background: #f1f5f9;
        }

        .btn-save {
            color: #ffffff;
            background: #1d4ed8;
            border: 1px solid #1d4ed8;
        }

        .btn-save:hover {
            background: #1e40af;
            border-color: #1e40af;
        }

        @media (max-width: 640px) {
            main {
                padding: 30px 16px 40px;
            }

            .page-heading h1 {
                font-size: 25px;
            }

            .card-header {
                padding: 20px;
            }

            .form-body {
                padding: 20px;
            }

            .form-grid {
                grid-template-columns: 1fr;
                gap: 0;
            }

            .form-actions {
                flex-direction: column-reverse;
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

    <main>

        <div class="page-heading">
            <p class="eyebrow">Student Management</p>

            <h1>Update Trainee</h1>

            <p class="page-description">
                Edit trainee personal information and assigned trade.
            </p>
        </div>

        <section class="form-card">

            <div class="card-header">
                <h2>Trainee Information</h2>

                <p class="record-id">
                    Trainee record #<?php echo escapeTrainee($trainee['Trainee_Id']); ?>
                </p>
            </div>

            <form method="POST" class="form-body">

                <?php if ($error !== ''): ?>
                    <div class="error-message">
                        <?php echo escapeTrainee($error); ?>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="Trainee_Id">Trainee Code</label>

                    <input
                        type="text"
                        id="Trainee_Id"
                        value="<?php echo escapeTrainee($trainee['Trainee_Id']); ?>"
                        readonly
                        class="form-control"
                    >
                </div>

                <div class="form-grid">

                    <div class="form-group">
                        <label for="Firstname">First Name</label>

                        <input
                            type="text"
                            id="Firstname"
                            name="Firstname"
                            value="<?php echo escapeTrainee($trainee['Firstname']); ?>"
                            placeholder="Enter first name"
                            maxlength="100"
                            required
                            class="form-control"
                        >
                    </div>

                    <div class="form-group">
                        <label for="lastname">Last Name</label>

                        <input
                            type="text"
                            id="lastname"
                            name="lastname"
                            value="<?php echo escapeTrainee($trainee['lastname']); ?>"
                            placeholder="Enter last name"
                            maxlength="100"
                            required
                            class="form-control"
                        >
                    </div>

                </div>

                <div class="form-group">
                    <label for="Gender">Gender</label>

                    <select
                        id="Gender"
                        name="Gender"
                        required
                        class="form-control"
                    >
                        <option value="">Select gender</option>

                        <option
                            value="male"
                            <?php echo strtolower($trainee['Gender']) === 'male' ? 'selected' : ''; ?>
                        >
                            Male
                        </option>

                        <option
                            value="female"
                            <?php echo strtolower($trainee['Gender']) === 'female' ? 'selected' : ''; ?>
                        >
                            Female
                        </option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="Trade_Id">Assigned Trade</label>

                    <select
                        id="Trade_Id"
                        name="Trade_Id"
                        required
                        class="form-control"
                    >
                        <option value="">Select a trade</option>

                        <?php while ($trade = mysqli_fetch_assoc($trades)): ?>
                            <option
                                value="<?php echo escapeTrainee($trade['Trade_id']); ?>"
                                <?php echo (string)$trainee['Trade_Id'] === (string)$trade['Trade_id'] ? 'selected' : ''; ?>
                            >
                                <?php echo escapeTrainee($trade['Trade_name']); ?>
                            </option>
                        <?php endwhile; ?>

                    </select>
                </div>

                <div class="info-note">
                    <p>
                        <strong>Note:</strong>
                        Changing the assigned trade will update the trade
                        associated with this trainee.
                    </p>
                </div>

                <div class="form-actions">

                    <a href="listOfTrainee.php" class="btn btn-cancel">
                        Cancel
                    </a>

                    <button
                        type="submit"
                        name="update"
                        class="btn btn-save"
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
