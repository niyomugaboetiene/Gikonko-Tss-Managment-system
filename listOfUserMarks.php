
<?php
include("conn.php");

session_start();

if (!isset($_SESSION['Firstname']) || !isset($_SESSION['Trainee_Id'])) {
    $_SESSION['login_error'] = "Please login to access this page";
    header("Location:login.php");
    exit();
}

$Trainee_Id = $_SESSION['Trainee_Id'];

$sql = "SELECT
            t.Trainee_Id,
            CONCAT(t.Firstname, ' ', t.lastname) AS Trainee_Name,
            m.Module_Id,
            md.Module_Name,
            m.Formative_Assessment,
            m.Summative_Assessment,
            m.Total_Marks,
            m.decision
        FROM trainees t
        JOIN marks m ON t.Trainee_Id = m.Trainee_Id
        JOIN modules md ON m.Module_Id = md.Module_Id
        WHERE t.Trainee_Id = ?
        ORDER BY md.Module_Name ASC";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    error_log("Student marks prepare failed: " . mysqli_error($conn));
    $_SESSION['error'] = "Unable to retrieve your marks.";
    header("Location:userPage.php");
    exit();
}

mysqli_stmt_bind_param($stmt, "i", $Trainee_Id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (!$result) {
    error_log("Student marks query failed: " . mysqli_stmt_error($stmt));
    $_SESSION['error'] = "Unable to retrieve your marks.";
    header("Location:userPage.php");
    exit();
}

$marks = [];
$totalRecords = 0;
$competentCount = 0;
$notCompetentCount = 0;
$totalMarks = 0;

while ($data = mysqli_fetch_assoc($result)) {
    $marks[] = $data;
    $totalRecords++;
    $totalMarks += (float)$data['Total_Marks'];

    if ((float)$data['Total_Marks'] >= 70) {
        $competentCount++;
    } else {
        $notCompetentCount++;
    }
}

$averageMarks = $totalRecords > 0 ? $totalMarks / $totalRecords : 0;

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

mysqli_stmt_close($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Academic Results | GIKONKO TSS</title>
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

        a {
            text-decoration: none;
        }

        .site-header {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 50;
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            box-shadow: 0 2px 5px rgba(15, 23, 42, 0.04);
        }

        main {
            flex: 1;
            width: 100%;
            padding: 125px 24px 55px;
        }

        .container {
            width: 100%;
            max-width: 1280px;
            margin: 0 auto;
        }

        .page-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
            margin-bottom: 28px;
        }

        .eyebrow {
            color: #1d4ed8;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 1.2px;
            text-transform: uppercase;
        }

        .page-title {
            margin-top: 9px;
            color: #1e293b;
            font-size: 30px;
            font-weight: 750;
        }

        .page-description {
            margin-top: 9px;
            color: #64748b;
            font-size: 14px;
            line-height: 1.6;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 11px 16px;
            color: #475569;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 650;
            transition: background 0.2s;
        }

        .btn:hover {
            background: #f1f5f9;
        }

        .btn svg {
            width: 17px;
            height: 17px;
        }

        .profile-card {
            display: flex;
            align-items: center;
            gap: 17px;
            padding: 23px;
            margin-bottom: 25px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 9px;
            box-shadow: 0 2px 5px rgba(15, 23, 42, 0.025);
        }

        .profile-avatar {
            width: 58px;
            height: 58px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #1d4ed8;
            background: #eff6ff;
            border-radius: 50%;
            font-size: 21px;
            font-weight: 750;
        }

        .profile-label {
            color: #64748b;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .profile-name {
            margin-top: 6px;
            color: #1e293b;
            font-size: 20px;
            font-weight: 750;
        }

        .profile-code {
            margin-top: 6px;
            color: #64748b;
            font-size: 13px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 18px;
            margin-bottom: 30px;
        }

        .stat-card {
            padding: 21px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 9px;
            box-shadow: 0 2px 5px rgba(15, 23, 42, 0.025);
        }

        .stat-label {
            color: #64748b;
            font-size: 13px;
            font-weight: 600;
        }

        .stat-number {
            margin-top: 12px;
            color: #1e293b;
            font-size: 30px;
            font-weight: 750;
            line-height: 1.1;
        }

        .stat-blue {
            color: #1d4ed8;
        }

        .stat-green {
            color: #047857;
        }

        .stat-red {
            color: #dc2626;
        }

        .stat-note {
            margin-top: 12px;
            color: #94a3b8;
            font-size: 12px;
            line-height: 1.5;
        }

        .results-section {
            overflow: hidden;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 9px;
            box-shadow: 0 2px 5px rgba(15, 23, 42, 0.025);
        }

        .section-header {
            padding: 22px 24px;
            border-bottom: 1px solid #e2e8f0;
        }

        .section-title {
            color: #1e293b;
            font-size: 17px;
            font-weight: 700;
        }

        .section-description {
            margin-top: 6px;
            color: #64748b;
            font-size: 13px;
            line-height: 1.5;
        }

        .table-wrapper {
            width: 100%;
            overflow-x: auto;
        }

        .results-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            white-space: nowrap;
            font-size: 13px;
        }

        .results-table thead {
            color: #64748b;
            background: #f8fafc;
        }

        .results-table th {
            padding: 15px 20px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.6px;
            text-transform: uppercase;
        }

        .results-table td {
            padding: 17px 20px;
            color: #475569;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        .results-table tbody tr:last-child td {
            border-bottom: none;
        }

        .results-table tbody tr:hover {
            background: #f8fafc;
        }

        .module-code {
            color: #334155;
            font-weight: 700;
        }

        .module-name {
            color: #1e293b;
            font-weight: 650;
        }

        .mark-value {
            color: #475569;
            font-weight: 600;
        }

        .total-value {
            color: #1e293b;
            font-weight: 750;
        }

        .text-center {
            text-align: center;
        }

        .decision-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 6px 11px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
        }

        .competent {
            color: #047857;
            background: #ecfdf5;
        }

        .not-competent {
            color: #b91c1c;
            background: #fef2f2;
        }

        .empty-state {
            padding: 60px 20px !important;
            text-align: center;
        }

        .empty-icon {
            width: 56px;
            height: 56px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            color: #94a3b8;
            background: #f1f5f9;
            border-radius: 50%;
        }

        .empty-icon svg {
            width: 27px;
            height: 27px;
        }

        .empty-title {
            margin-top: 16px;
            color: #1e293b;
            font-size: 15px;
            font-weight: 700;
        }

        .empty-description {
            margin-top: 8px;
            color: #64748b;
            font-size: 13px;
            line-height: 1.6;
        }

        .table-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            padding: 15px 22px;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
        }

        .table-footer p {
            color: #64748b;
            font-size: 12px;
        }

        @media (max-width: 1000px) {
            .stats-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 768px) {
            main {
                padding: 115px 16px 40px;
            }

            .page-heading {
                align-items: flex-start;
                flex-direction: column;
            }

            .page-title {
                font-size: 25px;
            }
        }

        @media (max-width: 520px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }

            .profile-card {
                align-items: flex-start;
                padding: 18px;
            }

            .profile-avatar {
                width: 48px;
                height: 48px;
                font-size: 18px;
            }

            .profile-name {
                font-size: 17px;
            }

            .table-footer {
                align-items: flex-start;
                flex-direction: column;
            }
        }
    </style>
