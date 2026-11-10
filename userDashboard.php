
<?php
include("conn.php");
session_start();

if (!isset($_SESSION['Trainee_Id']) || !isset($_SESSION['Firstname'])) {
    header("Location: loginStudent.php");
    exit();
}

$firstname = htmlspecialchars($_SESSION['Firstname'], ENT_QUOTES, 'UTF-8');
$traineeId = (int)$_SESSION['Trainee_Id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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

        /* Navigation */
        .site-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 100;
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            box-shadow: 0 2px 6px rgba(15, 23, 42, 0.04);
        }

        .nav-container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 24px;
        }

        .nav-main {
            min-height: 78px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }

        .brand-logo {
            width: 47px;
            height: 47px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            border-radius: 9px;
            background: #ffffff;
        }

        .brand-logo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .brand-info h1 {
            color: #0f172a;
            font-size: 17px;
            font-weight: 800;
            letter-spacing: -0.3px;
        }

        .brand-info p {
            color: #64748b;
            font-size: 12px;
            margin-top: 4px;
        }

        .desktop-nav {
            display: flex;
            align-items: center;
            gap: 27px;
        }

        .nav-link {
            position: relative;
            padding: 29px 0;
            color: #64748b;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            transition: color 0.2s;
        }

        .nav-link:hover,
        .nav-link.active {
            color: #1d4ed8;
        }

        .nav-link.active::after {
            position: absolute;
            content: "";
            height: 2px;
            background: #2563eb;
            bottom: 0;
            left: 0;
            right: 0;
        }

        .logout-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            padding: 11px 17px;
            color: #ffffff;
            background: #dc2626;
            border: 1px solid #dc2626;
            border-radius: 7px;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            transition: background 0.2s;
        }

        .logout-btn:hover {
            background: #b91c1c;
        }

        .mobile-nav {
            display: none;
        }

        /* Main content */
        main {
            flex: 1;
            width: 100%;
            padding: 112px 24px 55px;
        }

        .content-container {
            max-width: 1280px;
            margin: 0 auto;
        }

        /* Welcome section */
        .welcome-card {
            position: relative;
            overflow: hidden;
            padding: 42px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(15, 23, 42, 0.035);
        }

        .welcome-label {
            display: inline-block;
            color: #1d4ed8;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 15px;
        }

        .welcome-card h2 {
            color: #0f172a;
            font-size: 32px;
            font-weight: 800;
            letter-spacing: -0.8px;
            line-height: 1.3;
        }

        .welcome-card h2 span {
            color: #2563eb;
        }

        .welcome-description {
            max-width: 650px;
            margin-top: 14px;
            color: #64748b;
            font-size: 14px;
            line-height: 1.8;
        }

        .trainee-code {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            margin-top: 25px;
            padding: 11px 16px;
            background: #eff6ff;
            border: 1px solid #dbeafe;
            border-radius: 7px;
        }

        .trainee-code span:first-child {
            color: #475569;
            font-size: 13px;
            font-weight: 600;
        }

        .trainee-code span:last-child {
            color: #1d4ed8;
            font-size: 14px;
            font-weight: 800;
        }

        /* Dashboard cards */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 22px;
            margin-top: 27px;
        }

        .dashboard-card {
            padding: 27px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 11px;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.025);
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .dashboard-card:hover {
            border-color: #cbd5e1;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.05);
        }

        .card-icon {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 21px;
            border-radius: 9px;
        }

        .card-icon svg {
            width: 24px;
            height: 24px;
        }

        .icon-blue {
            color: #1d4ed8;
            background: #eff6ff;
        }

        .icon-green {
            color: #047857;
            background: #ecfdf5;
        }

        .dashboard-card h3 {
            color: #1e293b;
            font-size: 18px;
            font-weight: 700;
        }

        .dashboard-card p {
            margin-top: 10px;
            color: #64748b;
            font-size: 13px;
            line-height: 1.8;
        }

        .card-link {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            margin-top: 22px;
            color: #1d4ed8;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            transition: gap 0.2s, color 0.2s;
        }

        .card-link:hover {
            gap: 14px;
            color: #1e40af;
        }

        .account-status {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            margin-top: 22px;
            color: #047857;
            font-size: 13px;
            font-weight: 700;
        }

        .status-dot {
            width: 9px;
            height: 9px;
            background: #10b981;
            border-radius: 50%;
        }

        /* Footer */
        .site-footer {
            margin-top: auto;
            padding: 22px 16px;
            background: #ffffff;
            border-top: 1px solid #e2e8f0;
            text-align: center;
        }

        .site-footer p {
            color: #64748b;
            font-size: 13px;
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .nav-container {
                padding: 0 18px;
            }

            .nav-main {
                min-height: 70px;
            }

            .desktop-nav {
                display: none;
            }

            .mobile-nav {
                display: flex;
                align-items: center;
                gap: 25px;
                padding: 0 0 14px;
            }

            .mobile-nav .nav-link {
                padding: 5px 0;
            }

            .mobile-nav .nav-link.active::after {
                display: none;
            }

            main {
                padding: 125px 18px 40px;
            }

            .welcome-card {
                padding: 30px;
            }

            .welcome-card h2 {
                font-size: 27px;
            }

            .dashboard-grid {
                gap: 16px;
            }

            .dashboard-card {
                padding: 22px;
            }
        }

        @media (max-width: 560px) {
            .brand-logo {
                width: 42px;
                height: 42px;
            }

            .brand-info h1 {
                font-size: 15px;
            }

            .nav-main {
                min-height: 67px;
            }

            .logout-btn {
                padding: 9px 13px;
                font-size: 12px;
            }

            .mobile-nav {
                gap: 22px;
            }

            main {
                padding-top: 120px;
            }

            .welcome-card {
                padding: 25px 21px;
            }

            .welcome-card h2 {
                font-size: 24px;
            }

            .welcome-description {
                font-size: 13px;
            }

            .dashboard-grid {
                grid-template-columns: 1fr;
            }

            .dashboard-card {
                padding: 24px;
            }
        }
    </style>
