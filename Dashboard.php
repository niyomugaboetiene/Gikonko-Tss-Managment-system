
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
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gikonko Tss Management System Dashboard</title>
    <link href="output.css" rel="stylesheet">
</head>
<body>
    <div class="w-full bg-white border-b border-slate-200 shadow-sm"
     x-data="{ open: false, profileOpen: false }">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="h-16 flex items-center justify-between gap-4">

            <a href="greetingUser.php" class="flex items-center gap-3 shrink-0">
                <img src="gikonko.png"
                     alt="GIKONKO TSS Logo"
                     class="w-10 h-10 object-cover rounded-md">

                <div>
                    <h1 class="text-lg font-bold text-slate-800 leading-tight">
                        GIKONKO TSS
                    </h1>
                    <p class="text-xs text-slate-500">
                        Student Management System
                    </p>
                </div>
            </a>

            <nav class="hidden lg:flex items-center gap-1">
                <?php foreach ($navigation as $item): ?>
                    <?php $active = $currentPage === $item['link']; ?>

                    <a href="<?php echo htmlspecialchars($item['link']); ?>"
                       class="px-3 py-2 text-sm font-medium rounded-md transition
                       <?php echo $active
                           ? 'bg-blue-50 text-blue-700'
                           : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'; ?>">
                        <?php echo htmlspecialchars($item['name']); ?>
                    </a>
                <?php endforeach; ?>
            </nav>

            <div class="hidden md:flex items-center gap-4">

                <div class="text-right">
                    <p class="text-sm font-semibold text-slate-800">
                        <?php echo htmlspecialchars($username); ?>
                    </p>
                    <p class="text-xs text-slate-500">
                        <?php echo htmlspecialchars($role); ?>
                    </p>
                </div>

                <div class="relative" @click.outside="profileOpen = false">

                    <button @click="profileOpen = !profileOpen"
                            class="w-10 h-10 rounded-full bg-blue-700 text-white font-semibold flex items-center justify-center hover:bg-blue-800 transition">
                        <?php echo strtoupper(substr($username, 0, 1)); ?>
                    </button>

                    <div x-show="profileOpen"
                         x-transition
                         class="absolute right-0 top-12 w-48 bg-white border border-slate-200 rounded-md shadow-lg py-2 z-50">

                        <div class="px-4 py-2 border-b border-slate-100">
                            <p class="text-sm font-semibold text-slate-800">
                                <?php echo htmlspecialchars($username); ?>
                            </p>
                            <p class="text-xs text-slate-500">
                                <?php echo htmlspecialchars($role); ?>
                            </p>
                        </div>

                        <a href="logout.php"
                           onclick="return confirm('Are you sure you want to logout?')"
                           class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                            Logout
                        </a>
                    </div>
                </div>
            </div>

            <button @click="open = !open"
                    class="lg:hidden p-2 rounded-md text-slate-600 hover:bg-slate-100"
                    aria-label="Toggle navigation menu">
                <svg x-show="!open" class="w-6 h-6" fill="none"
                     stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>

                <svg x-show="open" x-cloak class="w-6 h-6" fill="none"
                     stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>

        </div>

        <div x-show="open" x-transition class="lg:hidden border-t border-slate-200 py-3">

            <div class="grid grid-cols-2 gap-2">
                <?php foreach ($navigation as $item): ?>
                    <?php $active = $currentPage === $item['link']; ?>

                    <a href="<?php echo htmlspecialchars($item['link']); ?>"
                       class="px-3 py-2.5 text-sm font-medium rounded-md
                       <?php echo $active
                           ? 'bg-blue-50 text-blue-700'
                           : 'text-slate-600 hover:bg-slate-100'; ?>">
                        <?php echo htmlspecialchars($item['name']); ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <div class="flex items-center justify-between mt-4 pt-3 border-t border-slate-200">
                <div>
                    <p class="text-sm font-semibold text-slate-800">
                        <?php echo htmlspecialchars($username); ?>
                    </p>
                    <p class="text-xs text-slate-500">
                        <?php echo htmlspecialchars($role); ?>
                    </p>
                </div>

                <a href="logout.php"
                   onclick="return confirm('Are you sure you want to logout?')"
                   class="px-4 py-2 text-sm font-medium text-red-600 bg-red-50 rounded-md hover:bg-red-100">
                    Logout
                </a>
            </div>

        </div>

    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-24 w-full">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 py-4">

        <div>
            <p class="text-sm text-slate-500">
                <?php echo date("l, F j, Y"); ?>
            </p>

            <h2 class="text-xl sm:text-2xl font-bold text-slate-800 mt-1">
                Welcome back, <?php echo htmlspecialchars($username); ?>!
            </h2>

            <p class="text-sm text-slate-500 mt-1">
                Manage your trainees, modules, trades and academic results.
            </p>
        </div>

        <div class="inline-flex items-center gap-2 bg-green-50 border border-green-100 px-3 py-2 rounded-md self-start">
            <span class="w-2 h-2 rounded-full bg-green-500"></span>
            <span class="text-xs font-medium text-green-700">
                System Active
            </span>
        </div>

    </div>

    <?php foreach ($flashMessages as $message): ?>
        <?php if (!empty($message['text'])): ?>

            <div class="mb-4 px-4 py-3 rounded-md border flex items-start justify-between gap-3
                <?php echo $message['type'] === 'success'
                    ? 'bg-green-50 border-green-200 text-green-800'
                    : 'bg-red-50 border-red-200 text-red-800'; ?>"
                x-data="{ visible: true }"
                x-show="visible">

                <div class="flex items-center gap-2 text-sm">
                    <?php if ($message['type'] === 'success'): ?>
                        <span class="font-bold">&#10003;</span>
                    <?php else: ?>
                        <span class="font-bold">!</span>
                    <?php endif; ?>

                    <?php echo htmlspecialchars($message['text']); ?>
                </div>

                <button @click="visible = false"
                        class="font-bold opacity-60 hover:opacity-100">
                    &times;
                </button>
            </div>

        <?php endif; ?>
    <?php endforeach; ?>

</div>
</body>
</html>
