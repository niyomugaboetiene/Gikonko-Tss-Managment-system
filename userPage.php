
<?php
session_start();
include("conn.php");

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

        header {
            position: sticky;
            top: 0;
            z-index: 50;
            width: 100%;
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            box-shadow: 0 2px 6px rgba(15, 23, 42, 0.04);
        }

        main {
            flex: 1;
            width: 100%;
            padding: 42px 24px 55px;
        }

        .content-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Welcome section */
        .welcome-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(15, 23, 42, 0.035);
        }

        .welcome-content {
            padding: 40px;
        }

        .welcome-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 30px;
        }

        .welcome-text {
            flex: 1;
        }

        .eyebrow {
            color: #1d4ed8;
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1.2px;
        }

        .welcome-text h1 {
            margin-top: 15px;
            color: #0f172a;
            font-size: 34px;
            font-weight: 800;
            line-height: 1.3;
            letter-spacing: -0.8px;
        }

        .welcome-text h1 span {
            color: #2563eb;
        }

        .welcome-description {
            max-width: 650px;
            margin-top: 15px;
            color: #64748b;
            font-size: 14px;
            line-height: 1.8;
        }

        .profile-icon {
            flex-shrink: 0;
            width: 76px;
            height: 76px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #1d4ed8;
            background: #eff6ff;
            border-radius: 50%;
        }

        .profile-icon svg {
            width: 36px;
            height: 36px;
        }

        .welcome-details {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 25px;
            margin-top: 32px;
            padding-top: 25px;
            border-top: 1px solid #e2e8f0;
        }

        .detail-label {
            color: #64748b;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }

        .detail-value {
            margin-top: 8px;
            color: #1e293b;
            font-size: 18px;
            font-weight: 800;
        }

        .status-value {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            color: #047857;
            font-size: 14px;
            font-weight: 700;
            margin-top: 10px;
        }

        .status-dot {
            width: 9px;
            height: 9px;
            background: #10b981;
            border-radius: 50%;
        }

        /* Quick access */
        .quick-access {
            margin-top: 36px;
        }

        .section-heading {
            margin-bottom: 20px;
        }

        .section-heading h2 {
            color: #0f172a;
            font-size: 21px;
            font-weight: 800;
        }

        .section-heading p {
            margin-top: 7px;
            color: #64748b;
            font-size: 13px;
            line-height: 1.6;
        }

        .quick-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 22px;
        }

        .quick-card {
            padding: 28px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 11px;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.025);
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .quick-card:hover {
            border-color: #cbd5e1;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.05);
        }

        .quick-icon {
            width: 49px;
            height: 49px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 21px;
            border-radius: 9px;
        }

        .quick-icon svg {
            width: 26px;
            height: 26px;
        }

        .icon-blue {
            color: #1d4ed8;
            background: #eff6ff;
        }

        .icon-green {
            color: #047857;
            background: #ecfdf5;
        }

        .quick-card h3 {
            color: #1e293b;
            font-size: 18px;
            font-weight: 700;
        }

        .quick-card p {
            margin-top: 10px;
            color: #64748b;
            font-size: 13px;
            line-height: 1.8;
        }

        .quick-link {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            margin-top: 21px;
            color: #1d4ed8;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            transition: gap 0.2s, color 0.2s;
        }

        .quick-link:hover {
            gap: 14px;
            color: #1e40af;
        }

        .portal-status {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            margin-top: 21px;
            color: #047857;
            font-size: 13px;
            font-weight: 700;
        }

        /* Responsive */
        @media (max-width: 768px) {
            main {
                padding: 32px 18px 45px;
            }

            .welcome-content {
                padding: 30px;
            }

            .welcome-text h1 {
                font-size: 29px;
            }

            .quick-grid {
                gap: 16px;
            }

            .quick-card {
                padding: 23px;
            }
        }

        @media (max-width: 600px) {
            main {
                padding: 25px 15px 40px;
            }

            .welcome-content {
                padding: 24px 20px;
            }

            .welcome-top {
                align-items: flex-start;
                gap: 15px;
            }

            .welcome-text h1 {
                font-size: 25px;
            }

            .welcome-description {
                font-size: 13px;
            }

            .profile-icon {
                width: 52px;
                height: 52px;
            }

            .profile-icon svg {
                width: 27px;
                height: 27px;
            }

            .welcome-details {
                grid-template-columns: 1fr;
                gap: 20px;
                margin-top: 25px;
                padding-top: 22px;
            }

            .quick-access {
                margin-top: 29px;
            }

            .section-heading h2 {
                font-size: 19px;
            }

            .quick-grid {
                grid-template-columns: 1fr;
            }

            .quick-card {
                padding: 23px;
            }
        }
    </style>
</head>

<body>

    <header>
        <?php include("userDashboard.php"); ?>
    </header>

    <main>

        <div class="content-container">

            <section class="welcome-card">

                <div class="welcome-content">

                    <div class="welcome-top">

                        <div class="welcome-text">

                            <p class="eyebrow">
                                Student Portal
                            </p>

                            <h1>
                                Welcome back,
                                <span><?php echo $firstname; ?></span>
                            </h1>

                            <p class="welcome-description">
                                You are now logged in to the GIKONKO TSS Trainee
                                Management System. Access your academic results
                                and stay informed about your progress.
                            </p>

                        </div>

                        <div class="profile-icon">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                        </div>

                    </div>

                    <div class="welcome-details">

                        <div>
                            <p class="detail-label">
                                Trainee Code
                            </p>

                            <p class="detail-value">
                                <?php echo $traineeId; ?>
                            </p>
                        </div>

                        <div>
                            <p class="detail-label">
                                Account Status
                            </p>

                            <p class="status-value">
                                <span class="status-dot"></span>
                                Logged In
                            </p>
                        </div>

                    </div>

                </div>

            </section>

            <section class="quick-access">

                <div class="section-heading">

                    <h2>Quick Access</h2>

                    <p>
                        Access your academic information from here.
                    </p>

                </div>

                <div class="quick-grid">

                    <div class="quick-card">

                        <div class="quick-icon icon-blue">

                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"/>
                            </svg>

                        </div>

                        <h3>My Academic Marks</h3>

                        <p>
                            View your formative assessments, summative
                            assessments, total marks and competency results.
                        </p>

                        <a
                            href="listOfUserMarks.php?Trainee_Id=<?php echo $traineeId; ?>"
                            class="quick-link"
                        >
                            View My Marks
                            <span aria-hidden="true">→</span>
                        </a>

                    </div>

                    <div class="quick-card">

                        <div class="quick-icon icon-green">

                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <path d="M12 22s8-4 8-11V5l-8-3-8 3v6c0 7 8 11 8 11Z"/>
                                <path d="m9 12 2 2 4-4"/>
                            </svg>

                        </div>

                        <h3>Student Portal</h3>

                        <p>
                            Your student account gives you access to your
                            personal academic records through a secure login.
                        </p>

                        <span class="portal-status">
                            <span class="status-dot"></span>
                            Portal Active
                        </span>

                    </div>

                </div>

            </section>

        </div>

    </main>

    <?php include("footer.php"); ?>

</body>
</html>
