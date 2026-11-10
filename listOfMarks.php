
<?php
include("conn.php");
session_start();

if (!isset($_SESSION['username'])) {
    $_SESSION['login_error'] = "Please login to access this page";
    header("Location:index.php");
    exit();
}

$sql = "SELECT
            m.Mark_Id,
            m.Trainee_Id,
            CONCAT(t.Firstname, ' ', t.lastname) AS Trainee_Name,
            m.Module_Id,
            md.Module_Name,
            m.Formative_Assessment,
            m.Summative_Assessment,
            m.Total_Marks,
            m.Decision
        FROM marks m
        JOIN trainees t ON m.Trainee_Id = t.Trainee_Id
        JOIN modules md ON m.Module_Id = md.Module_Id
        ORDER BY m.Trainee_Id ASC";

$query = mysqli_query($conn, $sql);

if (!$query) {
    error_log("Marks query failed: " . mysqli_error($conn));
    $_SESSION['error'] = "Unable to load marks records.";
    header("Location:greetingUser.php");
    exit();
}

$totalRecords = mysqli_num_rows($query);
$competentCount = 0;
$notCompetentCount = 0;
$records = [];

while ($data = mysqli_fetch_assoc($query)) {
    $records[] = $data;

    if ((float)$data['Total_Marks'] >= 70) {
        $competentCount++;
    } else {
        $notCompetentCount++;
    }
}

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Marks Management | GIKONKO TSS</title>
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
            margin-bottom: 30px;
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
            font-size: 30px;
            font-weight: 750;
            color: #1e293b;
        }

        .page-description {
            margin-top: 9px;
            color: #64748b;
            font-size: 14px;
            line-height: 1.6;
        }

        .heading-actions {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 11px 16px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 650;
            transition: background 0.2s, border-color 0.2s;
        }

        .btn svg {
            width: 17px;
            height: 17px;
        }

        .btn-back {
            color: #475569;
            background: #ffffff;
            border: 1px solid #cbd5e1;
        }

        .btn-back:hover {
            background: #f1f5f9;
        }

        .btn-primary {
            color: #ffffff;
            background: #1d4ed8;
            border: 1px solid #1d4ed8;
        }

        .btn-primary:hover {
            background: #1e40af;
            border-color: #1e40af;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            padding: 22px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 9px;
            box-shadow: 0 2px 5px rgba(15, 23, 42, 0.025);
        }

        .stat-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
        }

        .stat-label {
            color: #64748b;
            font-size: 13px;
            font-weight: 600;
        }

        .stat-number {
            margin-top: 10px;
            font-size: 30px;
            font-weight: 750;
            line-height: 1;
        }

        .stat-blue {
            color: #1e293b;
        }

        .stat-green {
            color: #15803d;
        }

        .stat-red {
            color: #b91c1c;
        }

        .stat-icon {
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
        }

        .stat-icon svg {
            width: 23px;
            height: 23px;
        }

        .icon-blue {
            color: #1d4ed8;
            background: #eff6ff;
        }

        .icon-green {
            color: #15803d;
            background: #f0fdf4;
        }

        .icon-red {
            color: #b91c1c;
            background: #fef2f2;
        }

        .stat-note {
            margin-top: 14px;
            color: #94a3b8;
            font-size: 12px;
        }

        .records-section {
            overflow: hidden;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 9px;
            box-shadow: 0 2px 5px rgba(15, 23, 42, 0.025);
        }

        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
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
        }

        .search-box {
            position: relative;
            width: 330px;
            max-width: 100%;
        }

        .search-box svg {
            position: absolute;
            top: 50%;
            left: 12px;
            width: 19px;
            height: 19px;
            color: #94a3b8;
            transform: translateY(-50%);
            pointer-events: none;
        }

        .search-input {
            width: 100%;
            padding: 11px 14px 11px 40px;
            color: #334155;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            outline: none;
            font-size: 13px;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .search-input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12);
        }

        .table-wrapper {
            width: 100%;
            overflow-x: auto;
        }

        .marks-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            white-space: nowrap;
            font-size: 13px;
        }

        .marks-table thead {
            background: #f8fafc;
            color: #64748b;
        }

        .marks-table th {
            padding: 15px 20px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.6px;
            text-transform: uppercase;
        }

        .marks-table td {
            padding: 16px 20px;
            border-bottom: 1px solid #f1f5f9;
            color: #475569;
            vertical-align: middle;
        }

        .marks-table tbody tr:last-child td {
            border-bottom: none;
        }

        .marks-table tbody tr:hover {
            background: #f8fafc;
        }

        .text-center {
            text-align: center;
        }

        .mark-code {
            color: #334155;
            font-weight: 700;
        }

        .trainee-name,
        .module-name {
            color: #1e293b;
            font-weight: 650;
        }

        .secondary-text {
            margin-top: 5px;
            color: #94a3b8;
            font-size: 11px;
        }

        .total-mark {
            color: #1e293b;
            font-weight: 750;
        }

        .status {
            display: inline-flex;
            align-items: center;
            padding: 6px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
        }

        .status-competent {
            color: #15803d;
            background: #f0fdf4;
        }

        .status-not-competent {
            color: #b91c1c;
            background: #fef2f2;
        }

        .action-buttons {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
        }

        .action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 7px 11px;
            border-radius: 5px;
            font-size: 11px;
            font-weight: 700;
            transition: background 0.2s;
        }

        .update-btn {
            color: #1d4ed8;
            background: #eff6ff;
        }

        .update-btn:hover {
            background: #dbeafe;
        }

        .delete-btn {
            color: #b91c1c;
            background: #fef2f2;
        }

        .delete-btn:hover {
            background: #fee2e2;
        }

        .empty-state {
            padding: 55px 20px !important;
            text-align: center;
        }

        .empty-icon {
            width: 55px;
            height: 55px;
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
            margin-top: 15px;
            color: #1e293b;
            font-size: 15px;
            font-weight: 700;
        }

        .empty-description {
            margin-top: 7px;
            color: #64748b;
            font-size: 13px;
        }

        .empty-link {
            display: inline-block;
            margin-top: 15px;
            color: #1d4ed8;
            font-size: 13px;
            font-weight: 700;
        }

        .empty-link:hover {
            text-decoration: underline;
        }

        .no-search-results td {
            padding: 28px 20px;
            color: #64748b;
            text-align: center;
        }

        .hidden {
            display: none !important;
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

            .stats-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .section-header {
                align-items: flex-start;
                flex-direction: column;
            }

            .search-box {
                width: 100%;
            }
        }

        @media (max-width: 520px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }

            .heading-actions {
                width: 100%;
            }

            .heading-actions .btn {
                flex: 1;
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
    <?php include("Dashboard.php"); ?>
</header>

<main>
    <div class="container">

        <div class="page-heading">
            <div>
                <p class="eyebrow">Academic Administration</p>

                <h1 class="page-title">Marks Management</h1>

                <p class="page-description">
                    View and manage trainee assessment records.
                </p>
            </div>

            <div class="heading-actions">
                <a href="greetingUser.php" class="btn btn-back">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Back
                </a>

                <a href="addMarks.php" class="btn btn-primary">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add New Marks
                </a>
            </div>
        </div>

        <div class="stats-grid">

            <div class="stat-card">
                <div class="stat-top">
                    <div>
                        <p class="stat-label">Total Assessments</p>
                        <h2 class="stat-number stat-blue">
                            <?php echo $totalRecords; ?>
                        </h2>
                    </div>

                    <div class="stat-icon icon-blue">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                </div>

                <p class="stat-note">All recorded trainee assessments</p>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <div>
                        <p class="stat-label">Competent</p>
                        <h2 class="stat-number stat-green">
                            <?php echo $competentCount; ?>
                        </h2>
                    </div>

                    <div class="stat-icon icon-green">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                </div>

                <p class="stat-note">Assessments with 70 marks or above</p>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <div>
                        <p class="stat-label">Not Competent</p>
                        <h2 class="stat-number stat-red">
                            <?php echo $notCompetentCount; ?>
                        </h2>
                    </div>

                    <div class="stat-icon icon-red">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </div>
                </div>

                <p class="stat-note">Assessments below 70 marks</p>
            </div>

        </div>

        <section class="records-section">

            <div class="section-header">
                <div>
                    <h2 class="section-title">Assessment Records</h2>

                    <p class="section-description">
                        A complete list of trainee marks and decisions.
                    </p>
                </div>

                <div class="search-box">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="7" stroke-width="2"/>
                        <path stroke-linecap="round" stroke-width="2" d="m16 16 4 4"/>
                    </svg>

                    <input
                        type="text"
                        id="marksSearch"
                        class="search-input"
                        placeholder="Search trainee, module or mark code..."
                    >
                </div>
            </div>

            <div class="table-wrapper">
                <table class="marks-table" id="marksTable">

                    <thead>
                        <tr>
                            <th>Mark Code</th>
                            <th>Trainee</th>
                            <th>Module</th>
                            <th class="text-center">Formative</th>
                            <th class="text-center">Summative</th>
                            <th class="text-center">Total</th>
                            <th class="text-center">Decision</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php if ($totalRecords > 0): ?>

                        <?php foreach ($records as $data): ?>

                            <?php
                            $isCompetent = (float)$data['Total_Marks'] >= 70;
                            ?>

                            <tr class="marks-row">

                                <td>
                                    <span class="mark-code">
                                        #<?php echo e($data['Mark_Id']); ?>
                                    </span>
                                </td>

                                <td>
                                    <p class="trainee-name">
                                        <?php echo e($data['Trainee_Name']); ?>
                                    </p>

                                    <p class="secondary-text">
                                        Trainee ID: <?php echo e($data['Trainee_Id']); ?>
                                    </p>
                                </td>

                                <td>
                                    <p class="module-name">
                                        <?php echo e($data['Module_Name']); ?>
                                    </p>

                                    <p class="secondary-text">
                                        Module ID: <?php echo e($data['Module_Id']); ?>
                                    </p>
                                </td>

                                <td class="text-center">
                                    <?php echo e($data['Formative_Assessment']); ?>/50
                                </td>

                                <td class="text-center">
                                    <?php echo e($data['Summative_Assessment']); ?>/50
                                </td>

                                <td class="text-center">
                                    <span class="total-mark">
                                        <?php echo e($data['Total_Marks']); ?>/100
                                    </span>
                                </td>

                                <td class="text-center">
                                    <?php if ($isCompetent): ?>

                                        <span class="status status-competent">
                                            Competent
                                        </span>

                                    <?php else: ?>

                                        <span class="status status-not-competent">
                                            Not Competent
                                        </span>

                                    <?php endif; ?>
                                </td>

                                <td>
                                    <div class="action-buttons">

                                        <a
                                            href="updateMarks.php?Mark_Id=<?php echo urlencode($data['Mark_Id']); ?>"
                                            class="action-btn update-btn"
                                        >
                                            Update
                                        </a>

                                        <a
                                            href="deleteMarks.php?Mark_Id=<?php echo urlencode($data['Mark_Id']); ?>"
                                            onclick="return confirm('Are you sure you want to delete these marks?')"
                                            class="action-btn delete-btn"
                                        >
                                            Delete
                                        </a>

                                    </div>
                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="8" class="empty-state">

                                <div class="empty-icon">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              stroke-width="2"
                                              d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>

                                <h3 class="empty-title">
                                    No marks records found
                                </h3>

                                <p class="empty-description">
                                    Start by adding the first assessment record.
                                </p>

                                <a href="addMarks.php" class="empty-link">
                                    Add marks
                                </a>

                            </td>
                        </tr>

                    <?php endif; ?>

                    <tr id="noSearchResults" class="no-search-results hidden">
                        <td colspan="8">
                            No matching assessment records found.
                        </td>
                    </tr>

                    </tbody>
                </table>
            </div>

            <div class="table-footer">
                <p>
                    Showing <span id="visibleCount"><?php echo $totalRecords; ?></span>
                    of <?php echo $totalRecords; ?> assessment records
                </p>

                <p>GIKONKO TSS · Marks Management</p>
            </div>

        </section>

    </div>
</main>

<?php include("footer.php"); ?>

<script>
const searchInput = document.getElementById("marksSearch");
const rows = document.querySelectorAll(".marks-row");
const visibleCount = document.getElementById("visibleCount");
const noSearchResults = document.getElementById("noSearchResults");

searchInput.addEventListener("input", function () {
    const searchValue = this.value.toLowerCase().trim();
    let count = 0;

    rows.forEach(function (row) {
        const matches = row.textContent.toLowerCase().includes(searchValue);

        row.classList.toggle("hidden", !matches);

        if (matches) {
            count++;
        }
    });

    visibleCount.textContent = count;

    noSearchResults.classList.toggle(
        "hidden",
        count !== 0 || rows.length === 0
    );
});
</script>

</body>
</html>
