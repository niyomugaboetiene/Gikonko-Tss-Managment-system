
<?php
include("conn.php");
session_start();

if (!isset($_SESSION['username'])) {
    $_SESSION['login_error'] = "Please login to access this page";
    header("Location:loginStudent.php");
    exit();
}

header("Expires: Tue, 01 Jan 2000 00:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

$username = htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8');
$initial = strtoupper(substr($_SESSION['username'], 0, 1));
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Dashboard | GIKONKO TSS</title>
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

        .student-main {
            flex: 1;
            width: 100%;
            padding: 115px 24px 45px;
        }

        .dashboard-container {
            width: 100%;
            max-width: 1280px;
            margin: 0 auto;
        }

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

        .welcome-section p:last-child {
            color: #64748b;
            font-size: 14px;
            margin-top: 9px;
        }

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
            .student-main {
                padding: 100px 15px 35px;
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
        <?php include("DashboardStudent.php"); ?>
    </header>

    <main class="student-main">

        <div class="dashboard-container">

            <div class="welcome-section">

                <p class="current-date">
                    <?php echo date("l, F j, Y"); ?>
                </p>

                <h1>
                    Welcome back, <?php echo $username; ?>!
                </h1>

                <p>
                    Welcome to your GIKONKO TSS student portal.
                </p>

            </div>

            <section class="hero-card">

                <div class="hero-grid">

                    <div class="hero-content">
                        <h2>
                            Your education journey starts here.
                        </h2>

                        <p class="hero-description">
                            Access your academic information, follow your assessment
                            progress, and stay connected with your training journey
                            at GIKONKO Technical Secondary School.
                        </p>

                        <div class="hero-actions">

                            <a href="listOfMarks.php" class="btn btn-primary">
                                View Marks

                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>

                            <a href="studentProfile.php" class="btn btn-secondary">
                                My Profile
                            </a>

                        </div>

                    </div>

                    <div class="hero-side">

                        <div class="account-card">

                            <div class="account-header">

                                <div>
                                    <p class="account-label">
                                        Student account
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
                                        <p>Welcome to your student portal</p>
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
                                        <h4>Academic Progress</h4>
                                        <p>Keep working towards your goals</p>
                                    </div>

                                </div>

                            </div>

                            <div class="account-footer">
                                <p>
                                    GIKONKO TSS · Student Management System
                                </p>
                            </div>

                        </div>

                    </div>

                </div>

            </section>

            <section class="quick-access">

                <div class="section-heading">
                    <h2>Quick Access</h2>

                    <p>
                        Navigate to your student services.
                    </p>
                </div>

                <div class="quick-grid">

                    <a href="studentMarks.php" class="quick-card">

                        <div class="quick-icon blue">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>

                        <h3>My Academic Marks</h3>

                        <p>
                            View your assessment results and academic performance.
                        </p>

                        <span class="quick-link">
                            View results &rarr;
                        </span>

                    </a>

                    <a href="studentProfile.php" class="quick-card">

                        <div class="quick-icon purple">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>

                        <h3>My Profile</h3>

                        <p>
                            Access your personal student account information.
                        </p>

                        <span class="quick-link">
                            View profile &rarr;
                        </span>

                    </a>

                    <a
                        href="logoutStudent.php"
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
                            Securely sign out of your student account.
                        </p>

                        <span class="quick-link">
                            Sign out &rarr;
                        </span>

                    </a>

                </div>

            </section>

            <div class="message-card">

                <h3>A message for every trainee</h3>

                <p>
                    Success comes through consistency, discipline, and hard work.
                    Stay focused on your studies and continue building the skills
                    that will shape your future.
                </p>

            </div>

        </div>

    </main>

    <?php include("footer.php"); ?>

</body>
</html>
