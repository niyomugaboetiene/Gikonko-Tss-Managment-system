
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
$tradeQuery = mysqli_query($conn, "SELECT Trade_id, Trade_name FROM trades ORDER BY Trade_name ASC");

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
    <link href="output.css" rel="stylesheet">
</head>

<body class="min-h-screen bg-slate-100 flex items-center justify-center px-4 py-10">

    <div class="w-full max-w-xl">

        <div class="text-center mb-7">

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

        <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-6 sm:p-8">

            <?php if ($registeredId !== null): ?>

                <div class="text-center">

                    <div class="w-16 h-16 mx-auto rounded-full bg-emerald-50 text-emerald-700 flex items-center justify-center">
                        <svg class="w-9 h-9" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>

                    <h2 class="text-2xl font-bold text-slate-800 mt-5">
                        Registration Successful!
                    </h2>

                    <p class="text-sm text-slate-500 mt-2">
                        Your student account has been created successfully.
                    </p>

                    <div class="bg-blue-50 border border-blue-100 rounded-md p-5 mt-6">

                        <p class="text-sm text-slate-600">
                            Your Trainee Code
                        </p>

                        <p class="text-3xl font-bold text-blue-800 mt-2">
                            #<?php echo e($registeredId); ?>
                        </p>

                        <p class="text-xs text-slate-500 mt-3">
                            Keep this code safe. You will need it to log in.
                        </p>

                    </div>

                    <a href="loginStudent.php"
                       class="block w-full mt-6 bg-blue-700 text-white font-semibold py-3 rounded-md hover:bg-blue-800 transition">
                        Continue to Student Login
                    </a>

                </div>

            <?php else: ?>

                <div class="mb-7">

                    <p class="text-sm font-semibold text-blue-700 uppercase tracking-wider">
                        Student Account
                    </p>

                    <h2 class="text-2xl font-bold text-slate-800 mt-2">
                        Create Your Account
                    </h2>

                    <p class="text-sm text-slate-500 mt-2">
                        Enter your details to register as a GIKONKO TSS trainee.
                    </p>

                </div>

                <?php if (!empty($errors)): ?>

                    <div class="bg-red-50 border border-red-200 text-red-700 rounded-md p-4 mb-6 text-sm">

                        <ul class="list-disc pl-5 space-y-1">
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

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-5">

                        <div>
                            <label for="firstname" class="block text-sm font-semibold text-slate-700 mb-2">
                                First Name
                            </label>

                            <input
                                type="text"
                                id="firstname"
                                name="firstname"
                                required
                                maxlength="100"
                                value="<?php echo e($firstname); ?>"
                                placeholder="Enter first name"
                                class="w-full px-4 py-3 border border-slate-300 rounded-md text-sm outline-none focus:ring-2 focus:ring-blue-500"
                            >
                        </div>

                        <div>
                            <label for="lastname" class="block text-sm font-semibold text-slate-700 mb-2">
                                Last Name
                            </label>

                            <input
                                type="text"
                                id="lastname"
                                name="lastname"
                                required
                                maxlength="100"
                                value="<?php echo e($lastname); ?>"
                                placeholder="Enter last name"
                                class="w-full px-4 py-3 border border-slate-300 rounded-md text-sm outline-none focus:ring-2 focus:ring-blue-500"
                            >
                        </div>

                    </div>

                    <div class="mb-5">

                        <label for="gender" class="block text-sm font-semibold text-slate-700 mb-2">
                            Gender
                        </label>

                        <select
                            id="gender"
                            name="gender"
                            required
                            class="w-full px-4 py-3 border border-slate-300 rounded-md text-sm bg-white outline-none focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="">Select gender</option>
                            <option value="male" <?php echo $gender === 'male' ? 'selected' : ''; ?>>Male</option>
                            <option value="female" <?php echo $gender === 'female' ? 'selected' : ''; ?>>Female</option>
                        </select>

                    </div>

                    <div class="mb-5">

                        <label for="trade_id" class="block text-sm font-semibold text-slate-700 mb-2">
                            Select Trade
                        </label>

                        <select
                            id="trade_id"
                            name="trade_id"
                            required
                            class="w-full px-4 py-3 border border-slate-300 rounded-md text-sm bg-white outline-none focus:ring-2 focus:ring-blue-500"
                        >
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
                            <p class="text-xs text-red-600 mt-2">
                                No trades are currently available for registration.
                            </p>
                        <?php endif; ?>

                    </div>

                    <div class="mb-5">

                        <label for="password" class="block text-sm font-semibold text-slate-700 mb-2">
                            Create Password
                        </label>

                        <input
                            type="password"
                            id="password"
                            name="password"
                            required
                            minlength="8"
                            autocomplete="new-password"
                            placeholder="At least 8 characters"
                            class="w-full px-4 py-3 border border-slate-300 rounded-md text-sm outline-none focus:ring-2 focus:ring-blue-500"
                        >

                    </div>

                    <div class="mb-6">

                        <label for="confirm_password" class="block text-sm font-semibold text-slate-700 mb-2">
                            Confirm Password
                        </label>

                        <input
                            type="password"
                            id="confirm_password"
                            name="confirm_password"
                            required
                            minlength="8"
                            autocomplete="new-password"
                            placeholder="Enter password again"
                            class="w-full px-4 py-3 border border-slate-300 rounded-md text-sm outline-none focus:ring-2 focus:ring-blue-500"
                        >

                    </div>

                    <button
                        type="submit"
                        <?php echo empty($trades) ? 'disabled' : ''; ?>
                        class="w-full bg-blue-700 text-white font-semibold py-3 rounded-md hover:bg-blue-800 transition disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Create Student Account
                    </button>

                </form>

                <div class="text-center mt-6 pt-5 border-t border-slate-200">

                    <p class="text-sm text-slate-500">
                        Already have an account?

                        <a href="loginStudent.php" class="font-semibold text-blue-700 hover:underline">
                            Student Login
                        </a>
                    </p>

                    <a href="login.php" class="inline-block text-xs text-slate-400 hover:text-blue-700 mt-4">
                        Return to Admin Login
                    </a>

                </div>

            <?php endif; ?>

        </div>

        <p class="text-center text-xs text-slate-400 mt-6">
            &copy; <?php echo date("Y"); ?> GIKONKO TSS. All rights reserved.
        </p>

    </div>

</body>
</html>