</head>

<body>

    <header class="site-header">

        <nav class="nav-container">

            <div class="nav-main">

                <a href="userPage.php" class="brand">

                    <div class="brand-logo">
                        <img src="gikonko.png" alt="GIKONKO TSS Logo">
                    </div>

                    <div class="brand-info">
                        <h1>GIKONKO TSS</h1>
                        <p>Student Portal</p>
                    </div>

                </a>

                <div class="desktop-nav">

                    <a href="userPage.php" class="nav-link active">
                        Dashboard
                    </a>

                    <a
                        href="listOfUserMarks.php?Trainee_Id=<?php echo $traineeId; ?>"
                        class="nav-link"
                    >
                        My Marks
                    </a>

                    <a href="userLogout.php" class="logout-btn">
                        Logout
                        <span aria-hidden="true">→</span>
                    </a>

                </div>

                <div class="mobile-logout">
                    <a href="userLogout.php" class="logout-btn">
                        Logout
                    </a>
                </div>

            </div>

            <div class="mobile-nav">

                <a href="userPage.php" class="nav-link active">
                    Dashboard
                </a>

                <a
                    href="listOfUserMarks.php?Trainee_Id=<?php echo $traineeId; ?>"
                    class="nav-link"
                >
                    My Marks
                </a>

            </div>

        </nav>

    </header>

    <main>

        <div class="content-container">

            <section class="welcome-card">

                <p class="welcome-label">
                    Student Dashboard
                </p>

                <h2>
                    Welcome back, <span><?php echo $firstname; ?>!</span>
                </h2>

                <p class="welcome-description">
                    Welcome to the GIKONKO TSS Student Portal. You can access
                    your academic information and check your assessment
                    results whenever you need them.
                </p>

                <div class="trainee-code">
                    <span>Trainee Code:</span>
                    <span><?php echo $traineeId; ?></span>
                </div>

            </section>

            <section class="dashboard-grid">

                <div class="dashboard-card">

                    <div class="card-icon icon-blue">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                            <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"/>
                        </svg>
                    </div>

                    <h3>View Your Marks</h3>

                    <p>
                        Check your formative assessments, summative
                        assessments, total marks and competency status.
                    </p>

                    <a
                        href="listOfUserMarks.php?Trainee_Id=<?php echo $traineeId; ?>"
                        class="card-link"
                    >
                        View Results
                        <span aria-hidden="true">→</span>
                    </a>

                </div>

                <div class="dashboard-card">

                    <div class="card-icon icon-green">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <circle cx="12" cy="8" r="4"/>
                            <path d="M5 21v-2a7 7 0 0 1 14 0v2"/>
                        </svg>
                    </div>

                    <h3>Your Student Account</h3>

                    <p>
                        You are signed in to your student portal. Your
                        academic records are associated with your trainee code.
                    </p>

                    <span class="account-status">
                        <span class="status-dot"></span>
                        Account Active
                    </span>

                </div>

            </section>

        </div>

    </main>

    <footer class="site-footer">
        <p>
            &copy; <?php echo date("Y"); ?> GIKONKO TSS. All rights reserved.
        </p>
    </footer>

</body>
</html>
