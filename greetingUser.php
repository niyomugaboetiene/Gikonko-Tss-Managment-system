
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Dashboard | GIKONKO TSS</title>
    <link href="output.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="gikonko.png">
</head>

<body class="min-h-screen bg-slate-50 flex flex-col">

<header class="w-full fixed top-0 left-0 z-50 bg-white border-b border-slate-200 shadow-sm">
    <?php include("DashboardStudent.php"); ?>
</header>

<main class="flex-grow pt-28 pb-12 px-4 sm:px-6 lg:px-8">

    <div class="max-w-7xl mx-auto">

        <div class="mb-8">
            <p class="text-sm text-slate-500">
                <?php echo date("l, F j, Y"); ?>
            </p>

            <h1 class="text-2xl sm:text-3xl font-bold text-slate-800 mt-2">
                Welcome back, <?php echo $username; ?>!
            </h1>

            <p class="text-slate-500 mt-2">
                Welcome to your GIKONKO TSS student portal.
            </p>
        </div>

        <section class="bg-white border border-slate-200 rounded-lg overflow-hidden shadow-sm">

            <div class="grid grid-cols-1 lg:grid-cols-2">

                <div class="p-7 sm:p-10 lg:p-12 flex flex-col justify-center">

                    <span class="text-sm font-semibold tracking-wider text-blue-700 uppercase">
                        GIKONKO TSS Student Portal
                    </span>

                    <h2 class="text-3xl sm:text-4xl font-bold text-slate-800 mt-4 leading-tight">
                        Your education journey starts here.
                    </h2>

                    <p class="text-slate-600 leading-relaxed mt-5">
                        Access your academic information, follow your assessment
                        progress, and stay connected with your training journey
                        at GIKONKO Technical Secondary School.
                    </p>

                    <div class="flex flex-wrap gap-3 mt-8">
                        <a href="studentMarks.php"
                           class="inline-flex items-center px-5 py-3 bg-blue-700 text-white text-sm font-semibold rounded-md hover:bg-blue-800 transition">
                            View My Marks
                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>

                        <a href="studentProfile.php"
                           class="inline-flex items-center px-5 py-3 border border-slate-300 text-slate-700 text-sm font-semibold rounded-md hover:bg-slate-50 transition">
                            My Profile
                        </a>
                    </div>

                </div>

                <div class="bg-slate-100 p-8 sm:p-10 flex items-center justify-center">

                    <div class="w-full max-w-sm">

                        <div class="bg-white rounded-lg border border-slate-200 p-6 shadow-sm">

                            <div class="flex items-center justify-between border-b border-slate-100 pb-5">

                                <div>
                                    <p class="text-xs uppercase tracking-wider text-slate-500 font-semibold">
                                        Student account
                                    </p>

                                    <h3 class="text-xl font-bold text-slate-800 mt-2">
                                        <?php echo $username; ?>
                                    </h3>
                                </div>

                                <div class="w-12 h-12 rounded-full bg-blue-50 text-blue-700 flex items-center justify-center text-xl font-bold">
                                    <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                                </div>

                            </div>

                            <div class="py-5 space-y-4">

                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-md bg-green-50 text-green-700 flex items-center justify-center">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>

                                    <div>
                                        <p class="text-sm font-semibold text-slate-800">Account Active</p>
                                        <p class="text-xs text-slate-500">Welcome to your student portal</p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-md bg-blue-50 text-blue-700 flex items-center justify-center">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  stroke-width="2" d="M12 6v6l4 2m6-2a10 10 0 11-20 0 10 10 0 0120 0z"/>
                                        </svg>
                                    </div>

                                    <div>
                                        <p class="text-sm font-semibold text-slate-800">Academic Progress</p>
                                        <p class="text-xs text-slate-500">Keep working towards your goals</p>
                                    </div>
                                </div>

                            </div>

                            <div class="pt-4 border-t border-slate-100">
                                <p class="text-xs text-slate-500">
                                    GIKONKO TSS · Student Management System
                                </p>
                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </section>

        <section class="mt-8">

            <div class="mb-5">
                <h2 class="text-xl font-bold text-slate-800">Quick Access</h2>
                <p class="text-sm text-slate-500 mt-1">
                    Navigate to your student services.
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">

                <a href="studentMarks.php"
                   class="bg-white border border-slate-200 rounded-lg p-6 hover:border-blue-300 hover:shadow-sm transition group">

                    <div class="w-11 h-11 bg-blue-50 text-blue-700 rounded-md flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>

                    <h3 class="text-base font-semibold text-slate-800 mt-4 group-hover:text-blue-700">
                        My Academic Marks
                    </h3>

                    <p class="text-sm text-slate-500 mt-2">
                        View your assessment results and academic performance.
                    </p>

                    <span class="text-sm text-blue-700 font-medium inline-block mt-4">
                        View results &rarr;
                    </span>

                </a>

                <a href="studentProfile.php"
                   class="bg-white border border-slate-200 rounded-lg p-6 hover:border-blue-300 hover:shadow-sm transition group">

                    <div class="w-11 h-11 bg-purple-50 text-purple-700 rounded-md flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>

                    <h3 class="text-base font-semibold text-slate-800 mt-4 group-hover:text-blue-700">
                        My Profile
                    </h3>

                    <p class="text-sm text-slate-500 mt-2">
                        Access your personal student account information.
                    </p>

                    <span class="text-sm text-blue-700 font-medium inline-block mt-4">
                        View profile &rarr;
                    </span>

                </a>

                <a href="logoutStudent.php"
                   onclick="return confirm('Are you sure you want to logout?')"
                   class="bg-white border border-slate-200 rounded-lg p-6 hover:border-red-300 hover:shadow-sm transition group">

                    <div class="w-11 h-11 bg-red-50 text-red-700 rounded-md flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </div>

                    <h3 class="text-base font-semibold text-slate-800 mt-4 group-hover:text-red-700">
                        Logout
                    </h3>

                    <p class="text-sm text-slate-500 mt-2">
                        Securely sign out of your student account.
                    </p>

                    <span class="text-sm text-red-600 font-medium inline-block mt-4">
                        Sign out &rarr;
                    </span>

                </a>

            </div>

        </section>

        <div class="mt-8 bg-blue-50 border border-blue-100 rounded-lg p-5">
            <h3 class="font-semibold text-blue-900">A message for every trainee</h3>
            <p class="text-sm text-blue-800 mt-2 leading-relaxed">
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
