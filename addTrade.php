
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
            color: #1e293b;
            font-family: Arial, Helvetica, sans-serif;
        }

        .page-container {
            width: 100%;
            max-width: 1280px;
            margin: 0 auto;
            padding: 36px 24px;
        }

        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 22px;
            color: #64748b;
            font-size: 13px;
        }

        .breadcrumb a {
            color: #64748b;
            text-decoration: none;
            transition: color 0.2s;
        }

        .breadcrumb a:hover {
            color: #1d4ed8;
        }

        .breadcrumb .current {
            color: #1e293b;
            font-weight: 600;
        }

        .page-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 32px;
        }

        .page-heading h1 {
            color: #0f172a;
            font-size: 30px;
            font-weight: 700;
            letter-spacing: -0.6px;
        }

        .page-heading p {
            margin-top: 9px;
            color: #64748b;
            font-size: 14px;
            line-height: 1.6;
        }

        .back-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            padding: 11px 17px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            background: #ffffff;
            color: #334155;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            white-space: nowrap;
            transition: background 0.2s, border-color 0.2s;
        }

        .back-button:hover {
            background: #f1f5f9;
            border-color: #94a3b8;
        }

        .form-wrapper {
            max-width: 760px;
        }

        .form-card {
            overflow: hidden;
            border: 1px solid #e2e8f0;
            border-radius: 9px;
            background: #ffffff;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
        }

        .form-header {
            padding: 23px 26px;
            border-bottom: 1px solid #e2e8f0;
        }

        .form-header h2 {
            color: #0f172a;
            font-size: 18px;
            font-weight: 700;
        }

        .form-header p {
            margin-top: 7px;
            color: #64748b;
            font-size: 13px;
            line-height: 1.5;
        }

        .error-message {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin: 20px 26px 0;
            padding: 13px 15px;
            border: 1px solid #fecaca;
            border-radius: 6px;
            background: #fef2f2;
            color: #b91c1c;
            font-size: 13px;
            line-height: 1.5;
        }

        .error-icon {
            font-weight: 700;
        }

        .trade-form {
            padding: 26px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
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
            display: block;
            width: 100%;
            padding: 12px 13px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            background: #ffffff;
            color: #334155;
            font-size: 14px;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-control:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.10);
        }

        .form-control::placeholder {
            color: #94a3b8;
        }

        .field-description {
            margin-top: 8px;
            color: #64748b;
            font-size: 12px;
            line-height: 1.5;
        }

        .info-box {
            display: flex;
            align-items: flex-start;
            gap: 13px;
            margin-bottom: 25px;
            padding: 17px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            background: #f8fafc;
        }

        .info-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 22px;
            height: 22px;
            flex-shrink: 0;
            border-radius: 50%;
            background: #dbeafe;
            color: #1d4ed8;
            font-size: 13px;
            font-weight: 700;
        }

        .info-box h3 {
            margin-bottom: 6px;
            color: #334155;
            font-size: 13px;
            font-weight: 700;
        }

        .info-box p {
            color: #64748b;
            font-size: 12px;
            line-height: 1.6;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            padding-top: 5px;
        }

        .cancel-button,
        .submit-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 115px;
            padding: 12px 20px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: background 0.2s, border-color 0.2s;
        }

        .cancel-button {
            border: 1px solid #cbd5e1;
            background: #ffffff;
            color: #334155;
        }

        .cancel-button:hover {
            background: #f1f5f9;
        }

        .submit-button {
            border: 1px solid #1d4ed8;
            background: #1d4ed8;
            color: #ffffff;
        }

        .submit-button:hover {
            border-color: #1e40af;
            background: #1e40af;
        }

        .bottom-note {
            margin-top: 19px;
            color: #64748b;
            font-size: 13px;
            line-height: 1.6;
        }

        @media (max-width: 640px) {
            .page-container {
                padding: 25px 16px;
            }

            .page-heading {
                align-items: flex-start;
                flex-direction: column;
            }

            .page-heading h1 {
                font-size: 25px;
            }

            .back-button {
                width: 100%;
            }

            .form-header,
            .trade-form {
                padding: 20px;
            }

            .error-message {
                margin-right: 20px;
                margin-left: 20px;
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
            <a href="listOfTrades.php">Trade Management</a>
            <span>/</span>
            <span class="current">Add Trade</span>
        </div>

        <div class="page-heading">
            <div>
                <h1>Add New Trade</h1>
                <p>Create a new academic trade in your institution.</p>
            </div>

            <a href="listOfTrades.php" class="back-button">
                <span>&larr;</span>
                Back to Trades
            </a>
        </div>

        <div class="form-wrapper">

            <div class="form-card">

                <div class="form-header">
                    <h2>Trade Information</h2>
                    <p>Enter the official name of the new trade below.</p>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="error-message">
                        <span class="error-icon">!</span>
                        <p>
                            <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                        </p>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" class="trade-form">

                    <div class="form-group">

                        <label for="Trade_name">
                            Trade Name <span class="required">*</span>
                        </label>

                        <input
                            type="text"
                            id="Trade_name"
                            name="Trade_name"
                            value="<?php echo htmlspecialchars($tradeName, ENT_QUOTES, 'UTF-8'); ?>"
                            placeholder="Enter trade name"
                            maxlength="150"
                            required
                            class="form-control">

                        <p class="field-description">
                            Example: Software Development, Networking or Electrical Technology.
                        </p>

                    </div>

                    <div class="info-box">

                        <span class="info-icon">i</span>

                        <div>
                            <h3>Important Information</h3>

                            <p>
                                A trade represents an academic specialization.
                                Modules can be assigned to a trade after it has
                                been created.
                            </p>
                        </div>

                    </div>

                    <div class="form-actions">

                        <a href="listOfTrades.php" class="cancel-button">
                            Cancel
                        </a>

                        <button
                            type="submit"
                            name="add"
                            class="submit-button">
                            Add Trade
                        </button>

                    </div>

                </form>

            </div>

            <p class="bottom-note">
                Organize your institution's academic programs by creating
                and managing trades.
            </p>

        </div>

    </main>

    <?php include("footer.php"); ?>

</body>
</html>
