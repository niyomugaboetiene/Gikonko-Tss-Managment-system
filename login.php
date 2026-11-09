
<?php
include('conn.php');
session_start();

$loginErrorMessage = "";
$error = "";

if (isset($_SESSION['login_error'])) {
    $loginErrorMessage = $_SESSION['login_error'];
    unset($_SESSION['login_error']);
}

if (isset($_POST['login'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = "Please enter your username and password.";
    } else {
        $sql = "SELECT username, password FROM users WHERE username = ?";
        $stmt = mysqli_prepare($conn, $sql);

        if (!$stmt) {
            error_log("Admin login prepare failed: " . mysqli_error($conn));
            $error = "Unable to process your login. Please try again.";
        } else {
            mysqli_stmt_bind_param($stmt, "s", $username);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);

            if (mysqli_stmt_num_rows($stmt) === 1) {
                mysqli_stmt_bind_result($stmt, $dbUsername, $hashedPassword);
                mysqli_stmt_fetch($stmt);

                if (password_verify($password, $hashedPassword)) {
                    session_regenerate_id(true);
                    $_SESSION['username'] = $dbUsername;

                    mysqli_stmt_close($stmt);
                    header("Location: greetingUser.php");
                    exit();
                }
            }

            mysqli_stmt_close($stmt);
            $error = "Invalid username or password.";
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
    <title>Admin Login | GIKONKO TSS</title>
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
                    Administration
                </p>

                <h2 class="text-2xl font-bold text-slate-800 mt-2">
                    Welcome back
                </h2>

                <p class="text-sm text-slate-500 mt-2">
                    Sign in to access the administration dashboard.
                </p>
            </div>

            <?php if ($loginErrorMessage !== ''): ?>
                <div class="bg-amber-50 border border-amber-200 text-amber-800 text-sm rounded-md px-4 py-3 mb-5">
                    <?php echo e($loginErrorMessage); ?>
                </div>
            <?php endif; ?>

            <?php if ($error !== ''): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-md px-4 py-3 mb-5">
                    <?php echo e($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">

                <div class="mb-5">
                    <label for="username" class="block text-sm font-semibold text-slate-700 mb-2">
                        Username
                    </label>

                    <input
                        type="text"
                        id="username"
                        name="username"
                        required
                        autocomplete="username"
                        value="<?php echo e($_POST['username'] ?? ''); ?>"
                        placeholder="Enter your username"
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

                <a href="loginStudent.php"
                   class="block text-sm font-semibold text-blue-700 hover:underline">
                    Login as a Student
                </a>

                <p class="text-sm text-slate-500">
                    New student?
                    <a href="register.php" class="font-semibold text-blue-700 hover:underline">
                        Create an account
                    </a>
                </p>

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
