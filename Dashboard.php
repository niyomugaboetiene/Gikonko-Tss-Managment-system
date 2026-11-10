
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$username = $_SESSION['username'] ?? 'User';
$role = $_SESSION['role'] ?? 'System User';
$currentPage = basename($_SERVER['PHP_SELF'] ?? '');

$navigation = [
    ['name' => 'Trainees', 'link' => 'listOfTrainee.php'],
    ['name' => 'Trades', 'link' => 'listOfTrades.php'],
    ['name' => 'Modules', 'link' => 'listOfModule.php'],
    ['name' => 'Marks', 'link' => 'listOfMarks.php'],
    ['name' => 'Competent', 'link' => 'competent.php'],
    ['name' => 'NYC Trainees', 'link' => 'notCompetent.php']
];

$flashMessages = [
    'success' => [
        'text' => $_SESSION['success'] ?? '',
        'type' => 'success'
    ],
    'error' => [
        'text' => $_SESSION['error'] ?? '',
        'type' => 'error'
    ],
    'login_error' => [
        'text' => $_SESSION['login_error'] ?? '',
        'type' => 'error'
    ]
];

foreach (['success', 'error', 'login_error'] as $key) {
    unset($_SESSION[$key]);
}

$displayName = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');
$displayRole = htmlspecialchars($role, ENT_QUOTES, 'UTF-8');
$avatarLetter = strtoupper(substr((string)$username, 0, 1));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GIKONKO TSS Management System</title>
    <link rel="icon" href="gikonko.png" type="image/png">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f8fafc;
            color: #0f172a;
        }

        a {
            text-decoration: none;
        }

        button {
            font-family: inherit;
        }

        .dashboard-header {
            width: 100%;
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
            position: relative;
            z-index: 100;
        }

        .dashboard-container {
            width: 100%;
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 24px;
        }

        .navbar {
            min-height: 76px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-shrink: 0;
        }

        .brand-logo {
            width: 44px;
            height: 44px;
            object-fit: cover;
            border-radius: 7px;
            border: 1px solid #e2e8f0;
        }

        .brand-info h1 {
            font-size: 19px;
            font-weight: 750;
            color: #0f172a;
            letter-spacing: -0.4px;
        }

        .brand-info p {
            font-size: 12px;
            color: #64748b;
            margin-top: 4px;
        }

        .desktop-navigation {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .nav-link {
            padding: 10px 12px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            color: #475569;
            transition: background 0.2s, color 0.2s;
            white-space: nowrap;
        }

        .nav-link:hover {
            background: #f1f5f9;
            color: #0f172a;
        }

        .nav-link.active {
            background: #eff6ff;
            color: #1d4ed8;
        }

        .account-area {
            display: flex;
            align-items: center;
            gap: 13px;
        }

        .account-info {
            text-align: right;
        }

        .account-name {
            font-size: 13px;
            font-weight: 700;
            color: #1e293b;
        }

        .account-role {
            font-size: 12px;
            color: #64748b;
            margin-top: 4px;
        }

        .profile-wrapper {
            position: relative;
        }

        .profile-button {
            width: 40px;
            height: 40px;
            border: none;
            border-radius: 50%;
            background: #1d4ed8;
            color: white;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.2s;
        }

        .profile-button:hover {
            background: #1e40af;
        }

        .profile-dropdown {
            display: none;
            position: absolute;
            top: 49px;
            right: 0;
            width: 210px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.12);
            overflow: hidden;
            z-index: 200;
        }

        .profile-dropdown.show {
            display: block;
        }

        .dropdown-user {
            padding: 15px;
            border-bottom: 1px solid #f1f5f9;
        }

        .dropdown-user strong {
            display: block;
            font-size: 13px;
            color: #1e293b;
        }

        .dropdown-user span {
            display: block;
            font-size: 12px;
            color: #64748b;
            margin-top: 4px;
        }

        .logout-link {
            display: block;
            padding: 12px 15px;
            color: #dc2626;
            font-size: 13px;
            font-weight: 600;
            transition: background 0.2s;
        }

        .logout-link:hover {
            background: #fef2f2;
        }

        .mobile-menu-button {
            display: none;
            width: 40px;
            height: 40px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            background: #ffffff;
            color: #475569;
            font-size: 23px;
            cursor: pointer;
        }

        .mobile-navigation {
            display: none;
            padding: 15px 0;
            border-top: 1px solid #e2e8f0;
        }

        .mobile-navigation.show {
            display: block;
        }

        .mobile-nav-links {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
        }

        .mobile-nav-links .nav-link {
            padding: 12px;
            background: #f8fafc;
        }

        .mobile-nav-links .nav-link.active {
            background: #eff6ff;
        }

        .mobile-account {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            border-top: 1px solid #e2e8f0;
            margin-top: 16px;
            padding-top: 15px;
        }

        .mobile-account-info strong {
            display: block;
            font-size: 13px;
            color: #1e293b;
        }

        .mobile-account-info span {
            display: block;
            font-size: 12px;
            color: #64748b;
            margin-top: 4px;
        }

        .mobile-logout {
            padding: 9px 15px;
            border-radius: 6px;
            background: #fef2f2;
            color: #dc2626;
            font-size: 12px;
            font-weight: 700;
        }

        .dashboard-main {
            width: 100%;
            max-width: 1280px;
            margin: 0 auto;
            padding: 30px 24px 20px;
        }

        .welcome-section {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            padding: 20px 0;
            margin-bottom: 10px;
        }

        .welcome-date {
            font-size: 13px;
            color: #64748b;
        }

        .welcome-title {
            font-size: 25px;
            font-weight: 750;
            color: #0f172a;
            margin-top: 8px;
            letter-spacing: -0.5px;
        }

        .welcome-description {
            font-size: 14px;
            color: #64748b;
            margin-top: 8px;
            line-height: 1.6;
        }

        .system-status {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            padding: 10px 14px;
            background: #f0fdf4;
            border: 1px solid #dcfce7;
            border-radius: 6px;
            color: #15803d;
            font-size: 12px;
            font-weight: 700;
            white-space: nowrap;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #22c55e;
        }

        .flash-container {
            margin-bottom: 12px;
        }

        .flash-message {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            padding: 13px 16px;
            border-radius: 6px;
            margin-bottom: 10px;
            font-size: 13px;
            line-height: 1.5;
        }

        .flash-success {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #166534;
        }

        .flash-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }

        .flash-close {
            border: none;
            background: transparent;
            color: inherit;
            font-size: 21px;
            cursor: pointer;
        }

        @media (max-width: 1100px) {
            .desktop-navigation {
                gap: 0;
            }

            .nav-link {
                padding: 9px 8px;
                font-size: 12px;
            }

            .brand-info h1 {
                font-size: 17px;
            }
        }

        @media (max-width: 900px) {
            .desktop-navigation,
            .desktop-account {
                display: none;
            }

            .mobile-menu-button {
                display: block;
            }

            .navbar {
                min-height: 70px;
            }
        }

        @media (max-width: 600px) {
            .dashboard-container {
                padding: 0 16px;
            }

            .dashboard-main {
                padding: 20px 16px;
            }

            .brand-logo {
                width: 39px;
                height: 39px;
            }

            .brand-info h1 {
                font-size: 16px;
            }

            .brand-info p {
                font-size: 10px;
            }

            .welcome-section {
                align-items: flex-start;
                flex-direction: column;
                padding: 15px 0;
            }

            .welcome-title {
                font-size: 22px;
            }

            .welcome-description {
                font-size: 13px;
            }

            .system-status {
                align-self: flex-start;
            }

            .mobile-nav-links {
                grid-template-columns: 1fr 1fr;
            }
        }

        [hidden] {
            display: none !important;
        }
    </style>
