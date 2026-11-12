<?php
session_start();
include("conn.php");

if (!isset($_SESSION['username'])) {
    $_SESSION['login_error'] = "Please login to access this page";
    header("Location: index.php");
    exit();
}

$error = "";
$moduleName = "";
$tradeId = "";

if (isset($_POST['addModule'])) {
    $moduleName = trim($_POST['Module_Name'] ?? "");
    $tradeId = trim($_POST['Trade_Id'] ?? "");

    if ($moduleName === "" || $tradeId === "") {
        $error = "Please complete all fields.";
    } else {
        $checkModule = mysqli_prepare(
            $conn,
            "SELECT Module_Id FROM modules WHERE Module_Name = ?"
        );

        mysqli_stmt_bind_param($checkModule, "s", $moduleName);
        mysqli_stmt_execute($checkModule);
        mysqli_stmt_store_result($checkModule);

        if (mysqli_stmt_num_rows($checkModule) > 0) {
            $error = "This module already exists.";
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
                $insertModule = mysqli_prepare(
                    $conn,
                    "INSERT INTO modules (Module_Name, Trade_Id) VALUES (?, ?)"
                );

                mysqli_stmt_bind_param(
                    $insertModule,
                    "ss",
                    $moduleName,
                    $tradeId
                );

                if (mysqli_stmt_execute($insertModule)) {
                    header("Location: listOfModule.php");
                    exit();
                } else {
                    $error = "Unable to add module. Please try again.";
                }

                mysqli_stmt_close($insertModule);
            }

            mysqli_stmt_close($checkTrade);
        }

        mysqli_stmt_close($checkModule);
    }
}

$tradesQuery = mysqli_query(
    $conn,
    "SELECT Trade_Id, Trade_Name FROM trades ORDER BY Trade_Name ASC"
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Add Module | GIKONKO TSS</title>
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
            font-size: 30px;
            font-weight: 700;
            letter-spacing: -0.6px;
            color: #0f172a;
        }

        .page-heading p {
            margin-top: 9px;
            font-size: 14px;
            color: #64748b;
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
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
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
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 9px;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
        }

        .form-header {
            padding: 23px 26px;
            border-bottom: 1px solid #e2e8f0;
        }

        .form-header h2 {
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
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
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 6px;
            color: #b91c1c;
            font-size: 13px;
            line-height: 1.5;
        }

        .error-icon {
            font-weight: 700;
        }

        .module-form {
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

        .form-divider {
            height: 1px;
            margin: 25px 0;
            background: #f1f5f9;
        }

        .info-box {
            display: flex;
            align-items: flex-start;
            gap: 13px;
            padding: 17px;
            margin-bottom: 25px;
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
            background: #1e40af;
            border-color: #1e40af;
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
            .module-form {
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

    <main class="page-container">

        <div class="breadcrumb">
            <a href="listOfModule.php">Module Management</a>
            <span>/</span>
            <span class="current">Add Module</span>
        </div>

        <div class="page-heading">
            <div>
                <h1>Add New Module</h1>
                <p>Create and assign a new learning module to a trade.</p>
            </div>

            <a href="listOfModule.php" class="back-button">
                <span>&larr;</span>
                Back to Modules
            </a>
        </div>

        <div class="form-wrapper">

            <div class="form-card">

                <div class="form-header">
                    <h2>Module Information</h2>
                    <p>Provide the module name and select its corresponding trade.</p>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="error-message">
                        <span class="error-icon">!</span>
                        <p><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" class="module-form">

                    <div class="form-group">
                        <label for="Module_Name">
                            Module Name <span class="required">*</span>
                        </label>

                        <input
                            type="text"
                            id="Module_Name"
                            name="Module_Name"
                            value="<?php echo htmlspecialchars($moduleName, ENT_QUOTES, 'UTF-8'); ?>"
                            placeholder="Enter module name"
                            required
                            maxlength="150"
                            class="form-control">

                        <p class="field-description">
                            Enter the official name of the learning module.
                        </p>
                    </div>

                    <div class="form-divider"></div>

                    <div class="form-group">
                        <label for="Trade_Id">
                            Select Trade <span class="required">*</span>
                        </label>

                        <select
                            id="Trade_Id"
                            name="Trade_Id"
                            required
                            class="form-control">

                            <option value="">Select a trade</option>

                            <?php while ($data = mysqli_fetch_assoc($tradesQuery)): ?>
                                <option
                                    value="<?php echo htmlspecialchars($data['Trade_Id'], ENT_QUOTES, 'UTF-8'); ?>"
                                    <?php echo ($tradeId == $data['Trade_Id']) ? 'selected' : ''; ?>>

                                    <?php echo htmlspecialchars($data['Trade_Name'], ENT_QUOTES, 'UTF-8'); ?>

                                </option>
                            <?php endwhile; ?>

                        </select>

                        <p class="field-description">
                            Choose the trade where this module belongs.
                        </p>
                    </div>

                    <div class="info-box">
                        <span class="info-icon">i</span>

                        <div>
                            <h3>Before saving</h3>
                            <p>
                                Make sure the module name is correct and
                                the appropriate trade has been selected.
                            </p>
                        </div>
                    </div>

                    <div class="form-actions">
                        <a href="listOfModule.php" class="cancel-button">
                            Cancel
                        </a>

                        <button
                            type="submit"
                            name="addModule"
                            class="submit-button">
                            Save Module
                        </button>
                    </div>

                </form>

            </div>

            <p class="bottom-note">
                Modules are organized under their respective trades for
                easier academic management.
            </p>

        </div>

    </main>

    <?php include("footer.php"); ?>

</body>
</html>
