
<?php
include("conn.php");
session_start();

$error = "";

if (isset($_SESSION['Firstname'], $_SESSION['Trainee_Id'])) {
    header("Location:userPage.php");
    exit();
}

if (isset($_POST['login'])) {

    $Trainee_id = trim($_POST['Trainee_Id'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($Trainee_id === '' || $password === '') {
        $error = "Please enter your trainee code and password.";
    } elseif (!ctype_digit($Trainee_id)) {
        $error = "Please enter a valid trainee code.";
    } else {

        $sql = "SELECT Trainee_Id, Firstname, lastname, Gender, Trade_id, password
                FROM trainees
                WHERE Trainee_Id = ?
                LIMIT 1";

        $stmt = mysqli_prepare($conn, $sql);

        if (!$stmt) {
            error_log("Student login prepare failed: " . mysqli_error($conn));
            $error = "Unable to process your login. Please try again.";
        } else {

            $traineeIdInt = (int)$Trainee_id;

            mysqli_stmt_bind_param($stmt, "i", $traineeIdInt);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);

            if (mysqli_stmt_num_rows($stmt) === 1) {

                mysqli_stmt_bind_result(
                    $stmt,
                    $dbTraineeId,
                    $dbFirstname,
                    $dbLastname,
                    $dbGender,
                    $dbTradeId,
                    $hashedPassword
                );

                mysqli_stmt_fetch($stmt);

                if (!empty($hashedPassword) && password_verify($password, $hashedPassword)) {

                    session_regenerate_id(true);

                    $_SESSION['Trainee_Id'] = $dbTraineeId;
                    $_SESSION['Firstname'] = $dbFirstname;
                    $_SESSION['lastname'] = $dbLastname;
                    $_SESSION['Gender'] = $dbGender;
                    $_SESSION['Trade_Id'] = $dbTradeId;

                    if (password_needs_rehash($hashedPassword, PASSWORD_DEFAULT)) {
                        $newHash = password_hash($password, PASSWORD_DEFAULT);

                        $updateStmt = mysqli_prepare(
                            $conn,
                            "UPDATE trainees SET password = ? WHERE Trainee_Id = ?"
                        );

                        if ($updateStmt) {
                            mysqli_stmt_bind_param($updateStmt, "si", $newHash, $dbTraineeId);
                            mysqli_stmt_execute($updateStmt);
                            mysqli_stmt_close($updateStmt);
                        }
                    }

                    mysqli_stmt_close($stmt);

                    header("Location:userPage.php");
                    exit();

                } else {
                    $error = "Invalid trainee code or password.";
                }

            } else {
                $error = "Invalid trainee code or password.";
            }

            mysqli_stmt_close($stmt);
        }
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Login | GIKONKO TSS</title>
    <link rel="icon" type="image/png" href="gikonko.png">
    <link href="output.css" rel="stylesheet">
</head>

<body class="min-h-screen bg-slate-100 flex items-center justify-center px-4 py-10">

    <div class="w-full max-w-md">

        <div class="text-center mb-8">

            <div class="w-16 h-16 bg-blue-700 rounded-xl flex items-center justify-center mx-auto shadow-sm">
                <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M12 14l9-5-9-5-9 5 9 5zm0 0v7m-7-10v5c0 1.5 3.1 3 7 3s7-1.5 7-3v-5"/>
                </svg>
            </div>

            <h1 class="text-2xl font-bold text-slate-800 mt-4">
                GIKONKO TSS
            </h1>

            <p class="text-sm text-slate-500 mt-1">
                Student Management System
            </p>

        </div>

        <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-7 sm:p-8">

            <div class="mb-7">

                <p class="text-sm font-semibold text-blue-700 uppercase tracking-wider">
                    Student Portal
                </p>

                <h2 class="text-2xl font-bold text-slate-800 mt-2">
                    Welcome Back
                </h2>

                <p class="text-sm text-slate-500 mt-2">
                    Sign in to access your academic information and results.
                </p>

            </div>

            <?php if ($error !== ''): ?>

                <div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-md px-4 py-3 mb-5">
                    <?php echo e($error); ?>
                </div>

            <?php endif; ?>

            <form method="POST" action="">

                <div class="mb-5">

                    <label for="Trainee_Id" class="block text-sm font-semibold text-slate-700 mb-2">
                        Trainee Code
                    </label>

                    <input
                        type="text"
                        id="Trainee_Id"
                        name="Trainee_Id"
                        required
                        inputmode="numeric"
                        pattern="[0-9]+"
                        autocomplete="username"
                        value="<?php echo e($_POST['Trainee_Id'] ?? ''); ?>"
                        placeholder="Enter your trainee code"
                        class="w-full px-4 py-3 border border-slate-300 rounded-md text-sm text-slate-800 outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >

                </div>

                <div class="mb-6">

                    <label for="password" class="block text-sm font-semibold text-slate-700 mb-2">
                        Password
                    </label>

                    <div class="relative">

                        <input
                            type="password"
                            id="password"
                            name="password"
                            required
                            autocomplete="current-password"
                            placeholder="Enter your password"
                            class="w-full px-4 py-3 pr-16 border border-slate-300 rounded-md text-sm text-slate-800 outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >

                        <button
                            type="button"
                            id="togglePassword"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-semibold text-blue-700 hover:text-blue-900"
                        >
                            Show
                        </button>

                    </div>

                </div>

                <button
                    type="submit"
                    name="login"
                    class="w-full bg-blue-700 text-white font-semibold py-3 rounded-md hover:bg-blue-800 transition"
                >
                    Sign In
                </button>

            </form>

            <div class="relative my-6">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-slate-200"></div>
                </div>

                <div class="relative flex justify-center">
                    <span class="bg-white px-3 text-xs text-slate-400">
                        OR
                    </span>
                </div>
            </div>

            <div class="text-center space-y-3">

                <p class="text-sm text-slate-500">
                    Don't have an account?
                    <a href="register.php" class="font-semibold text-blue-700 hover:underline">
                        Register here
                    </a>
                </p>

                <a href="login.php" class="block text-sm text-slate-500 hover:text-blue-700 transition">
                    ← Back to Admin Login
                </a>

            </div>

        </div>

        <p class="text-center text-xs text-slate-400 mt-6">
            &copy; <?php echo date("Y"); ?> GIKONKO TSS. All rights reserved.
        </p>

    </div>

    <script>
        const passwordInput = document.getElementById("password");
        const togglePassword = document.getElementById("togglePassword");

        togglePassword.addEventListener("click", function () {
            const isPassword = passwordInput.type === "password";

            passwordInput.type = isPassword ? "text" : "password";
            this.textContent = isPassword ? "Hide" : "Show";
        });
    </script>

</body>
</html>