</head>

<body>

<header class="dashboard-header">

    <div class="dashboard-container">

        <div class="navbar">

            <a href="greetingUser.php" class="brand">

                <img src="gikonko.png" alt="GIKONKO TSS Logo" class="brand-logo">

                <div class="brand-info">
                    <h1>GIKONKO TSS</h1>
                    <p>Student Management System</p>
                </div>

            </a>

            <nav class="desktop-navigation">

                <?php foreach ($navigation as $item): ?>
                    <?php $active = $currentPage === $item['link']; ?>

                    <a
                        href="<?php echo htmlspecialchars($item['link'], ENT_QUOTES, 'UTF-8'); ?>"
                        class="nav-link <?php echo $active ? 'active' : ''; ?>"
                    >
                        <?php echo htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?>
                    </a>

                <?php endforeach; ?>

            </nav>

            <div class="account-area desktop-account">

                <div class="account-info">
                    <p class="account-name"><?php echo $displayName; ?></p>
                    <p class="account-role"><?php echo $displayRole; ?></p>
                </div>

                <div class="profile-wrapper">

                    <button
                        type="button"
                        class="profile-button"
                        id="profileButton"
                        aria-label="Open profile menu"
                        aria-expanded="false"
                    >
                        <?php echo htmlspecialchars($avatarLetter, ENT_QUOTES, 'UTF-8'); ?>
                    </button>

                    <div class="profile-dropdown" id="profileDropdown">

                        <div class="dropdown-user">
                            <strong><?php echo $displayName; ?></strong>
                            <span><?php echo $displayRole; ?></span>
                        </div>

                        <a
                            href="logout.php"
                            class="logout-link"
                            onclick="return confirm('Are you sure you want to logout?')"
                        >
                            Logout
                        </a>

                    </div>

                </div>

            </div>

            <button
                type="button"
                class="mobile-menu-button"
                id="mobileMenuButton"
                aria-label="Toggle navigation"
                aria-expanded="false"
            >
                ☰
            </button>

        </div>

        <div class="mobile-navigation" id="mobileNavigation">

            <nav class="mobile-nav-links">

                <?php foreach ($navigation as $item): ?>
                    <?php $active = $currentPage === $item['link']; ?>

                    <a
                        href="<?php echo htmlspecialchars($item['link'], ENT_QUOTES, 'UTF-8'); ?>"
                        class="nav-link <?php echo $active ? 'active' : ''; ?>"
                    >
                        <?php echo htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?>
                    </a>

                <?php endforeach; ?>

            </nav>

            <div class="mobile-account">

                <div class="mobile-account-info">
                    <strong><?php echo $displayName; ?></strong>
                    <span><?php echo $displayRole; ?></span>
                </div>

                <a
                    href="logout.php"
                    class="mobile-logout"
                    onclick="return confirm('Are you sure you want to logout?')"
                >
                    Logout
                </a>

            </div>

        </div>

    </div>

