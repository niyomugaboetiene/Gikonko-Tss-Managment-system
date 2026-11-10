
<?php
include("conn.php");
session_start();

if (!isset($_SESSION['username'])) {
    $_SESSION['login_error'] = "Please login to access this page";
    header("Location:index.php");
    exit();
}

$sql = "SELECT
            m.Module_Id,
            m.Module_Name,
            m.Trade_Id,
            t.Trade_name
        FROM modules m
        JOIN trades t ON t.Trade_id = m.Trade_Id
        ORDER BY m.Module_Id ASC";

$result = mysqli_query($conn, $sql);

if (!$result) {
    error_log("Modules query failed: " . mysqli_error($conn));
    $_SESSION['error'] = "Unable to load module records.";
    header("Location:greetingUser.php");
    exit();
}

$totalModules = mysqli_num_rows($result);
$modules = [];

while ($row = mysqli_fetch_assoc($result)) {
    $modules[] = $row;
}

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$tradeQuery = mysqli_query($conn, "SELECT COUNT(*) AS total FROM trades");
$totalTrades = "—";

if ($tradeQuery) {
    $tradeData = mysqli_fetch_assoc($tradeQuery);
    $totalTrades = (int)$tradeData['total'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Module Management | GIKONKO TSS</title>
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
            grid-template-columns: repeat(2, minmax(0, 1fr));
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

        .module-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            white-space: nowrap;
            font-size: 13px;
        }

        .module-table thead {
            background: #f8fafc;
            color: #64748b;
        }

        .module-table th {
            padding: 15px 22px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.6px;
            text-transform: uppercase;
        }

        .module-table td {
            padding: 17px 22px;
            color: #475569;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        .module-table tbody tr:last-child td {
            border-bottom: none;
        }

        .module-table tbody tr:hover {
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

        .trade-name {
            display: inline-flex;
            align-items: center;
            padding: 6px 10px;
            color: #1d4ed8;
            background: #eff6ff;
            border-radius: 5px;
            font-size: 11px;
            font-weight: 700;
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

                <h1 class="page-title">Module Management</h1>

                <p class="page-description">
                    Manage training modules and their associated trades.
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

                <a href="addModule.php" class="btn btn-primary">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add New Module
                </a>

            </div>
        </div>

        <div class="stats-grid">

            <div class="stat-card">
                <div class="stat-top">
                    <div>
                        <p class="stat-label">Total Modules</p>

                        <h2 class="stat-number">
                            <?php echo $totalModules; ?>
                        </h2>
                    </div>

                    <div class="stat-icon icon-blue">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5s3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18s-3.332.477-4.5 1.253"/>
                        </svg>
                    </div>
                </div>

                <p class="stat-note">
                    All registered training modules
                </p>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <div>
                        <p class="stat-label">Registered Trades</p>

                        <h2 class="stat-number">
                            <?php echo $totalTrades; ?>
                        </h2>
                    </div>

                    <div class="stat-icon icon-purple">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  stroke-width="2" d="M12 6v12m-8-6h16"/>
                        </svg>
                    </div>
                </div>

                <p class="stat-note">
                    Trades available in the system
                </p>
            </div>

        </div>

        <section class="records-section">

            <div class="section-header">
                <div>
                    <h2 class="section-title">Registered Modules</h2>

                    <p class="section-description">
                        View and manage all training modules.
                    </p>
                </div>

                <div class="search-box">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="7" stroke-width="2"/>
                        <path stroke-linecap="round" stroke-width="2" d="m16 16 4 4"/>
                    </svg>

                    <input
                        type="text"
                        id="moduleSearch"
                        class="search-input"
                        placeholder="Search module or trade..."
                    >
                </div>
            </div>

            <div class="table-wrapper">
                <table class="module-table" id="moduleTable">

                    <thead>
                        <tr>
                            <th>Module Code</th>
                            <th>Module Name</th>
                            <th>Trade Code</th>
                            <th>Trade Name</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php if ($totalModules > 0): ?>

                        <?php foreach ($modules as $data): ?>

                            <tr class="module-row">

                                <td>
                                    <span class="module-code">
                                        #<?php echo e($data['Module_Id']); ?>
                                    </span>
                                </td>

                                <td>
                                    <span class="module-name">
                                        <?php echo e($data['Module_Name']); ?>
                                    </span>
                                </td>

                                <td>
                                    <?php echo e($data['Trade_Id']); ?>
                                </td>

                                <td>
                                    <span class="trade-name">
                                        <?php echo e($data['Trade_name']); ?>
                                    </span>
                                </td>

                                <td>
                                    <div class="action-buttons">

                                        <a
                                            href="updateModule.php?Module_Id=<?php echo urlencode($data['Module_Id']); ?>"
                                            class="action-btn update-btn"
                                        >
                                            Update
                                        </a>

                                        <a
                                            href="deleteModule.php?Module_Id=<?php echo urlencode($data['Module_Id']); ?>"
                                            onclick="return confirm('Are you sure you want to delete this module? Its related marks may also be deleted.')"
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
                                              stroke-width="2" d="M12 6v12m-8-6h16"/>
                                    </svg>
                                </div>

                                <h3 class="empty-title">
                                    No modules found
                                </h3>

                                <p class="empty-description">
                                    There are currently no registered modules.
                                </p>

                                <a href="addModule.php" class="empty-link">
                                    Add your first module
                                </a>

                            </td>
                        </tr>

                    <?php endif; ?>

                    <tr id="noSearchResults" class="no-search-results hidden">
                        <td colspan="5">
                            No matching modules found.
                        </td>
                    </tr>

                    </tbody>
                </table>
            </div>

            <div class="table-footer">
                <p>
                    Showing <span id="visibleCount"><?php echo $totalModules; ?></span>
                    of <?php echo $totalModules; ?> modules
                </p>

                <p>GIKONKO TSS · Module Management</p>
            </div>

        </section>

    </div>
</main>

<?php include("footer.php"); ?>

<script>
const searchInput = document.getElementById("moduleSearch");
const rows = document.querySelectorAll(".module-row");
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
