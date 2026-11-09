
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
    <link href="output.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="gikonko.png">
</head>

<body class="min-h-screen bg-slate-50 flex flex-col">

    <header class="fixed top-0 left-0 right-0 z-50 bg-white border-b border-slate-200 shadow-sm">
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="h-20 flex items-center justify-between">

                <a href="userPage.php" class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-lg overflow-hidden border border-slate-200">
                        <img src="gikonko.png" alt="GIKONKO TSS Logo" class="w-full h-full object-cover">
                    </div>

                    <div>
                        <h1 class="text-lg font-bold text-slate-800">GIKONKO TSS</h1>
                        <p class="text-xs text-slate-500">Student Portal</p>
                    </div>
                </a>

                <div class="hidden sm:flex items-center gap-6">
                    <a href="userPage.php"
                       class="text-sm font-semibold text-blue-700 border-b-2 border-blue-700 py-2">
                        Dashboard
                    </a>

                    <a href="listOfUserMarks.php?Trainee_Id=<?php echo $traineeId; ?>"
                       class="text-sm font-medium text-slate-600 hover:text-blue-700 transition">
                        My Marks
                    </a>

                    <a href="userLogout.php"
                       class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-red-600 rounded-md hover:bg-red-700 transition">
                        <span>Logout</span>
                        <span aria-hidden="true">→</span>
                    </a>
                </div>

                <div class="sm:hidden">
                    <a href="userLogout.php"
                       class="px-3 py-2 text-sm font-semibold text-white bg-red-600 rounded-md hover:bg-red-700 transition">
                        Logout
                    </a>
                </div>

            </div>

            <div class="sm:hidden flex items-center gap-5 pb-3">
                <a href="userPage.php" class="text-sm font-semibold text-blue-700">
                    Dashboard
                </a>

                <a href="listOfUserMarks.php?Trainee_Id=<?php echo $traineeId; ?>"
                   class="text-sm font-medium text-slate-600 hover:text-blue-700">
                    My Marks
                </a>
            </div>
        </nav>
    </header>

    <main class="flex-grow pt-32 pb-12 px-4 sm:px-6 lg:px-8">

        <div class="max-w-7xl mx-auto">

            <section class="bg-white border border-slate-200 rounded-lg p-6 sm:p-10 shadow-sm">

                <p class="text-sm font-semibold uppercase tracking-wide text-blue-700">
                    Student Dashboard
                </p>

                <h2 class="text-2xl sm:text-3xl font-bold text-slate-800 mt-3">
                    Welcome back, <?php echo $firstname; ?>!
                </h2>

                <p class="text-slate-500 mt-3 max-w-2xl leading-relaxed">
                    Welcome to the GIKONKO TSS Student Portal. You can access your academic information
                    and check your assessment results whenever you need them.
                </p>

                <div class="mt-6 inline-flex items-center gap-2 px-4 py-2 bg-blue-50 border border-blue-100 rounded-md">
                    <span class="text-sm font-medium text-slate-600">Trainee Code:</span>
                    <span class="text-sm font-bold text-blue-700">
                        <?php echo $traineeId; ?>
                    </span>
                </div>

            </section>

            <section class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">

                <div class="bg-white border border-slate-200 rounded-lg p-6 shadow-sm">
                    <div class="w-11 h-11 flex items-center justify-center bg-blue-50 text-blue-700 rounded-md mb-5">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                             viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                            <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"/>
                        </svg>
                    </div>

                    <h3 class="text-lg font-bold text-slate-800">
                        View Your Marks
                    </h3>

                    <p class="text-sm text-slate-500 mt-2 leading-relaxed">
                        Check your formative assessments, summative assessments, total marks and competency status.
                    </p>

                    <a href="listOfUserMarks.php?Trainee_Id=<?php echo $traineeId; ?>"
                       class="inline-flex items-center gap-2 mt-5 text-sm font-semibold text-blue-700 hover:text-blue-900 transition">
                        View Results
                        <span aria-hidden="true">→</span>
                    </a>
                </div>

                <div class="bg-white border border-slate-200 rounded-lg p-6 shadow-sm">
                    <div class="w-11 h-11 flex items-center justify-center bg-emerald-50 text-emerald-700 rounded-md mb-5">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                             viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="8" r="4"/>
                            <path d="M5 21v-2a7 7 0 0 1 14 0v2"/>
                        </svg>
                    </div>

                    <h3 class="text-lg font-bold text-slate-800">
                        Your Student Account
                    </h3>

                    <p class="text-sm text-slate-500 mt-2 leading-relaxed">
                        You are signed in to your student portal. Your academic records are associated with your trainee code.
                    </p>

                    <span class="inline-flex items-center gap-2 mt-5 text-sm font-semibold text-emerald-700">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        Account Active
                    </span>
                </div>

            </section>

        </div>

    </main>

    <footer class="bg-white border-t border-slate-200 py-5 mt-auto">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <p class="text-sm text-slate-500">
                &copy; <?php echo date("Y"); ?> GIKONKO TSS. All rights reserved.
            </p>
        </div>
    </footer>

</body>
</html>
