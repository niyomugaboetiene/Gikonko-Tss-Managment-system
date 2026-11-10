
<?php
include("conn.php");
session_start();

if (!isset($_SESSION['username'])) {
    $_SESSION['login_error'] = "Please login to access this page";
    header("Location:index.php");
    exit();
}

$sql = "SELECT
            t.Trainee_Id,
            t.Firstname,
            t.lastname,
            t.Gender,
            t.Trade_Id,
            tr.Trade_name
        FROM trainees t
        JOIN trades tr ON t.Trade_Id = tr.Trade_id
        ORDER BY t.Trainee_Id ASC";

$query = mysqli_query($conn, $sql);

if (!$query) {
    error_log("Trainees query failed: " . mysqli_error($conn));
    $_SESSION['error'] = "Unable to load trainee records.";
    header("Location:greetingUser.php");
    exit();
}

$totalTrainees = mysqli_num_rows($query);
$trainees = [];
$maleCount = 0;
$femaleCount = 0;

while ($data = mysqli_fetch_assoc($query)) {
    $trainees[] = $data;

    if (strtolower($data['Gender']) === 'male') {
        $maleCount++;
    } elseif (strtolower($data['Gender']) === 'female') {
        $femaleCount++;
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
    <title>Trainee Management | GIKONKO TSS</title>
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
            color: #1e293b;
            font-size: 30px;
            font-weight: 750;
            line-height: 1;
        }

        .stat-number.blue {
            color: #1d4ed8;
        }

        .stat-number.purple {
            color: #7e22ce;
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

        .icon-purple {
            color: #7e22ce;
            background: #faf5ff;
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
            width: 350px;
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

        .trainee-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            white-space: nowrap;
            font-size: 13px;
        }

        .trainee-table thead {
            background: #f8fafc;
            color: #64748b;
        }

        .trainee-table th {
            padding: 15px 20px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.6px;
            text-transform: uppercase;
        }

        .trainee-table td {
            padding: 16px 20px;
            color: #475569;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        .trainee-table tbody tr:last-child td {
            border-bottom: none;
        }

        .trainee-table tbody tr.trainee-row:hover {
            background: #f8fafc;
        }

        .trainee-code {
            color: #334155;
            font-weight: 700;
        }

        .trainee-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .trainee-avatar {
            width: 40px;
            height: 40px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #1d4ed8;
            background: #eff6ff;
            border-radius: 50%;
            font-size: 15px;
            font-weight: 750;
        }

        .trainee-name {
            color: #1e293b;
            font-size: 13px;
            font-weight: 650;
        }

        .trainee-subtitle {
            margin-top: 5px;
            color: #94a3b8;
            font-size: 11px;
        }

        .gender-badge {
            display: inline-flex;
            align-items: center;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
        }

        .gender-male {
            color: #1d4ed8;
            background: #eff6ff;
        }

        .gender-female {
            color: #7e22ce;
            background: #faf5ff;
        }

        .gender-other {
            color: #475569;
            background: #f1f5f9;
        }

        .trade-name {
            color: #1e293b;
            font-size: 13px;
            font-weight: 650;
        }

        .trade-subtitle {
            margin-top: 5px;
            color: #94a3b8;
            font-size: 11px;
        }

        .text-center {
            text-align: center;
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

        @media (max-width: 900px) {
            .stats-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .page-heading {
                align-items: flex-start;
                flex-direction: column;
            }
        }

        @media (max-width: 768px) {
            main {
                padding: 115px 16px 40px;
            }

            .page-title {
                font-size: 25px;
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

                <h1 class="page-title">Trainee Management</h1>

                <p class="page-description">
                    Manage trainee information and their registered trades.
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

                <a href="addTrainee.php" class="btn btn-primary">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add New Trainee
                </a>

            </div>
        </div>

        <div class="stats-grid">

            <div class="stat-card">
                <div class="stat-top">
                    <div>
                        <p class="stat-label">Total Trainees</p>

                        <h2 class="stat-number">
                            <?php echo $totalTrainees; ?>
                        </h2>
                    </div>

                    <div class="stat-icon icon-blue">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M17 20h5v-2a3 3 0 00-5.824-1M17 20H7m10 0v-2c0-.656-.126-1.283-.355-1.857M7 20H2v-2a3 3 0 015.824-1M7 20v-2c0-.656.126-1.283.355-1.857m0 0a5.002 5.002 0 019.29 0M15 6a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 9a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                </div>

                <p class="stat-note">All registered trainees</p>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <div>
                        <p class="stat-label">Male Trainees</p>

                        <h2 class="stat-number blue">
                            <?php echo $maleCount; ?>
                        </h2>
                    </div>

                    <div class="stat-icon icon-blue">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2m8-10a4 4 0 100-8 4 4 0 000 8z"/>
                        </svg>
                    </div>
                </div>

                <p class="stat-note">Registered male trainees</p>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <div>
                        <p class="stat-label">Female Trainees</p>

                        <h2 class="stat-number purple">
                            <?php echo $femaleCount; ?>
                        </h2>
                    </div>

                    <div class="stat-icon icon-purple">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M12 15v6m-3-3h6m3-9a6 6 0 11-12 0 6 6 0 0112 0z"/>
                        </svg>
                    </div>
                </div>

                <p class="stat-note">Registered female trainees</p>
            </div>

        </div>

        <section class="records-section">

            <div class="section-header">
                <div>
                    <h2 class="section-title">Registered Trainees</h2>

                    <p class="section-description">
                        View and manage trainee records.
                    </p>
                </div>

                <div class="search-box">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="7" stroke-width="2"/>
                        <path stroke-linecap="round" stroke-width="2" d="m16 16 4 4"/>
                    </svg>

                    <input
                        type="text"
                        id="traineeSearch"
                        class="search-input"
                        placeholder="Search name, code, gender or trade..."
                    >
                </div>
            </div>

            <div class="table-wrapper">
                <table class="trainee-table" id="traineeTable">

                    <thead>
                        <tr>
                            <th>Trainee Code</th>
                            <th>Trainee Name</th>
                            <th>Gender</th>
                            <th>Trade</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php if ($totalTrainees > 0): ?>

                        <?php foreach ($trainees as $data): ?>

                            <tr class="trainee-row">

                                <td>
                                    <span class="trainee-code">
                                        #<?php echo e($data['Trainee_Id']); ?>
                                    </span>
                                </td>

                                <td>
                                    <div class="trainee-info">

                                        <div class="trainee-avatar">
                                            <?php echo e(strtoupper(substr($data['Firstname'], 0, 1))); ?>
                                        </div>

                                        <div>
                                            <p class="trainee-name">
                                                <?php echo e($data['Firstname'] . ' ' . $data['lastname']); ?>
                                            </p>

                                            <p class="trainee-subtitle">
                                                Trainee ID: <?php echo e($data['Trainee_Id']); ?>
                                            </p>
                                        </div>

                                    </div>
                                </td>

                                <td>
                                    <?php if (strtolower($data['Gender']) === 'male'): ?>

                                        <span class="gender-badge gender-male">
                                            Male
                                        </span>

                                    <?php elseif (strtolower($data['Gender']) === 'female'): ?>

                                        <span class="gender-badge gender-female">
                                            Female
                                        </span>

                                    <?php else: ?>

                                        <span class="gender-badge gender-other">
                                            <?php echo e($data['Gender']); ?>
                                        </span>

                                    <?php endif; ?>
                                </td>

                                <td>
                                    <p class="trade-name">
                                        <?php echo e($data['Trade_name']); ?>
                                    </p>

                                    <p class="trade-subtitle">
                                        Trade ID: <?php echo e($data['Trade_Id']); ?>
                                    </p>
                                </td>

                                <td>
                                    <div class="action-buttons">

                                        <a
                                            href="updateTrainee.php?Trainee_Id=<?php echo urlencode($data['Trainee_Id']); ?>"
                                            class="action-btn update-btn"
                                        >
                                            Update
                                        </a>

                                        <a
                                            href="deleteTrainee.php?Trainee_Id=<?php echo urlencode($data['Trainee_Id']); ?>"
                                            onclick="return confirm('Are you sure you want to delete this trainee? Their related marks may also be deleted.')"
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
                            <td colspan="5" class="empty-state">

                                <div class="empty-icon">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              stroke-width="2"
                                              d="M17 20h5v-2a3 3 0 00-5.824-1M17 20H7m10 0v-2c0-.656-.126-1.283-.355-1.857M7 20H2v-2a3 3 0 015.824-1M7 20v-2c0-.656.126-1.283.355-1.857m0 0a5.002 5.002 0 019.29 0M15 6a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </div>

                                <h3 class="empty-title">
                                    No trainees found
                                </h3>

                                <p class="empty-description">
                                    There are currently no registered trainees.
                                </p>

                                <a href="addTrainee.php" class="empty-link">
                                    Register your first trainee
                                </a>

                            </td>
                        </tr>

                    <?php endif; ?>

                    <tr id="noSearchResults" class="no-search-results hidden">
                        <td colspan="5">
                            No matching trainees found.
                        </td>
                    </tr>

                    </tbody>
                </table>
            </div>

            <div class="table-footer">
                <p>
                    Showing <span id="visibleCount"><?php echo $totalTrainees; ?></span>
                    of <?php echo $totalTrainees; ?> trainees
                </p>

                <p>GIKONKO TSS · Trainee Management</p>
            </div>

        </section>

    </div>
</main>

<?php include("footer.php"); ?>

<script>
const searchInput = document.getElementById("traineeSearch");
const rows = document.querySelectorAll(".trainee-row");
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
