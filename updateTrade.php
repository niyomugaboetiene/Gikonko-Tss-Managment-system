
<?php
include("conn.php");
session_start();

if (!isset($_SESSION['username'])) {
    $_SESSION['login_error'] = "Please login to access this page";
    header("Location: index.php");
    exit();
}

$error = "";

if (!isset($_GET['Trade_id']) || !ctype_digit((string)$_GET['Trade_id'])) {
    header("Location: listOfTrades.php");
    exit();
}

$Trade_Id = (int)$_GET['Trade_id'];

$stmt = mysqli_prepare(
    $conn,
    "SELECT Trade_id, Trade_name FROM trades WHERE Trade_id = ?"
);

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
            max-width: 760px;
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
            <p class="eyebrow">Academic Management</p>

            <h1>Update Trade</h1>

            <p class="page-description">
                Modify the trade information in the system.
            </p>
        </div>

        <section class="form-card">

            <div class="card-header">
                <h2>Trade Information</h2>

                <p class="record-id">
                    Trade record #<?php echo escapeTrade($Data['Trade_id']); ?>
                </p>
            </div>

            <form method="POST" class="form-body">

                <?php if ($error !== ''): ?>
                    <div class="error-message">
                        <?php echo escapeTrade($error); ?>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="Trade_id">Trade Code</label>

                    <input
                        type="text"
                        id="Trade_id"
                        value="<?php echo escapeTrade($Data['Trade_id']); ?>"
                        readonly
                        class="form-control"
                    >
                </div>

                <div class="form-group">
                    <label for="Trade_name">Trade Name</label>

                    <input
                        type="text"
                        id="Trade_name"
                        name="Trade_name"
                        value="<?php echo escapeTrade($Data['Trade_name']); ?>"
                        placeholder="Enter trade name"
                        maxlength="255"
                        required
                        class="form-control"
                    >
                </div>

                <div class="info-note">
                    <p>
                        <strong>Note:</strong>
                        Updating a trade name will also change how that trade
                        is displayed wherever it is referenced in the system.
                    </p>
                </div>

                <div class="form-actions">

                    <a href="listOfTrades.php" class="btn btn-cancel">
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

</body>
</html>
