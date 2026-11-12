
<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/conn.php';

if (!isset($_SESSION['username'])) {
    $_SESSION['login_error'] = "Please login to access this page.";
    header("Location: index.php");
    exit();
}

header("Expires: Tue, 01 Jan 2000 00:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

$username = htmlspecialchars(
    $_SESSION['username'],
    ENT_QUOTES,
    'UTF-8'
);

$initial = strtoupper(substr($_SESSION['username'], 0, 1));

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>DOS Dashboard | GIKONKO TSS</title>
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

        .admin-main {
            flex: 1;
            width: 100%;
            padding: 45px 24px;
        }

        .dashboard-container {
            width: 100%;
            max-width: 1280px;
            margin: 0 auto;
        }

        /* Welcome section */

        .welcome-section {
            margin-bottom: 30px;
        }

        .current-date {
            color: #64748b;
            font-size: 13px;
        }

        .welcome-section h1 {
            color: #0f172a;
            font-size: 29px;
            font-weight: 700;
            letter-spacing: -0.5px;
            margin-top: 9px;
        }

        .welcome-section h1 span {
            color: #1d4ed8;
        }

        .welcome-section p:last-child {
            color: #64748b;
            font-size: 14px;
            margin-top: 9px;
        }

        /* Hero section */

        .hero-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 9px;
            overflow: hidden;
            box-shadow: 0 2px 6px rgba(15, 23, 42, 0.04);
        }

        .hero-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .hero-content {
            padding: 48px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .hero-label {
            color: #1d4ed8;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .hero-content h2 {
            color: #0f172a;
            font-size: 37px;
            line-height: 1.2;
            font-weight: 700;
            margin-top: 17px;
            letter-spacing: -0.8px;
        }

        .hero-description {
            color: #64748b;
            font-size: 14px;
            line-height: 1.8;
            margin-top: 18px;
            max-width: 530px;
        }

        .hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 28px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            padding: 12px 18px;
            min-height: 43px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            transition: background 0.2s, border-color 0.2s;
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

        .btn-primary svg {
            width: 17px;
            height: 17px;
        }

        .btn-secondary {
            color: #334155;
            background: #ffffff;
            border: 1px solid #cbd5e1;
        }

        .btn-secondary:hover {
            background: #f8fafc;
        }

        /* Administrator account card */

        .hero-side {
            background: #f1f5f9;
            padding: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .account-card {
            width: 100%;
            max-width: 390px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 25px;
            box-shadow: 0 2px 6px rgba(15, 23, 42, 0.04);
        }

        .account-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding-bottom: 20px;
            border-bottom: 1px solid #f1f5f9;
        }

        .account-label {
            color: #64748b;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.8px;
            text-transform: uppercase;
        }

        .account-name {
            color: #0f172a;
            font-size: 20px;
            font-weight: 700;
            margin-top: 8px;
            overflow-wrap: anywhere;
        }

        .account-avatar {
            width: 48px;
            height: 48px;
            min-width: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #1d4ed8;
            background: #eff6ff;
            border-radius: 50%;
            font-size: 20px;
            font-weight: 700;
        }

        .account-details {
            display: flex;
            flex-direction: column;
            gap: 20px;
            padding: 22px 0;
        }

        .account-item {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .item-icon {
            width: 38px;
            height: 38px;
            min-width: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
        }

        .item-icon svg {
            width: 20px;
            height: 20px;
        }

        .icon-green {
            color: #15803d;
            background: #f0fdf4;
        }

        .icon-blue {
            color: #1d4ed8;
            background: #eff6ff;
        }

        .account-item h4 {
            color: #334155;
            font-size: 13px;
            font-weight: 700;
        }

        .account-item p {
            color: #94a3b8;
            font-size: 11px;
            margin-top: 4px;
        }

        .account-footer {
            padding-top: 16px;
            border-top: 1px solid #f1f5f9;
        }

        .account-footer p {
            color: #94a3b8;
            font-size: 11px;
        }

        /* Quick access */

        .quick-access {
            margin-top: 35px;
        }

        .section-heading {
            margin-bottom: 20px;
        }

        .section-heading h2 {
            color: #0f172a;
            font-size: 21px;
            font-weight: 700;
        }

        .section-heading p {
            color: #64748b;
            font-size: 13px;
            margin-top: 6px;
        }

        .quick-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
        }

        .quick-card {
            display: block;
            padding: 25px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .quick-card:hover {
            border-color: #93c5fd;
            box-shadow: 0 3px 10px rgba(15, 23, 42, 0.05);
        }

        .quick-card.logout-card:hover {
            border-color: #fca5a5;
        }

        .quick-icon {
            width: 44px;
            height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 7px;
        }

        .quick-icon svg {
            width: 24px;
            height: 24px;
        }

        .quick-icon.blue {
            color: #1d4ed8;
            background: #eff6ff;
        }

        .quick-icon.purple {
            color: #7e22ce;
            background: #faf5ff;
        }

        .quick-icon.green {
            color: #15803d;
            background: #f0fdf4;
        }

        .quick-icon.orange {
            color: #c2410c;
            background: #fff7ed;
        }

        .quick-icon.red {
            color: #dc2626;
            background: #fef2f2;
        }

        .quick-card h3 {
            color: #0f172a;
            font-size: 15px;
            font-weight: 700;
            margin-top: 17px;
            transition: color 0.2s;
        }

        .quick-card:hover h3 {
            color: #1d4ed8;
        }

        .quick-card.logout-card:hover h3 {
            color: #b91c1c;
        }

        .quick-card p {
            color: #64748b;
            font-size: 13px;
            line-height: 1.6;
            margin-top: 8px;
        }

        .quick-link {
            display: inline-block;
            color: #1d4ed8;
            font-size: 13px;
            font-weight: 600;
            margin-top: 17px;
        }

        .logout-card .quick-link {
            color: #dc2626;
        }

        /* Information message */

        .message-card {
            margin-top: 30px;
            padding: 22px;
            background: #eff6ff;
            border: 1px solid #dbeafe;
            border-radius: 8px;
        }

        .message-card h3 {
            color: #1e3a8a;
            font-size: 15px;
            font-weight: 700;
        }

        .message-card p {
            color: #1e40af;
            font-size: 13px;
            line-height: 1.7;
            margin-top: 8px;
        }

        @media (max-width: 900px) {
            .hero-grid {
                grid-template-columns: 1fr;
            }

            .hero-content {
                padding: 35px;
            }

            .hero-side {
                padding: 30px;
            }

            .quick-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 600px) {
            .admin-main {
                padding: 30px 15px 35px;
            }

            .welcome-section h1 {
                font-size: 24px;
            }

            .hero-content {
                padding: 25px 20px;
            }

            .hero-content h2 {
                font-size: 29px;
            }

            .hero-side {
                padding: 20px;
            }

            .account-card {
                padding: 20px;
            }

            .quick-grid {
                grid-template-columns: 1fr;
                gap: 13px;
            }

            .quick-card {
                padding: 21px;
            }

            .hero-actions {
                flex-direction: column;
            }

            .hero-actions .btn {
                width: 100%;
            }
        }
    </style>
</head>

<body>

    <header>
        <?php include("userDashboard.php"); ?>
    </header>

    <main class="admin-main">

        <div class="dashboard-container">

            <div class="welcome-section">

                <p class="current-date">
                    <?php echo date("l, F j, Y"); ?>
                </p>

                <h1>
                    Welcome back, <span><?php echo $username; ?></span>!
                </h1>

                <p>
                    Director of Studies (DOS) Administration Portal.
                </p>

            </div>

            <section class="hero-card">

                <div class="hero-grid">

                    <div class="hero-content">

                        <p class="hero-label">DOS Administration</p>

                        <h2>
                            Manage academics. Empower every trainee.
                        </h2>

                        <p class="hero-description">
                            Welcome to the GIKONKO TSS Director of Studies
                            dashboard. Manage trainee records, organize
                            academic modules, monitor assessment results,
                            and oversee the academic progress of trainees.
                        </p>

                        <div class="hero-actions">

                            <a href="Dashboard.php" class="btn btn-primary">
                                Administration Dashboard
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>

                            <a href="listOfMarks.php" class="btn btn-secondary">
                                Manage Marks
                            </a>

                        </div>

                    </div>

                    <div class="hero-side">

                        <div class="account-card">

                            <div class="account-header">

                                <div>
                                    <p class="account-label">
                                        Administrator Account
                                    </p>

                                    <h3 class="account-name">
                                        <?php echo $username; ?>
                                    </h3>
                                </div>

                                <div class="account-avatar">
                                    <?php echo htmlspecialchars($initial, ENT_QUOTES, 'UTF-8'); ?>
                                </div>

                            </div>

                            <div class="account-details">

                                <div class="account-item">

                                    <div class="item-icon icon-green">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>

                                    <div>
                                        <h4>Account Active</h4>
                                        <p>Administrator access verified</p>
                                    </div>

                                </div>

                                <div class="account-item">

                                    <div class="item-icon icon-blue">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M12 6v6l4 2m6-2a10 10 0 11-20 0 10 10 0 0120 0z"/>
                                        </svg>
                                    </div>

                                    <div>
                                        <h4>Academic Administration</h4>
                                        <p>Director of Studies Portal</p>
                                    </div>

                                </div>

                            </div>

                            <div class="account-footer">
                                <p>
                                    GIKONKO TSS · DOS Management System
                                </p>
                            </div>

                        </div>

                    </div>

                </div>

            </section>

            <section class="quick-access">

                <div class="section-heading">

                    <h2>Administrative Quick Access</h2>

                    <p>
                        Manage and monitor the main academic activities.
                    </p>

                </div>

                <div class="quick-grid">

                    <a href="listOfTrainee.php" class="quick-card">

                        <div class="quick-icon blue">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M17 20h5v-2a4 4 0 00-4-4h-1M9 20H2v-2a4 4 0 014-4h3m8-7a4 4 0 11-8 0 4 4 0 018 0zM16 3.13a4 4 0 010 7.75"/>
                            </svg>
                        </div>

                        <h3>Trainee Management</h3>

                        <p>
                            View, register, update and manage trainee records.
                        </p>

                        <span class="quick-link">
                            Manage trainees →
                        </span>

                    </a>

                    <a href="listOfMarks.php" class="quick-card">

                        <div class="quick-icon purple">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>

                        <h3>Assessment Management</h3>

                        <p>
                            Add, review and update trainee assessment marks.
                        </p>

                        <span class="quick-link">
                            Manage assessments →
                        </span>

                    </a>

                    <a href="listOfModule.php" class="quick-card">

                        <div class="quick-icon green">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5s3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18s-3.332.477-4.5 1.253"/>
                            </svg>
                        </div>

                        <h3>Module Management</h3>

                        <p>
                            Organize training modules and their assigned trades.
                        </p>

                        <span class="quick-link">
                            Manage modules →
                        </span>

                    </a>

                    <a href="listOfTrades.php" class="quick-card">

                        <div class="quick-icon orange">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-4M9 9v.01M9 12v.01M9 15v.01M9 18v.01"/>
                            </svg>
                        </div>

                        <h3>Trade Management</h3>

                        <p>
                            Manage training trades and their academic categories.
                        </p>

                        <span class="quick-link">
                            Manage trades →
                        </span>

                    </a>

                    <a href="greetingUser.php" class="quick-card">

                        <div class="quick-icon blue">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M3 3v18h18M18 17V9M13 17V5M8 17v-3"/>
                            </svg>
                        </div>

                        <h3>Academic Overview</h3>

                        <p>
                            Access the main administration dashboard and academic tools.
                        </p>

                        <span class="quick-link">
                            View overview →
                        </span>

                    </a>

                    <a href="logoutStudent.php"
                       onclick="return confirm('Are you sure you want to logout?')"
                       class="quick-card logout-card">

                        <div class="quick-icon red">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                        </div>

                        <h3>Logout</h3>

                        <p>
                            Securely sign out of the DOS administrator account.
                        </p>

                        <span class="quick-link">
                            Sign out →
                        </span>

                    </a>

                </div>

            </section>

            <div class="message-card">

                <h3>Director of Studies Responsibilities</h3>

                <p>
                    Ensure accurate academic records, monitor trainee
                    performance, coordinate training modules, and support
                    quality education at GIKONKO Technical Secondary School.
                    Your leadership helps every trainee achieve academic success.
                </p>

            </div>

        </div>

    </main>

    <?php include("footer.php"); ?>

</body>
</html>
