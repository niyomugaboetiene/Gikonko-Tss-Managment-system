
<?php
session_start();
include("conn.php");

if (!isset($_SESSION['username'])) {
    $_SESSION['login_error'] = "Please login to access this page";
    header("Location: login.php");
    exit();
}

$success = "";
$error = "";
$username = "";

if (isset($_POST['add_user'])) {

    $username = trim($_POST['username'] ?? "");
    $password = $_POST['password'] ?? "";

    if ($username === "" || $password === "") {
        $error = "Both fields are required.";
    } elseif (strlen($password) < 8) {
        $error = "Password must contain at least 8 characters.";
    } else {

        $checkUser = mysqli_prepare(
            $conn,
            "SELECT username FROM users WHERE username = ?"
        );

        mysqli_stmt_bind_param($checkUser, "s", $username);
        mysqli_stmt_execute($checkUser);
        mysqli_stmt_store_result($checkUser);

        if (mysqli_stmt_num_rows($checkUser) > 0) {

            $error = "This username is already taken.";

        } else {

            $hashedPassword = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $insertUser = mysqli_prepare(
                $conn,
                "INSERT INTO users (username, `password`) VALUES (?, ?)"
            );

            mysqli_stmt_bind_param(
                $insertUser,
                "ss",
                $username,
                $hashedPassword
            );

            if (mysqli_stmt_execute($insertUser)) {
                $success = "User account created successfully!";
                $username = "";
            } else {
                $error = "Unable to create user account. Please try again.";
            }

            mysqli_stmt_close($insertUser);
        }

        mysqli_stmt_close($checkUser);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Add User | GIKONKO TSS</title>

    <link href="output.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="gikonko.png">
</head>

<body class="min-h-screen bg-slate-50 text-slate-800">

    <header class="sticky top-0 z-30 bg-white border-b border-slate-200">
        <?php include("Dashboard.php"); ?>
    </header>

    <main class="max-w-7xl mx-auto px-5 py-8 md:px-8">

        <div class="mb-8">

            <div class="flex items-center gap-2 text-sm text-slate-500 mb-3">

                <span>User Management</span>

                <span>/</span>

                <span class="text-slate-800 font-medium">
                    Add User
                </span>

            </div>

            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

                <div>

                    <h1 class="text-2xl md:text-3xl font-bold tracking-tight text-slate-900">
                        Add New User
                    </h1>

                    <p class="text-sm text-slate-500 mt-2">
                        Create a new account to access the management system.
                    </p>

                </div>

            </div>

        </div>


        <div class="max-w-3xl">

            <div class="bg-white border border-slate-200 rounded-lg shadow-sm">

                <div class="px-6 py-5 border-b border-slate-200">

                    <h2 class="text-lg font-semibold text-slate-900">
                        Account Information
                    </h2>

                    <p class="text-sm text-slate-500 mt-1">
                        Enter the username and password for the new account.
                    </p>

                </div>


                <?php if (!empty($success)): ?>

                    <div class="mx-6 mt-5 flex items-start gap-3 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-md">

                        <span class="font-bold">✓</span>

                        <p class="text-sm font-medium">
                            <?php echo htmlspecialchars($success); ?>
                        </p>

                    </div>

                <?php endif; ?>


                <?php if (!empty($error)): ?>

                    <div class="mx-6 mt-5 flex items-start gap-3 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-md">

                        <span class="font-bold">!</span>

                        <p class="text-sm font-medium">
                            <?php echo htmlspecialchars($error); ?>
                        </p>

                    </div>

                <?php endif; ?>


                <form method="POST" action="" class="p-6 space-y-6">

                    <div>

                        <h3 class="text-sm font-semibold text-slate-900 mb-1">
                            Login Credentials
                        </h3>

                        <p class="text-xs text-slate-500 mb-5">
                            Set up the credentials that will be used to log in.
                        </p>


                        <div class="mb-5">

                            <label for="username"
                                   class="block text-sm font-medium text-slate-700 mb-2">

                                Username
                                <span class="text-red-500">*</span>

                            </label>

                            <input
                                type="text"
                                id="username"
                                name="username"
                                value="<?php echo htmlspecialchars($username); ?>"
                                placeholder="Enter username"
                                maxlength="100"
                                autocomplete="username"
                                required
                                class="w-full px-3 py-2.5 border border-slate-300 rounded-md bg-white text-sm text-slate-700 outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition">

                        </div>


                        <div>

                            <label for="password"
                                   class="block text-sm font-medium text-slate-700 mb-2">

                                Password
                                <span class="text-red-500">*</span>

                            </label>

                            <div class="relative">

                                <input
                                    type="password"
                                    id="password"
                                    name="password"
                                    placeholder="Create a secure password"
                                    minlength="8"
                                    autocomplete="new-password"
                                    required
                                    class="w-full px-3 py-2.5 pr-20 border border-slate-300 rounded-md bg-white text-sm text-slate-700 outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition">

                                <button
                                    type="button"
                                    id="togglePassword"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-semibold text-blue-700 hover:text-blue-900">

                                    Show

                                </button>

                            </div>

                            <p class="text-xs text-slate-500 mt-2">
                                Use at least 8 characters for your password.
                            </p>

                        </div>

                    </div>


                    <div class="border-t border-slate-100"></div>


                    <div class="bg-slate-50 border border-slate-200 rounded-md p-4">

                        <div class="flex items-start gap-3">

                            <span class="text-blue-700 font-semibold">i</span>

                            <div>

                                <h3 class="text-sm font-semibold text-slate-800">
                                    Account Security
                                </h3>

                                <p class="text-xs text-slate-500 mt-1">
                                    Passwords are securely hashed before being
                                    stored in the database. Never share account
                                    credentials with unauthorized people.
                                </p>

                            </div>

                        </div>

                    </div>


                    <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 pt-2">

                        <button
                            type="reset"
                            class="px-5 py-2.5 border border-slate-300 rounded-md text-sm font-semibold text-slate-700 hover:bg-slate-100 transition">

                            Clear

                        </button>

                        <button
                            type="submit"
                            name="add_user"
                            class="px-6 py-2.5 bg-blue-700 text-white rounded-md text-sm font-semibold hover:bg-blue-800 focus:ring-4 focus:ring-blue-100 transition">

                            Create Account

                        </button>

                    </div>

                </form>

            </div>


            <div class="mt-5 text-sm text-slate-500">
                Only authorized personnel should create and manage system accounts.
            </div>

        </div>

    </main>

    <?php include("footer.php"); ?>

    <script>

        const passwordInput = document.getElementById("password");

        const togglePassword = document.getElementById("togglePassword");

        togglePassword.addEventListener("click", function () {

            if (passwordInput.type === "password") {

                passwordInput.type = "text";
                togglePassword.textContent = "Hide";

            } else {

                passwordInput.type = "password";
                togglePassword.textContent = "Show";

            }

        });

    </script>

</body>
</html>
