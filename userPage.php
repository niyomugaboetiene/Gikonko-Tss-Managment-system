
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
   <link href="/Gikonko-Tss-Managment-system/output.css" rel="stylesheet">
    <link rel="icon" href="gikonko.png" type="image/png">
</head>

<body class="min-h-screen bg-slate-50 flex flex-col font-sans">

    <header class="sticky top-0 z-50 bg-white border-b border-slate-200 shadow-sm">
        <?php include("userDashboard.php"); ?>
    </header>

    <main class="flex-grow px-4 sm:px-6 lg:px-8 py-10">

        <div class="max-w-7xl mx-auto">

            <section class="bg-white border border-slate-200 rounded-lg shadow-sm overflow-hidden">

                <div class="p-6 sm:p-10">

                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6">

                        <div>
                            <p class="text-sm font-semibold uppercase tracking-wider text-blue-700">
                                Student Portal
                            </p>

                            <h1 class="text-2xl sm:text-4xl font-bold text-slate-800 mt-3">
                                Welcome back,
                                <span class="text-blue-700"><?php echo $firstname; ?></span>
                            </h1>

                            <p class="text-slate-500 mt-4 max-w-2xl leading-relaxed">
                                You are now logged in to the GIKONKO TSS Trainee Management System.
                                Access your academic results and stay informed about your progress.
                            </p>
                        </div>

                        <div class="flex items-center justify-center w-16 h-16 rounded-full bg-blue-50 text-blue-700 shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32"
                                 viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                 stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                        </div>

                    </div>

                    <div class="mt-8 pt-6 border-t border-slate-100 grid grid-cols-1 sm:grid-cols-2 gap-5">

                        <div>
                            <p class="text-xs uppercase tracking-wide font-semibold text-slate-500">
                                Trainee Code
                            </p>
                            <p class="text-lg font-bold text-slate-800 mt-1">
                                <?php echo $traineeId; ?>
                            </p>
                        </div>

                        <div>
                            <p class="text-xs uppercase tracking-wide font-semibold text-slate-500">
                                Account Status
                            </p>
                            <p class="flex items-center gap-2 text-sm font-semibold text-emerald-700 mt-2">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                Logged In
                            </p>
                        </div>

                    </div>

                </div>
            </section>

            <section class="mt-8">
                <div class="mb-5">
                    <h2 class="text-xl font-bold text-slate-800">
                        Quick Access
                    </h2>
                    <p class="text-sm text-slate-500 mt-1">
                        Access your academic information from here.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-6">

                        <div class="w-12 h-12 flex items-center justify-center rounded-md bg-blue-50 text-blue-700 mb-5">
                            <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26"
                                 viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                 stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"/>
                            </svg>
                        </div>

                        <h3 class="text-lg font-bold text-slate-800">
                            My Academic Marks
                        </h3>

                        <p class="text-sm text-slate-500 leading-relaxed mt-2">
                            View your formative assessments, summative assessments, total marks and competency results.
                        </p>

                        <a href="listOfUserMarks.php?Trainee_Id=<?php echo $traineeId; ?>"
                           class="inline-flex items-center gap-2 mt-5 text-sm font-semibold text-blue-700 hover:text-blue-900 transition">
                            View My Marks
                            <span aria-hidden="true">→</span>
                        </a>

                    </div>

                    <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-6">

                        <div class="w-12 h-12 flex items-center justify-center rounded-md bg-emerald-50 text-emerald-700 mb-5">
                            <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26"
                                 viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                 stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 22s8-4 8-11V5l-8-3-8 3v6c0 7 8 11 8 11Z"/>
                                <path d="m9 12 2 2 4-4"/>
                            </svg>
                        </div>

                        <h3 class="text-lg font-bold text-slate-800">
                            Student Portal
                        </h3>

                        <p class="text-sm text-slate-500 leading-relaxed mt-2">
                            Your student account gives you access to your personal academic records through a secure login.
                        </p>

                        <span class="inline-flex items-center gap-2 mt-5 text-sm font-semibold text-emerald-700">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
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