</header>

<main class="dashboard-main">

    <section class="welcome-section">

        <div>
            <p class="welcome-date">
                <?php echo date("l, F j, Y"); ?>
            </p>

            <h2 class="welcome-title">
                Welcome back, <?php echo $displayName; ?>!
            </h2>

            <p class="welcome-description">
                Manage your trainees, modules, trades and academic results.
            </p>
        </div>

        <div class="system-status">
            <span class="status-dot"></span>
            System Active
        </div>

    </section>

    <div class="flash-container">

        <?php foreach ($flashMessages as $message): ?>

            <?php if (!empty($message['text'])): ?>

                <div class="flash-message <?php echo $message['type'] === 'success' ? 'flash-success' : 'flash-error'; ?>">

                    <div>
                        <?php if ($message['type'] === 'success'): ?>
                            <strong>✓</strong>
                        <?php else: ?>
                            <strong>!</strong>
                        <?php endif; ?>

                        <?php echo htmlspecialchars($message['text'], ENT_QUOTES, 'UTF-8'); ?>
                    </div>

                    <button
                        type="button"
                        class="flash-close"
                        onclick="this.parentElement.remove()"
                        aria-label="Dismiss message"
                    >
                        &times;
                    </button>

                </div>

            <?php endif; ?>

        <?php endforeach; ?>

    </div>

</main>

<script>
    const mobileMenuButton = document.getElementById('mobileMenuButton');
    const mobileNavigation = document.getElementById('mobileNavigation');

    const profileButton = document.getElementById('profileButton');
    const profileDropdown = document.getElementById('profileDropdown');

    mobileMenuButton.addEventListener('click', function () {
        const isOpen = mobileNavigation.classList.toggle('show');

        mobileMenuButton.setAttribute('aria-expanded', isOpen);
        mobileMenuButton.textContent = isOpen ? '×' : '☰';
    });

    profileButton.addEventListener('click', function (event) {
        event.stopPropagation();

        const isOpen = profileDropdown.classList.toggle('show');

        profileButton.setAttribute('aria-expanded', isOpen);
    });

    document.addEventListener('click', function (event) {
        if (!profileDropdown.contains(event.target) &&
            !profileButton.contains(event.target)) {

            profileDropdown.classList.remove('show');
            profileButton.setAttribute('aria-expanded', 'false');
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            profileDropdown.classList.remove('show');
            mobileNavigation.classList.remove('show');

            profileButton.setAttribute('aria-expanded', 'false');
            mobileMenuButton.setAttribute('aria-expanded', 'false');
            mobileMenuButton.textContent = '☰';
        }
    });
</script>

</body>
</html>
