
<?php

include("conn.php");

session_start();

if (!isset($_SESSION['username'])) {
    $_SESSION['login_error'] = "Please login to access this page";
    header("Location: login.php");
    exit();
}

$sql = "SELECT
            m.Trainee_Id,
            CONCAT(t.Firstname, ' ', t.lastname) AS Trainee_Name,
            m.Module_Id,
            md.Module_Name,
            m.Decision,
            m.Total_Marks,
            td.Trade_name
        FROM marks m
        JOIN trainees t ON t.Trainee_Id = m.Trainee_Id
        JOIN modules md ON m.Module_Id = md.Module_Id
        JOIN trades td ON td.Trade_id = t.Trade_id
        WHERE m.Total_Marks < 70
        ORDER BY t.Firstname ASC, md.Module_Name ASC";

$query = mysqli_query($conn, $sql);

if (!$query) {
    error_log("Not competent trainees query failed: " . mysqli_error($conn));
    die("Unable to retrieve trainee records.");
}

$totalRecords = mysqli_num_rows($query);

function escapeOutput($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Not Yet Competent Trainees | GIKONKO TSS</title>

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
            font-family: Arial, Helvetica, sans-serif;
            background: #f8fafc;
            color: #1e293b;
        }

        header {
            width: 100%;
            position: sticky;
            top: 0;
            z-index: 50;
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            box-shadow: 0 2px 5px rgba(15, 23, 42, 0.04);
        }

        main {
            flex: 1;
            width: 100%;
            max-width: 1280px;
            margin: 0 auto;
            padding: 36px 24px;
        }

        .page-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 30px;
        }

        .heading-label {
            color: #1d4ed8;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            margin-bottom: 9px;
        }

        .page-heading h1 {
            font-size: 28px;
            font-weight: 750;
            letter-spacing: -0.7px;
            color: #172554;
        }

        .page-description {
            margin-top: 9px;
            font-size: 13px;
            color: #64748b;
            line-height: 1.6;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            padding: 11px 16px;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 7px;
            color: #334155;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            white-space: nowrap;
            transition: 0.2s;
        }

        .back-btn:hover {
            background: #f1f5f9;
            border-color: #94a3b8;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
            margin-bottom: 26px;
        }

        .stat-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 22px;
            box-shadow: 0 2px 6px rgba(15, 23, 42, 0.03);
        }

        .stat-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
        }

        .stat-label {
            font-size: 13px;
            font-weight: 600;
            color: #64748b;
        }

        .stat-number {
            margin-top: 10px;
            font-size: 31px;
            font-weight: 750;
            letter-spacing: -0.8px;
            color: #172554;
        }

        .stat-number.danger {
            color: #dc2626;
        }

        .stat-icon {
            width: 46px;
            height: 46px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 9px;
            flex-shrink: 0;
        }

        .stat-icon svg {
            width: 24px;
            height: 24px;
        }

        .danger-icon {
            background: #fef2f2;
            color: #dc2626;
        }

        .blue-icon {
            background: #eff6ff;
            color: #1d4ed8;
        }

        .records-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 6px rgba(15, 23, 42, 0.03);
        }

        .records-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            padding: 22px;
            border-bottom: 1px solid #e2e8f0;
        }

        .records-header h2 {
            color: #172554;
            font-size: 17px;
            font-weight: 700;
        }

        .records-header p {
            margin-top: 7px;
            color: #64748b;
            font-size: 12px;
        }

        .download-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            padding: 11px 16px;
            background: #1d4ed8;
            color: #ffffff;
            border-radius: 7px;
            font-size: 12px;
            font-weight: 700;
            text-decoration: none;
            white-space: nowrap;
            transition: background 0.2s;
        }

        .download-btn:hover {
            background: #1e40af;
        }

        .download-btn svg {
            width: 17px;
            height: 17px;
        }

        .table-container {
            width: 100%;
            overflow-x: auto;
        }

        table {
            width: 100%;
            min-width: 900px;
            border-collapse: collapse;
            text-align: left;
        }

        thead {
            background: #f8fafc;
        }

        th {
            padding: 16px 20px;
            color: #475569;
            font-size: 12px;
            font-weight: 700;
            white-space: nowrap;
            border-bottom: 1px solid #e2e8f0;
        }

        td {
            padding: 16px 20px;
            color: #475569;
            font-size: 13px;
            border-bottom: 1px solid #f1f5f9;
            white-space: nowrap;
        }

        tbody tr {
            transition: background 0.15s;
        }

        tbody tr:hover {
            background: #f8fafc;
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        .trainee-code {
            color: #334155;
            font-weight: 650;
        }

        .trainee-name {
            color: #1e293b;
            font-weight: 600;
        }

        .marks-value {
            text-align: center;
            font-weight: 700;
            color: #1e293b;
        }

        .decision-cell {
            text-align: center;
        }

        .decision-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 6px 12px;
            border-radius: 20px;
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #b91c1c;
            font-size: 11px;
            font-weight: 700;
        }

        .empty-state {
            padding: 55px 20px;
            text-align: center;
        }

        .empty-icon {
            width: 50px;
            height: 50px;
            margin: 0 auto 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f0fdf4;
            color: #16a34a;
            border-radius: 50%;
        }

        .empty-icon svg {
            width: 25px;
            height: 25px;
        }

        .empty-state h3 {
            color: #1e293b;
            font-size: 15px;
            font-weight: 700;
        }

        .empty-state p {
            margin-top: 8px;
            color: #64748b;
            font-size: 12px;
            line-height: 1.6;
        }

        .records-footer {
            padding: 15px 22px;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
        }

        .records-footer p {
            color: #64748b;
            font-size: 12px;
        }

        @media (max-width: 768px) {
            main {
                padding: 28px 18px;
            }

            .page-heading {
                align-items: flex-start;
                flex-direction: column;
            }

            .page-heading h1 {
                font-size: 24px;
            }

            .records-header {
                align-items: flex-start;
                flex-direction: column;
            }

            .download-btn {
                width: 100%;
            }
        }

        @media (max-width: 520px) {
            main {
                padding: 23px 13px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
                gap: 13px;
            }

            .stat-card {
                padding: 18px;
            }

            .records-header {
                padding: 18px;
            }

            .stat-number {
                font-size: 27px;
            }

            .back-btn {
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

            <div>
                <p class="heading-label">
                    Academic Performance
                </p>

                <h1>Not Yet Competent Trainees</h1>

                <p class="page-description">
                    Assessment records for trainees who scored below 70 marks.
                </p>
            </div>

            <a href="greetingUser.php" class="back-btn">
                <span>&larr;</span>
                Back to Dashboard
            </a>

        </div>

        <section class="stats-grid">

            <div class="stat-card">

                <div class="stat-content">

                    <div>
                        <p class="stat-label">
                            Not Competent Records
                        </p>

                        <p class="stat-number danger">
                            <?php echo $totalRecords; ?>
                        </p>
                    </div>

                    <div class="stat-icon danger-icon">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.8"
                                d="M12 9v4m0 4h.01M10.3 3.9L1.8 18a2 2 0 001.7 3h17a2 2 0 001.7-3L13.7 3.9a2 2 0 00-3.4 0z"
                            />
                        </svg>
                    </div>

                </div>

            </div>

            <div class="stat-card">

                <div class="stat-content">

                    <div>
                        <p class="stat-label">
                            Required Competency Mark
                        </p>

                        <p class="stat-number">
                            70%
                        </p>
                    </div>

                    <div class="stat-icon blue-icon">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.8"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                        </svg>
                    </div>

                </div>

            </div>

        </section>

        <section class="records-card">

            <div class="records-header">

                <div>
                    <h2>Trainee Assessment Records</h2>

                    <p>
                        Review and download the assessment results.
                    </p>
                </div>

                <a href="download_not_competent.php" class="download-btn">

                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"
                        />
                    </svg>

                    Download CSV

                </a>

            </div>

            <div class="table-container">

                <table>

                    <thead>
                        <tr>
                            <th>Trainee Code</th>
                            <th>Trainee Name</th>
                            <th>Module Code</th>
                            <th>Module Name</th>
                            <th>Trade Name</th>
                            <th style="text-align: center;">Total Marks</th>
                            <th style="text-align: center;">Decision</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php if ($totalRecords > 0): ?>

                            <?php while ($data = mysqli_fetch_assoc($query)): ?>

                                <tr>

                                    <td class="trainee-code">
                                        <?php echo escapeOutput($data['Trainee_Id']); ?>
                                    </td>

                                    <td class="trainee-name">
                                        <?php echo escapeOutput($data['Trainee_Name']); ?>
                                    </td>

                                    <td>
                                        <?php echo escapeOutput($data['Module_Id']); ?>
                                    </td>

                                    <td>
                                        <?php echo escapeOutput($data['Module_Name']); ?>
                                    </td>

                                    <td>
                                        <?php echo escapeOutput($data['Trade_name']); ?>
                                    </td>

                                    <td class="marks-value">
                                        <?php echo escapeOutput($data['Total_Marks']); ?>/100
                                    </td>

                                    <td class="decision-cell">
                                        <span class="decision-badge">
                                            <?php echo escapeOutput($data['Decision']); ?>
                                        </span>
                                    </td>

                                </tr>

                            <?php endwhile; ?>

                        <?php else: ?>

                            <tr>
                                <td colspan="7">

                                    <div class="empty-state">

                                        <div class="empty-icon">
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M5 13l4 4L19 7"
                                                />
                                            </svg>
                                        </div>

                                        <h3>No records found</h3>

                                        <p>
                                            There are currently no trainees below the competency threshold.
                                        </p>

                                    </div>

                                </td>
                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

            <div class="records-footer">
                <p>
                    Showing <?php echo $totalRecords; ?> assessment record(s).
                </p>
            </div>

        </section>

    </main>

    <?php include("footer.php"); ?>

</body>
</html>
