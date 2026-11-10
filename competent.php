
<?php
include("conn.php");
session_start();

if (!isset($_SESSION['username'])) {
    $_SESSION['login_error'] = "Please login to access this page";
    header("Location:index.php");
    exit();
}

$sql = "SELECT
            m.Trainee_Id,
            CONCAT(t.Firstname, ' ', t.lastname) AS Trainee_Name,
            m.Module_Id,
            md.Module_Name,
            m.decision,
            m.Total_Marks,
            td.Trade_name
        FROM marks m
        JOIN trainees t ON t.Trainee_Id = m.Trainee_Id
        JOIN modules md ON m.Module_Id = md.Module_Id
        JOIN trades td ON td.Trade_id = t.Trade_id
        WHERE m.Total_Marks >= 70
        ORDER BY t.Firstname ASC";

$query = mysqli_query($conn, $sql);

if (!$query) {
    die("Unable to retrieve competent trainees: " . htmlspecialchars(mysqli_error($conn), ENT_QUOTES, 'UTF-8'));
}

$totalCompetent = mysqli_num_rows($query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Competent Trainees | GIKONKO TSS</title>
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

        a {
            text-decoration: none;
        }

        .main-container {
            flex: 1;
            width: 100%;
            max-width: 1280px;
            margin: 0 auto;
            padding: 105px 24px 45px;
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
            letter-spacing: 0.8px;
            margin-bottom: 8px;
        }

        .page-heading h1 {
            color: #0f172a;
            font-size: 29px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .page-heading p {
            color: #64748b;
            font-size: 14px;
            margin-top: 8px;
        }

        .heading-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn {
            min-height: 41px;
            padding: 10px 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            transition: background 0.2s, border-color 0.2s;
            white-space: nowrap;
        }

        .btn-back {
            color: #334155;
            background: #ffffff;
            border: 1px solid #cbd5e1;
        }

        .btn-back:hover {
            background: #f1f5f9;
        }

        .btn-download {
            color: #ffffff;
            background: #1d4ed8;
            border: 1px solid #1d4ed8;
        }

        .btn-download:hover {
            background: #1e40af;
            border-color: #1e40af;
        }

        .btn svg {
            width: 17px;
            height: 17px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 25px;
        }

        .stat-card {
            padding: 21px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
        }

        .stat-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .stat-label {
            color: #64748b;
            font-size: 13px;
            font-weight: 600;
        }

        .stat-number {
            color: #0f172a;
            font-size: 30px;
            font-weight: 700;
            margin-top: 8px;
        }

        .stat-description {
            color: #94a3b8;
            font-size: 12px;
            margin-top: 12px;
        }

        .stat-icon {
            width: 44px;
            height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #15803d;
            background: #f0fdf4;
            border-radius: 8px;
        }

        .stat-icon svg {
            width: 23px;
            height: 23px;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 5px 12px;
            border-radius: 20px;
            background: #f0fdf4;
            color: #15803d;
            font-size: 12px;
            font-weight: 700;
            margin-top: 12px;
        }

        .results-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(15, 23, 42, 0.04);
            overflow: hidden;
        }

        .results-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            padding: 20px 22px;
            border-bottom: 1px solid #e2e8f0;
        }

        .results-header h2 {
            color: #0f172a;
            font-size: 17px;
            font-weight: 700;
        }

        .results-header p {
            color: #64748b;
            font-size: 13px;
            margin-top: 5px;
        }

        .record-count {
            color: #64748b;
            font-size: 13px;
            white-space: nowrap;
        }

        .record-count strong {
            color: #0f172a;
        }

        .table-wrapper {
            width: 100%;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        thead {
            background: #f8fafc;
        }

        th {
            padding: 15px 20px;
            color: #64748b;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            white-space: nowrap;
            border-bottom: 1px solid #e2e8f0;
        }

        td {
            padding: 16px 20px;
            color: #475569;
            font-size: 13px;
            border-bottom: 1px solid #f1f5f9;
        }

        tbody tr {
            transition: background 0.2s;
        }

        tbody tr:hover {
            background: #f8fafc;
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        .trainee-code {
            color: #334155;
            font-weight: 700;
        }

        .trainee-name {
            color: #0f172a;
            font-weight: 600;
        }

        .decision-badge {
            display: inline-flex;
            align-items: center;
            padding: 5px 11px;
            border-radius: 20px;
            color: #15803d;
            background: #f0fdf4;
            font-size: 11px;
            font-weight: 700;
            white-space: nowrap;
        }

        .empty-state {
            padding: 55px 20px;
            text-align: center;
        }

        .empty-icon {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 14px;
            color: #94a3b8;
            background: #f1f5f9;
            border-radius: 50%;
        }

        .empty-icon svg {
            width: 25px;
            height: 25px;
        }

        .empty-state h3 {
            color: #334155;
            font-size: 14px;
            font-weight: 700;
        }

        .empty-state p {
            color: #94a3b8;
            font-size: 12px;
            margin-top: 6px;
        }

        .results-footer {
            padding: 15px 22px;
            background: #ffffff;
            border-top: 1px solid #e2e8f0;
        }

        .results-footer p {
            color: #94a3b8;
            font-size: 11px;
        }

        @media (max-width: 850px) {
            .page-heading {
                align-items: flex-start;
                flex-direction: column;
            }

            .stats-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 600px) {
            .main-container {
                padding: 95px 15px 35px;
            }

            .page-heading h1 {
                font-size: 24px;
            }

            .heading-actions {
                width: 100%;
                flex-wrap: wrap;
            }

            .heading-actions .btn {
                flex: 1;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .results-header {
                align-items: flex-start;
                flex-direction: column;
            }

            .table-wrapper table {
                min-width: 850px;
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

            <div>
                <p class="heading-label">ACADEMIC RECORDS</p>

                <h1>Competent Trainees</h1>

                <p>
                    View trainees who have achieved the required assessment marks.
                </p>
            </div>

            <div class="heading-actions">

                <a href="greetingUser.php" class="btn btn-back">
                    <span>&larr;</span>
                    Back
                </a>

                <a href="download_competent.php" class="btn btn-download">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 3v12m0 0l4-4m-4 4l-4-4M5 17v3h14v-3"/>
                    </svg>
                    Download List
                </a>

            </div>

        </div>

        <div class="stats-grid">

            <div class="stat-card">
                <div class="stat-top">
                    <div>
                        <p class="stat-label">Competent Records</p>
                        <h2 class="stat-number">
                            <?php echo $totalCompetent; ?>
                        </h2>
                    </div>

                    <div class="stat-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                stroke-width="2"
                                d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                </div>

                <p class="stat-description">
                    Assessment results with 70 marks or above
                </p>
            </div>

            <div class="stat-card">
                <p class="stat-label">Required Marks</p>
                <h2 class="stat-number">70+</h2>
                <p class="stat-description">
                    Minimum marks for competency
                </p>
            </div>

            <div class="stat-card">
                <p class="stat-label">Academic Status</p>

                <div>
                    <span class="status-badge">Competent</span>
                </div>

                <p class="stat-description">
                    Trainees meeting the required marks
                </p>
            </div>

        </div>

        <div class="results-card">

            <div class="results-header">
                <div>
                    <h2>Competency Results</h2>
                    <p>List of competent trainees by module.</p>
                </div>

                <span class="record-count">
                    Total records:
                    <strong><?php echo $totalCompetent; ?></strong>
                </span>
            </div>

            <div class="table-wrapper">

                <table>

                    <thead>
                        <tr>
                            <th>Trainee Code</th>
                            <th>Trainee Name</th>
                            <th>Module Code</th>
                            <th>Module Name</th>
                            <th>Trade Name</th>
                            <th>Decision</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php if ($totalCompetent > 0): ?>

                            <?php while ($data = mysqli_fetch_assoc($query)): ?>

                                <tr>
                                    <td class="trainee-code">
                                        <?php echo htmlspecialchars($data['Trainee_Id'], ENT_QUOTES, 'UTF-8'); ?>
                                    </td>

                                    <td class="trainee-name">
                                        <?php echo htmlspecialchars($data['Trainee_Name'], ENT_QUOTES, 'UTF-8'); ?>
                                    </td>

                                    <td>
                                        <?php echo htmlspecialchars($data['Module_Id'], ENT_QUOTES, 'UTF-8'); ?>
                                    </td>

                                    <td>
                                        <?php echo htmlspecialchars($data['Module_Name'], ENT_QUOTES, 'UTF-8'); ?>
                                    </td>

                                    <td>
                                        <?php echo htmlspecialchars($data['Trade_name'], ENT_QUOTES, 'UTF-8'); ?>
                                    </td>

                                    <td>
                                        <span class="decision-badge">
                                            <?php echo htmlspecialchars($data['decision'], ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    </td>
                                </tr>

                            <?php endwhile; ?>

                        <?php else: ?>

                            <tr>
                                <td colspan="6">
                                    <div class="empty-state">

                                        <div class="empty-icon">
                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </div>

                                        <h3>No competent trainees found</h3>

                                        <p>
                                            Competent assessment records will appear here.
                                        </p>

                                    </div>
                                </td>
                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

            <div class="results-footer">
                <p>GIKONKO TSS · Student Management System</p>
            </div>

        </div>

    </main>

    <?php include("footer.php"); ?>

</body>
</html>