</head>

<body>

<header class="site-header">
    <?php include("userDashboard.php"); ?>
</header>

<main>
    <div class="container">

        <div class="page-heading">
            <div>
                <p class="eyebrow">Student Academic Portal</p>

                <h1 class="page-title">My Academic Results</h1>

                <p class="page-description">
                    View your assessment marks and competency results.
                </p>
            </div>

            <a href="userPage.php" class="btn">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Back to Dashboard
            </a>
        </div>

        <div class="profile-card">

            <div class="profile-avatar">
                <?php echo e(strtoupper(substr($_SESSION['Firstname'], 0, 1))); ?>
            </div>

            <div>
                <p class="profile-label">Student Profile</p>

                <h2 class="profile-name">
                    <?php echo e($_SESSION['Firstname']); ?>
                </h2>

                <p class="profile-code">
                    Trainee Code: #<?php echo e($Trainee_Id); ?>
                </p>
            </div>

        </div>

        <div class="stats-grid">

            <div class="stat-card">
                <p class="stat-label">Modules Assessed</p>

                <h2 class="stat-number">
                    <?php echo $totalRecords; ?>
                </h2>

                <p class="stat-note">
                    Available assessment records
                </p>
            </div>

            <div class="stat-card">
                <p class="stat-label">Average Marks</p>

                <h2 class="stat-number stat-blue">
                    <?php echo number_format($averageMarks, 1); ?>%
                </h2>

                <p class="stat-note">
                    Average across assessed modules
                </p>
            </div>

            <div class="stat-card">
                <p class="stat-label">Competent</p>

                <h2 class="stat-number stat-green">
                    <?php echo $competentCount; ?>
                </h2>

                <p class="stat-note">
                    Modules with 70 marks or above
                </p>
            </div>

            <div class="stat-card">
                <p class="stat-label">Not Competent</p>

                <h2 class="stat-number stat-red">
                    <?php echo $notCompetentCount; ?>
                </h2>

                <p class="stat-note">
                    Modules below 70 marks
                </p>
            </div>

        </div>

        <section class="results-section">

            <div class="section-header">
                <h2 class="section-title">
                    Assessment Records
                </h2>

                <p class="section-description">
                    Your formative, summative and final assessment results.
                </p>
            </div>

            <div class="table-wrapper">

                <table class="results-table">

                    <thead>
                        <tr>
                            <th>Module Code</th>
                            <th>Module Name</th>
                            <th class="text-center">Formative /50</th>
                            <th class="text-center">Summative /50</th>
                            <th class="text-center">Total</th>
                            <th class="text-center">Decision</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php if ($totalRecords > 0): ?>

                        <?php foreach ($marks as $data): ?>

                            <tr>

                                <td>
                                    <span class="module-code">
                                        #<?php echo e($data['Module_Id']); ?>
                                    </span>
                                </td>

                                <td>
                                    <p class="module-name">
                                        <?php echo e($data['Module_Name']); ?>
                                    </p>
                                </td>

                                <td class="text-center">
                                    <span class="mark-value">
                                        <?php echo e($data['Formative_Assessment']); ?>
                                    </span>
                                </td>

                                <td class="text-center">
                                    <span class="mark-value">
                                        <?php echo e($data['Summative_Assessment']); ?>
                                    </span>
                                </td>

                                <td class="text-center">
                                    <span class="total-value">
                                        <?php echo e($data['Total_Marks']); ?>/100
                                    </span>
                                </td>

                                <td class="text-center">

                                    <?php if ((float)$data['Total_Marks'] >= 70): ?>

                                        <span class="decision-badge competent">
                                            Competent
                                        </span>

                                    <?php else: ?>

                                        <span class="decision-badge not-competent">
                                            Not Competent
                                        </span>

                                    <?php endif; ?>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="6" class="empty-state">

                                <div class="empty-icon">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              stroke-width="2"
                                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>

                                <h3 class="empty-title">
                                    No assessment results yet
                                </h3>

                                <p class="empty-description">
                                    Your marks will appear here once they have been recorded.
                                </p>

                            </td>
                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

            <div class="table-footer">

                <p>
                    Showing <?php echo $totalRecords; ?> assessment records
                </p>

                <p>
                    GIKONKO TSS · Student Academic Portal
                </p>

            </div>

        </section>

    </div>
</main>

<?php include("footer.php"); ?>

</body>
</html>
