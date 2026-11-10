
<?php
include('conn.php');
session_start();

$errors = [];
$registeredId = null;

$firstname = '';
$lastname = '';
$gender = '';
$tradeId = '';
$trades = [];

$tradeQuery = mysqli_query(
    $conn,
    "SELECT Trade_id, Trade_name FROM trades ORDER BY Trade_name ASC"
);

if (!$tradeQuery) {
    error_log("Trade loading failed: " . mysqli_error($conn));
    $errors[] = "Unable to load available trades. Please try again later.";
} else {
    while ($trade = mysqli_fetch_assoc($tradeQuery)) {
        $trades[] = $trade;
    }
}

if (empty($_SESSION['registration_token'])) {
    $_SESSION['registration_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname = trim($_POST['firstname'] ?? '');
    $lastname = trim($_POST['lastname'] ?? '');
    $gender = $_POST['gender'] ?? '';
    $tradeId = $_POST['trade_id'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $token = $_POST['registration_token'] ?? '';

    if (!hash_equals($_SESSION['registration_token'], $token)) {
        $errors[] = "Invalid registration request. Please refresh the page.";
    }

    if ($firstname === '' || $lastname === '') {
        $errors[] = "First name and last name are required.";
    }

    if (!in_array($gender, ['male', 'female'], true)) {
        $errors[] = "Please select a valid gender.";
    }

    if (!ctype_digit((string)$tradeId) || (int)$tradeId < 1) {
        $errors[] = "Please select a valid trade.";
    }

    if (strlen($password) < 8) {
        $errors[] = "Your password must contain at least 8 characters.";
    }

    if ($password !== $confirmPassword) {
        $errors[] = "The passwords do not match.";
    }

    if (empty($errors)) {
        $checkTrade = mysqli_prepare(
            $conn,
            "SELECT Trade_id FROM trades WHERE Trade_id = ?"
        );

        if (!$checkTrade) {
            error_log("Trade validation prepare failed: " . mysqli_error($conn));
            $errors[] = "Unable to verify your selected trade.";
        } else {
            $tradeIdInt = (int)$tradeId;
            mysqli_stmt_bind_param($checkTrade, "i", $tradeIdInt);
            mysqli_stmt_execute($checkTrade);
            mysqli_stmt_store_result($checkTrade);

            $validTrade = mysqli_stmt_num_rows($checkTrade) === 1;

            mysqli_stmt_close($checkTrade);

            if (!$validTrade) {
                $errors[] = "The selected trade does not exist.";
            }
        }
    }

    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO trainees
                (Firstname, lastname, Gender, Trade_id, password)
                VALUES (?, ?, ?, ?, ?)";

        $stmt = mysqli_prepare($conn, $sql);

        if (!$stmt) {
            error_log("Registration prepare failed: " . mysqli_error($conn));
            $errors[] = "Registration could not be completed. Please try again.";
        } else {
            $tradeIdInt = (int)$tradeId;

            mysqli_stmt_bind_param(
                $stmt,
                "sssis",
                $firstname,
                $lastname,
                $gender,
                $tradeIdInt,
                $hashedPassword
            );

            if (mysqli_stmt_execute($stmt)) {
                $registeredId = mysqli_insert_id($conn);
                $_SESSION['registration_token'] = bin2hex(random_bytes(32));
            } else {
                error_log("Student registration failed: " . mysqli_stmt_error($stmt));
                $errors[] = "Registration failed. Please try again.";
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
    <title>Student Registration | GIKONKO TSS</title>
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
            align-items: center;
            justify-content: center;
            padding: 40px 18px;
            background: #f1f5f9;
            font-family: Arial, Helvetica, sans-serif;
            color: #1e293b;
        }

        .registration-wrapper {
            width: 100%;
            max-width: 620px;
        }

        .brand-header {
            text-align: center;
            margin-bottom: 28px;
        }

        .brand-icon {
            width: 66px;
            height: 66px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            background: #1d4ed8;
            border-radius: 15px;
            box-shadow: 0 5px 12px rgba(29, 78, 216, 0.15);
        }

        .brand-icon svg {
            width: 36px;
            height: 36px;
            color: white;
        }

        .brand-header h1 {
            font-size: 25px;
            font-weight: 750;
            letter-spacing: -0.5px;
            color: #1e293b;
        }

        .brand-header p {
            margin-top: 7px;
            font-size: 14px;
            color: #64748b;
        }

        .registration-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 13px;
            padding: 34px;
            box-shadow: 0 8px 25px rgba(15, 23, 42, 0.045);
        }

        .form-heading {
            margin-bottom: 26px;
        }

        .form-heading .eyebrow {
            display: inline-block;
            color: #2563eb;
            font-size: 11px;
            font-weight: 750;
            letter-spacing: 1.3px;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        .form-heading h2 {
            font-size: 25px;
            font-weight: 750;
            letter-spacing: -0.6px;
            color: #1e293b;
        }

        .form-heading p {
            color: #64748b;
            font-size: 13px;
            line-height: 1.6;
            margin-top: 9px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-grid .form-group {
            margin-bottom: 0;
        }

        label {
            display: block;
            margin-bottom: 9px;
            color: #334155;
            font-size: 13px;
            font-weight: 650;
        }

        input,
        select {
            width: 100%;
            height: 46px;
            padding: 0 13px;
            border: 1px solid #cbd5e1;
            border-radius: 7px;
            outline: none;
            background: #ffffff;
            color: #1e293b;
            font-family: inherit;
            font-size: 13px;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        input::placeholder {
            color: #94a3b8;
        }

        input:focus,
        select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12);
        }

        select {
            cursor: pointer;
        }

        .error-box {
            padding: 15px 17px;
            margin-bottom: 23px;
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 8px;
            color: #b91c1c;
            font-size: 13px;
            line-height: 1.7;
        }

        .error-box ul {
            padding-left: 19px;
        }

        .no-trades {
            margin-top: 9px;
            color: #dc2626;
            font-size: 12px;
        }

        .submit-button {
            width: 100%;
            height: 48px;
            border: none;
            border-radius: 7px;
            background: #1d4ed8;
            color: white;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.2s, transform 0.2s;
        }

        .submit-button:hover:not(:disabled) {
            background: #1e40af;
            transform: translateY(-1px);
        }

        .submit-button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .form-links {
            margin-top: 25px;
            padding-top: 21px;
            border-top: 1px solid #e2e8f0;
            text-align: center;
        }

        .form-links p {
            color: #64748b;
            font-size: 13px;
        }

        .form-links a {
            color: #1d4ed8;
            font-weight: 650;
            text-decoration: none;
        }

        .form-links a:hover {
            text-decoration: underline;
        }

        .admin-link {
            display: inline-block;
            margin-top: 17px;
            color: #94a3b8 !important;
            font-size: 12px;
            font-weight: 500 !important;
        }

        .admin-link:hover {
            color: #1d4ed8 !important;
        }

        /* Registration success */

        .success-container {
            text-align: center;
        }

        .success-icon {
            width: 66px;
            height: 66px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: #ecfdf5;
            color: #059669;
        }

        .success-icon svg {
            width: 34px;
            height: 34px;
        }

        .success-container h2 {
            margin-top: 21px;
            font-size: 25px;
            color: #1e293b;
        }

        .success-container > p {
            margin-top: 10px;
            font-size: 13px;
            line-height: 1.6;
            color: #64748b;
        }

        .trainee-code-box {
            margin-top: 25px;
            padding: 25px 18px;
            border: 1px solid #bfdbfe;
            border-radius: 9px;
            background: #eff6ff;
        }

        .trainee-code-box p:first-child {
            font-size: 13px;
            color: #475569;
        }

        .trainee-code {
            margin-top: 9px;
            font-size: 34px;
            font-weight: 800;
            letter-spacing: 1px;
            color: #1e40af;
        }

        .trainee-code-box p:last-child {
            margin-top: 12px;
            font-size: 12px;
            color: #64748b;
            line-height: 1.5;
        }

        .login-button {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            min-height: 48px;
            margin-top: 24px;
            border-radius: 7px;
            background: #1d4ed8;
            color: white;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
            transition: background 0.2s;
        }

        .login-button:hover {
            background: #1e40af;
        }

        @media (max-width: 540px) {
            body {
                padding: 25px 14px;
            }

            .registration-card {
                padding: 25px 19px;
            }

            .form-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .form-heading h2 {
                font-size: 22px;
            }

            .brand-header h1 {
                font-size: 23px;
            }
        }
    </style>
</head>

<body>

    <main class="registration-wrapper">

        <header class="brand-header">
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
        </header>

        <section class="registration-card">

            <?php if ($registeredId !== null): ?>

                <div class="success-container">

                    <div class="success-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M5 13l4 4L19 7"
                            />
                        </svg>
                    </div>

                    <h2>Registration Successful!</h2>

                    <p>
                        Your student account has been created successfully.
                    </p>

                    <div class="trainee-code-box">
                        <p>Your Trainee Code</p>

                        <div class="trainee-code">
                            #<?php echo e($registeredId); ?>
                        </div>

                        <p>
                            Keep this code safe. You will need it to log in.
                        </p>
                    </div>

                    <a href="loginStudent.php" class="login-button">
                        Continue to Student Login
                    </a>

                </div>

            <?php else: ?>

                <div class="form-heading">
                    <span class="eyebrow">Student Account</span>

                    <h2>Create Your Account</h2>

                    <p>
                        Enter your details to register as a GIKONKO TSS trainee.
                    </p>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="error-box">
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo e($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">

                    <input
                        type="hidden"
                        name="registration_token"
                        value="<?php echo e($_SESSION['registration_token']); ?>"
                    >

                    <div class="form-grid">

                        <div class="form-group">
                            <label for="firstname">First Name</label>

                            <input
                                type="text"
                                id="firstname"
                                name="firstname"
                                required
                                maxlength="100"
                                value="<?php echo e($firstname); ?>"
                                placeholder="Enter first name"
                            >
                        </div>

                        <div class="form-group">
                            <label for="lastname">Last Name</label>

                            <input
                                type="text"
                                id="lastname"
                                name="lastname"
                                required
                                maxlength="100"
                                value="<?php echo e($lastname); ?>"
                                placeholder="Enter last name"
                            >
                        </div>

                    </div>

                    <div class="form-group">
                        <label for="gender">Gender</label>

                        <select id="gender" name="gender" required>
                            <option value="">Select gender</option>

                            <option
                                value="male"
                                <?php echo $gender === 'male' ? 'selected' : ''; ?>
                            >
                                Male
                            </option>

                            <option
                                value="female"
                                <?php echo $gender === 'female' ? 'selected' : ''; ?>
                            >
                                Female
                            </option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="trade_id">Select Trade</label>

                        <select id="trade_id" name="trade_id" required>
                            <option value="">Choose your trade</option>

                            <?php foreach ($trades as $trade): ?>
                                <option
                                    value="<?php echo e($trade['Trade_id']); ?>"
                                    <?php echo (string)$tradeId === (string)$trade['Trade_id'] ? 'selected' : ''; ?>
                                >
                                    <?php echo e($trade['Trade_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <?php if (empty($trades)): ?>
                            <p class="no-trades">
                                No trades are currently available for registration.
                            </p>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="password">Create Password</label>

                        <input
                            type="password"
                            id="password"
                            name="password"
                            required
                            minlength="8"
                            autocomplete="new-password"
                            placeholder="At least 8 characters"
                        >
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>

                        <input
                            type="password"
                            id="confirm_password"
                            name="confirm_password"
                            required
                            minlength="8"
                            autocomplete="new-password"
                            placeholder="Enter password again"
                        >
                    </div>

                    <button
                        type="submit"
                        class="submit-button"
                        <?php echo empty($trades) ? 'disabled' : ''; ?>
                    >
                        Create Student Account
                    </button>

                </form>

                <div class="form-links">
                    <p>
                        Already have an account?
                        <a href="loginStudent.php">Student Login</a>
                    </p>

                    <a href="login.php" class="admin-link">
                        Return to Admin Login
                    </a>
                </div>

            <?php endif; ?>

        </section>

    </main>

</body>
</html>
