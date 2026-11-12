<?php
include("conn.php");

session_start();

if (!isset($_SESSION['username'])) {
    $_SESSION['login_error'] = "Please login to access this page";
    header("Location: index.php");
    exit();
}

$error = "";

if (!isset($_GET['Module_Id']) || !ctype_digit((string)$_GET['Module_Id'])) {
    header("Location: listOfModule.php");
    exit();
}

$Module_Id = (int)$_GET['Module_Id'];

$stmt = mysqli_prepare(
    $conn,
    "SELECT
        m.Module_Id,
        m.Module_Name,
        m.Trade_Id,
        t.Trade_name
    FROM modules m
    JOIN trades t ON t.Trade_id = m.Trade_Id
    WHERE m.Module_Id = ?"
);

if (!$stmt) {
    error_log("Module query preparation failed: " . mysqli_error($conn));
    die("Unable to load module information.");
}

mysqli_stmt_bind_param($stmt, "i", $Module_Id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    mysqli_stmt_close($stmt);
    header("Location: listOfModule.php");
    exit();
}

$Module = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (isset($_POST['save'])) {
    $Module_Name = trim($_POST['Module_Name'] ?? '');
    $Trade_Id = trim($_POST['Trade_Id'] ?? '');

    if ($Module_Name === '' || $Trade_Id === '') {
        $error = "Please fill in all required fields.";
    } elseif (!ctype_digit($Trade_Id) || (int)$Trade_Id < 1) {
        $error = "Please select a valid trade.";
    } else {
        $Trade_Id = (int)$Trade_Id;

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
            $checkDuplicate = mysqli_prepare(
                $conn,
                "SELECT Module_Id FROM modules
                 WHERE Module_Name = ? AND Module_Id != ?"
            );

            mysqli_stmt_bind_param(
                $checkDuplicate,
                "si",
                $Module_Name,
                $Module_Id
            );

            mysqli_stmt_execute($checkDuplicate);

            $duplicateResult = mysqli_stmt_get_result($checkDuplicate);
            $duplicateExists = mysqli_num_rows($duplicateResult) > 0;

            mysqli_stmt_close($checkDuplicate);

            if ($duplicateExists) {
                $error = "A module with this name already exists.";
            } else {
                $update = mysqli_prepare(
                    $conn,
                    "UPDATE modules
                     SET Module_Name = ?, Trade_Id = ?
                     WHERE Module_Id = ?"
                );

                mysqli_stmt_bind_param(
                    $update,
                    "sii",
                    $Module_Name,
                    $Trade_Id,
                    $Module_Id
                );

                if (mysqli_stmt_execute($update)) {
                    mysqli_stmt_close($update);
                    header("Location: listOfModule.php");
                    exit();
                } else {
                    error_log("Module update failed: " . mysqli_stmt_error($update));
                    $error = "Unable to update the module. Please try again.";
                }

                mysqli_stmt_close($update);
            }
        }
    }

    $Module['Module_Name'] = $Module_Name;
    $Module['Trade_Id'] = $Trade_Id;
}

function escapeModule($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$trades = mysqli_query(
    $conn,
    "SELECT Trade_id, Trade_name
     FROM trades
     ORDER BY Trade_name ASC"
);

if (!$trades) {
    error_log("Trade loading failed: " . mysqli_error($conn));
    die("Unable to load trade information.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Update Module | GIKONKO TSS</title>

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

        .module-card {
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
            margin-bottom: 24px;
        }

        label {
            display: block;
            margin-bottom: 9px;
            color: #334155;
            font-size: 13px;
            font-weight: 650;
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

        input[readonly] {
            background: #f1f5f9;
            border-color: #e2e8f0;
            color: #64748b;
            cursor: not-allowed;
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

        .note-box {
            display: flex;
            gap: 10px;
            align-items: flex-start;
            margin-top: 4px;
            margin-bottom: 27px;
            padding: 16px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: #f8fafc;
        }

        .note-icon {
            flex-shrink: 0;
            width: 20px;
            height: 20px;
            color: #64748b;
        }

        .note-box p {
            color: #64748b;
            font-size: 13px;
            line-height: 1.6;
        }

        .note-box strong {
            color: #334155;
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
        }

        @media (max-width: 420px) {
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

            <h1>Update Module</h1>

            <p>
                Modify module information and its assigned trade.
            </p>
        </div>

        <section class="module-card">

            <div class="card-header">
                <h2>Module Information</h2>

                <p>
                    Edit the module details and review its assigned trade.
                </p>

                <span class="record-id">
                    Record #<?php echo escapeModule($Module['Module_Id']); ?>
                </span>
            </div>

            <form method="POST" class="form-content">

                <?php if ($error !== ''): ?>
                    <div class="error-message">
                        <?php echo escapeModule($error); ?>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="Module_Id">Module Code</label>

                    <input
                        type="text"
                        id="Module_Id"
                        value="<?php echo escapeModule($Module['Module_Id']); ?>"
                        readonly
                    >
                </div>

                <div class="form-group">
                    <label for="Module_Name">Module Name</label>

                    <input
                        type="text"
                        id="Module_Name"
                        name="Module_Name"
                        value="<?php echo escapeModule($Module['Module_Name']); ?>"
                        placeholder="Enter module name"
                        maxlength="255"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="Trade_Id">Assigned Trade</label>

                    <select
                        id="Trade_Id"
                        name="Trade_Id"
                        required
                    >
                        <option value="">Select a trade</option>

                        <?php while ($trade = mysqli_fetch_assoc($trades)): ?>
                            <option
                                value="<?php echo escapeModule($trade['Trade_id']); ?>"
                                <?php echo (string)$Module['Trade_Id'] === (string)$trade['Trade_id'] ? 'selected' : ''; ?>
                            >
                                <?php echo escapeModule($trade['Trade_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="note-box">
                    <svg
                        class="note-icon"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="1.8"
                            d="M12 8v4m0 4h.01M10.3 3.86L1.82 18a2 2 0 001.7 3h16.96a2 2 0 001.7-3L13.7 3.86a2 2 0 00-3.4 0z"
                        />
                    </svg>

                    <p>
                        <strong>Note:</strong>
                        Changing the assigned trade will update the trade associated with this module.
                    </p>
                </div>

                <div class="form-actions">

                    <a href="listOfModule.php" class="btn btn-cancel">
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
