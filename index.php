
<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/conn.php';

$loginErrorMessage = "";
$error = "";

if (isset($_SESSION['login_error'])) {
    $loginErrorMessage = $_SESSION['login_error'];
    unset($_SESSION['login_error']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {

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

                    mysqli_stmt_close($stmt);

                    session_regenerate_id(true);

                    $_SESSION['username'] = $dbUsername;

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
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Login | GIKONKO TSS</title>

    <link rel="icon" type="image/png" href="gikonko.png">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            font-family: Arial, Helvetica, sans-serif;
            background: #f1f5f9;
            color: #1e293b;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 18px;
        }

        .login-wrapper {
            width: 100%;
            max-width: 430px;
        }

        .brand-section {
            text-align: center;
            margin-bottom: 30px;
        }

        .brand-icon {
            width: 66px;
            height: 66px;
            margin: 0 auto 16px;
            background: #1d4ed8;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 5px 14px rgba(29, 78, 216, 0.18);
        }

        .brand-icon svg {
            width: 36px;
            height: 36px;
            color: #ffffff;
        }

        .brand-section h1 {
            font-size: 25px;
            font-weight: 750;
            letter-spacing: -0.5px;
            color: #172554;
        }

        .brand-section p {
            margin-top: 7px;
            font-size: 13px;
            color: #64748b;
        }

        .login-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 13px;
            padding: 34px;
            box-shadow: 0 8px 30px rgba(15, 23, 42, 0.06);
        }

        .form-heading {
            margin-bottom: 26px;
        }

        .form-heading .category {
            color: #1d4ed8;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 1.3px;
            text-transform: uppercase;
        }

        .form-heading h2 {
            margin-top: 10px;
            font-size: 26px;
            font-weight: 750;
            color: #172554;
            letter-spacing: -0.6px;
        }

        .form-heading p {
            margin-top: 9px;
            color: #64748b;
            font-size: 13px;
            line-height: 1.6;
        }

        .alert {
            padding: 13px 15px;
            border-radius: 7px;
            font-size: 13px;
            line-height: 1.5;
            margin-bottom: 19px;
        }

        .alert-warning {
            color: #92400e;
            background: #fffbeb;
            border: 1px solid #fde68a;
        }

        .alert-error {
            color: #b91c1c;
            background: #fef2f2;
            border: 1px solid #fecaca;
        }

        .form-group {
            margin-bottom: 21px;
        }

        .form-group label {
            display: block;
            margin-bottom: 9px;
            font-size: 13px;
            font-weight: 650;
            color: #334155;
        }

        .form-control {
            width: 100%;
            height: 47px;
            padding: 0 14px;
            border: 1px solid #cbd5e1;
            border-radius: 7px;
            outline: none;
            font-size: 13px;
            color: #1e293b;
            background: #ffffff;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-control::placeholder {
            color: #94a3b8;
        }

        .form-control:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        }

        .password-wrapper {
            position: relative;
        }

        .password-wrapper .form-control {
            padding-right: 65px;
        }

        .toggle-password {
            position: absolute;
            right: 13px;
            top: 50%;
            transform: translateY(-50%);
            border: none;
            background: transparent;
            color: #1d4ed8;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            padding: 5px;
        }

        .toggle-password:hover {
            color: #1e40af;
        }

        .submit-btn {
            width: 100%;
            height: 48px;
            border: none;
            border-radius: 7px;
            background: #1d4ed8;
            color: #ffffff;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.2s, transform 0.2s;
        }

        .submit-btn:hover {
            background: #1e40af;
        }

        .submit-btn:active {
            transform: scale(0.99);
        }

        .divider {
            display: flex;
            align-items: center;
            gap: 13px;
            margin: 25px 0;
            color: #94a3b8;
            font-size: 11px;
            font-weight: 600;
        }

        .divider::before,
        .divider::after {
            content: "";
            flex: 1;
            height: 1px;
            background: #e2e8f0;
        }

        .alternative-links {
            text-align: center;
        }

        .alternative-links a {
            color: #1d4ed8;
            text-decoration: none;
            font-size: 13px;
            font-weight: 650;
        }

        .alternative-links a:hover {
            text-decoration: underline;
        }

        .alternative-links p {
            margin-top: 15px;
            color: #64748b;
            font-size: 13px;
        }

        .footer-text {
            margin-top: 25px;
            text-align: center;
            color: #94a3b8;
            font-size: 11px;
        }

        @media (max-width: 480px) {
            body {
                padding: 25px 14px;
            }

            .login-card {
                padding: 25px 20px;
            }

            .form-heading h2 {
                font-size: 23px;
            }

            .brand-section h1 {
                font-size: 23px;
            }
        }
    </style>
</head>

<body>

    <main class="login-wrapper">

        <div class="brand-section">

            <div class="brand-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="1.8"
                        d="M12 14l9-5-9-5-9 5 9 5zm0 0v7m-7-10v5c0 1.5 3.1 3 7 3s7-1.5 7-3v-5"
                    />
                </svg>
            </div>

            <h1>GIKONKO TSS</h1>
            <p>Student Management System</p>

        </div>

        <section class="login-card">

            <div class="form-heading">

                <span class="category">Administration</span>

                <h2>Welcome back</h2>

                <p>
                    Sign in to access the administration dashboard.
                </p>

            </div>

            <?php if ($loginErrorMessage !== ''): ?>

                <div class="alert alert-warning">
                    <?php echo e($loginErrorMessage); ?>
                </div>

            <?php endif; ?>

            <?php if ($error !== ''): ?>

                <div class="alert alert-error">
                    <?php echo e($error); ?>
                </div>

            <?php endif; ?>

            <form method="POST" action="">

                <div class="form-group">

                    <label for="username">Username</label>

                    <input
                        type="text"
                        id="username"
                        name="username"
                        required
                        autocomplete="username"
                        value="<?php echo e($_POST['username'] ?? ''); ?>"
                        placeholder="Enter your username"
                        class="form-control"
                    >

                </div>

                <div class="form-group">

                    <label for="password">Password</label>

                    <div class="password-wrapper">

                        <input
                            type="password"
                            id="password"
                            name="password"
                            required
                            autocomplete="current-password"
                            placeholder="Enter your password"
                            class="form-control"
                        >

                        <button
                            type="button"
                            id="togglePassword"
                            class="toggle-password"
                        >
                            Show
                        </button>

                    </div>

                </div>

                <button
                    type="submit"
                    name="login"
                    class="submit-btn"
                >
                    Sign In
                </button>

            </form>

            <div class="divider">OR</div>

            <div class="alternative-links">

                <a href="loginStudent.php">
                    Login as a Student
                </a>

                <p>
                    New student?
                    <a href="register.php">Create an account</a>
                </p>

            </div>

        </section>

        <p class="footer-text">
            © <?php echo date("Y"); ?> GIKONKO TSS. All rights reserved.
        </p>

    </main>

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