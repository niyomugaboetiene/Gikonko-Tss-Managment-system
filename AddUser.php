<?php
session_start();
include("conn.php");

if (!isset($_SESSION['username'])) {
    $_SESSION['login_error'] = "Please login to access this page";
    header("Location: index.php");
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
    <link rel="icon" type="image/png" href="gikonko.png">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            background: #f8fafc;
            color: #1e293b;
            font-family: Arial, Helvetica, sans-serif;
            line-height: 1.5;
        }

        a {
            text-decoration: none;
        }

        .page-container {
            width: 100%;
            max-width: 1280px;
            margin: 0 auto;
            padding: 35px 24px 55px;
        }

        .page-heading {
            margin-bottom: 30px;
        }

        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #64748b;
            font-size: 13px;
            margin-bottom: 16px;
        }

        .breadcrumb a {
            color: #64748b;
        }

        .breadcrumb a:hover {
            color: #1d4ed8;
        }

        .breadcrumb span:last-child {
            color: #334155;
            font-weight: 600;
        }

        .heading-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
        }

        .heading-content h1 {
            color: #0f172a;
            font-size: 29px;
            font-weight: 700;
            letter-spacing: -0.6px;
        }

        .heading-content p {
            color: #64748b;
            font-size: 14px;
            margin-top: 7px;
        }

        .form-wrapper {
            max-width: 800px;
        }

        .form-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 9px;
            box-shadow: 0 2px 6px rgba(15, 23, 42, 0.04);
            overflow: hidden;
        }

        .card-header {
            padding: 22px 25px;
            border-bottom: 1px solid #e2e8f0;
        }

        .card-header h2 {
            color: #0f172a;
            font-size: 18px;
            font-weight: 700;
        }

        .card-header p {
            color: #64748b;
            font-size: 13px;
            margin-top: 5px;
        }

        .form-content {
            padding: 25px;
        }

        .message {
            margin: 20px 25px 0;
            padding: 12px 15px;
            border-radius: 6px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
            font-size: 13px;
            font-weight: 500;
        }

        .success-message {
            color: #166534;
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
        }

        .error-message {
            color: #b91c1c;
            background: #fef2f2;
            border: 1px solid #fecaca;
        }

        .section-heading {
            margin-bottom: 20px;
        }

        .section-heading h3 {
            color: #0f172a;
            font-size: 14px;
            font-weight: 700;
        }

        .section-heading p {
            color: #64748b;
            font-size: 12px;
            margin-top: 4px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            color: #334155;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .required {
            color: #dc2626;
        }

        .form-control {
            width: 100%;
            height: 43px;
            padding: 10px 13px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            background: #ffffff;
            color: #334155;
            font-family: inherit;
            font-size: 13px;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-control::placeholder {
            color: #94a3b8;
        }

        .form-control:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.10);
        }

        .password-wrapper {
            position: relative;
        }

        .password-wrapper .form-control {
            padding-right: 75px;
        }

        .toggle-password {
            position: absolute;
            right: 13px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #1d4ed8;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
        }

        .toggle-password:hover {
            color: #1e40af;
        }

        .help-text {
            color: #64748b;
            font-size: 12px;
            margin-top: 7px;
        }

        .form-divider {
            border: none;
            border-top: 1px solid #f1f5f9;
            margin: 25px 0;
        }

        .information-box {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 16px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            margin-bottom: 25px;
        }

        .information-icon {
            width: 21px;
            height: 21px;
            min-width: 21px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #1d4ed8;
            background: #dbeafe;
            border-radius: 50%;
            font-size: 12px;
            font-weight: 700;
        }

        .information-box h3 {
            color: #334155;
            font-size: 13px;
            font-weight: 700;
        }

        .information-box p {
            color: #64748b;
            font-size: 12px;
            margin-top: 4px;
            line-height: 1.6;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }

        .btn {
            min-height: 41px;
            padding: 10px 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            font-family: inherit;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s, border-color 0.2s;
        }

        .btn-clear {
            background: #ffffff;
            color: #475569;
            border: 1px solid #cbd5e1;
        }

        .btn-clear:hover {
            background: #f1f5f9;
        }

        .btn-primary {
            background: #1d4ed8;
            color: #ffffff;
            border: 1px solid #1d4ed8;
        }

        .btn-primary:hover {
            background: #1e40af;
            border-color: #1e40af;
        }

        .bottom-note {
            color: #64748b;
            font-size: 13px;
            margin-top: 20px;
        }

        @media (max-width: 640px) {
            .page-container {
                padding: 25px 15px 40px;
            }

            .heading-content {
                align-items: flex-start;
            }

            .heading-content h1 {
                font-size: 24px;
            }

            .card-header,
            .form-content {
                padding: 19px;
            }

            .message {
                margin-left: 19px;
                margin-right: 19px;
            }

            .form-actions {
                flex-direction: column-reverse;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
</head>

<body>

    <header>
        <?php include("Dashboard.php"); ?>
    </header>

    <main class="page-container">

        <div class="page-heading">

            <div class="breadcrumb">
                <span>User Management</span>
                <span>/</span>
                <span>Add User</span>
            </div>

            <div class="heading-content">
                <div>
                    <h1>Add New User</h1>
                    <p>Create a new account to access the management system.</p>
                </div>
            </div>

        </div>

        <div class="form-wrapper">

            <div class="form-card">

                <div class="card-header">
                    <h2>Account Information</h2>
                    <p>Enter the username and password for the new account.</p>
                </div>

                <?php if (!empty($success)): ?>
                    <div class="message success-message">
                        <span>✓</span>
                        <p><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                <?php endif; ?>

                <?php if (!empty($error)): ?>
                    <div class="message error-message">
                        <span>!</span>
                        <p><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" class="form-content">

                    <div class="section-heading">
                        <h3>Login Credentials</h3>
                        <p>Set up the credentials that will be used to log in.</p>
                    </div>

                    <div class="form-group">
                        <label for="username">
                            Username <span class="required">*</span>
                        </label>

                        <input
                            type="text"
                            id="username"
                            name="username"
                            value="<?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>"
                            placeholder="Enter username"
                            maxlength="100"
                            autocomplete="username"
                            required
                            class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="password">
                            Password <span class="required">*</span>
                        </label>

                        <div class="password-wrapper">
                            <input
                                type="password"
                                id="password"
                                name="password"
                                placeholder="Create a secure password"
                                minlength="8"
                                autocomplete="new-password"
                                required
                                class="form-control">

                            <button
                                type="button"
                                id="togglePassword"
                                class="toggle-password">
                                Show
                            </button>
                        </div>

                        <p class="help-text">
                            Use at least 8 characters for your password.
                        </p>
                    </div>

                    <hr class="form-divider">

                    <div class="information-box">
                        <span class="information-icon">i</span>

                        <div>
                            <h3>Account Security</h3>
                            <p>
                                Passwords are securely hashed before being
                                stored in the database. Never share account
                                credentials with unauthorized people.
                            </p>
                        </div>
                    </div>

                    <div class="form-actions">

                        <button type="reset" class="btn btn-clear">
                            Clear
                        </button>

                        <button
                            type="submit"
                            name="add_user"
                            class="btn btn-primary">
                            Create Account
                        </button>

                    </div>

                </form>

            </div>

            <div class="bottom-note">
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
