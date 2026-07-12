<?php
require_once __DIR__ . '/../config/db.php';
session_start();

$successMessage = '';
$errorMessage = '';
$name = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confrim_password'] ?? '';

    if ($name === '' || $email === '' || $password === '' || $confirmPassword === '') {
        $errorMessage = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMessage = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $errorMessage = 'Password must be at least 6 characters long.';
    } elseif ($password !== $confirmPassword) {
        $errorMessage = 'Passwords do not match.';
    } else {
        $checkStmt = $conn->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $checkStmt->bind_param('s', $email);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows > 0) {
            $errorMessage = 'An account with that email already exists.';
        } else {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $insertStmt = $conn->prepare('INSERT INTO users (username, email, password) VALUES (?, ?, ?)');
            $insertStmt->bind_param('sss', $name, $email, $passwordHash);

            if ($insertStmt->execute()) {
                $_SESSION['user_id'] = $insertStmt->insert_id;
                $_SESSION['username'] = $name;
                header('Location: ../user/index.php');
                exit;
            } else {
                $errorMessage = 'Something went wrong while creating your account.';
            }

            $insertStmt->close();
        }

        $checkStmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova News - Sign Up</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    />
</head>

<body class="min-h-screen bg-[#F6F5FC] flex items-center justify-center p-4 md:p-6">

    <div class="relative w-full max-w-md">

        <!-- Decorative Dots -->
        <div class="hidden md:grid grid-cols-5 gap-4 absolute -left-28 top-20 opacity-40">
            <!-- 25 dots -->
            <div class="w-2 h-2 rounded-full bg-violet-300"></div>
            <div class="w-2 h-2 rounded-full bg-violet-300"></div>
            <div class="w-2 h-2 rounded-full bg-violet-300"></div>
            <div class="w-2 h-2 rounded-full bg-violet-300"></div>
            <div class="w-2 h-2 rounded-full bg-violet-300"></div>

            <div class="w-2 h-2 rounded-full bg-violet-300"></div>
            <div class="w-2 h-2 rounded-full bg-violet-300"></div>
            <div class="w-2 h-2 rounded-full bg-violet-300"></div>
            <div class="w-2 h-2 rounded-full bg-violet-300"></div>
            <div class="w-2 h-2 rounded-full bg-violet-300"></div>

            <div class="w-2 h-2 rounded-full bg-violet-300"></div>
            <div class="w-2 h-2 rounded-full bg-violet-300"></div>
            <div class="w-2 h-2 rounded-full bg-violet-300"></div>
            <div class="w-2 h-2 rounded-full bg-violet-300"></div>
            <div class="w-2 h-2 rounded-full bg-violet-300"></div>

            <div class="w-2 h-2 rounded-full bg-violet-300"></div>
            <div class="w-2 h-2 rounded-full bg-violet-300"></div>
            <div class="w-2 h-2 rounded-full bg-violet-300"></div>
            <div class="w-2 h-2 rounded-full bg-violet-300"></div>
            <div class="w-2 h-2 rounded-full bg-violet-300"></div>

            <div class="w-2 h-2 rounded-full bg-violet-300"></div>
            <div class="w-2 h-2 rounded-full bg-violet-300"></div>
            <div class="w-2 h-2 rounded-full bg-violet-300"></div>
            <div class="w-2 h-2 rounded-full bg-violet-300"></div>
            <div class="w-2 h-2 rounded-full bg-violet-300"></div>
        </div>

        <!-- Card -->
        <div class="bg-white rounded-[20px] shadow-xl p-2.5 md:p-4">

            <!-- Logo -->
            <div class="text-center">

                <h1 class="mt-2.5 text-xl md:text-2xl font-bold">
                    <span class="text-slate-900 font-family">Nova</span>
                    <span class="text-blue-600"> News</span>
                </h1>

                <p class="text-gray-500 mt-1 text-[11px] md:text-xs">
                    Stay informed. Stay ahead.
                </p>

                <h2 class="text-lg md:text-xl font-bold mt-4">
                    Create your account
                </h2>

                <p class="text-gray-500 mt-1 text-[11px] md:text-xs">
                    Join Nova News and start exploring.
                </p>
            </div>

            <!-- Form -->
            <form class="mt-4 space-y-3" method="post" action="">

                <?php if ($successMessage): ?>
                    <div class="rounded-lg border border-green-200 bg-green-50 px-3 py-2 text-[11px] md:text-xs text-green-700">
                        <?php echo htmlspecialchars($successMessage); ?>
                    </div>
                <?php endif; ?>

                <?php if ($errorMessage): ?>
                    <div class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-[11px] md:text-xs text-red-700">
                        <?php echo htmlspecialchars($errorMessage); ?>
                    </div>
                <?php endif; ?>

                <!-- Full Name -->
                <div>
                    <label class="font-semibold text-gray-700 mb-1 block text-[11px] md:text-xs">
                        Full Name
                    </label>

                    <div class="relative">
                        <i class="fa-regular fa-user absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>

                        <input type="text"
                            name="name"
                            placeholder="Enter your full name"
                            value="<?php echo htmlspecialchars($name); ?>"
                            class="w-full h-9 rounded-lg border border-gray-300 pl-11 pr-4 outline-none focus:ring-2 focus:ring-violet-500"
                        />
                    </div>
                </div>

                <!-- Email -->
                <div>
                    <label class="font-semibold text-gray-700 mb-1 block text-[11px] md:text-xs">
                        Email
                    </label>

                    <div class="relative">
                        <i class="fa-regular fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>

                        <input type="email"
                            name="email"
                            placeholder="Enter your email address"
                            value="<?php echo htmlspecialchars($email); ?>"
                            class="w-full h-9 rounded-lg border border-gray-300 pl-11 pr-4 outline-none focus:ring-2 focus:ring-violet-500"
                        />
                    </div>
                </div>

                <!-- Password -->
                <div>
                    <label class="font-semibold text-gray-700 mb-1 block text-[11px] md:text-xs">
                        Password
                    </label>

                    <div class="relative">
                        <i class="fa-solid fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>

                        <input type="password"
                            name="password"
                            placeholder="Create a password"
                            class="w-full h-9 rounded-lg border border-gray-300 pl-11 pr-11 outline-none focus:ring-2 focus:ring-violet-500"
                        />

                        
                    </div>

                    <p class="text-[10px] text-gray-500 mt-1">
                        Must be at least 6 characters with a mix of letters,
                        numbers & symbols.
                    </p>
                </div>

                <!-- Confirm Password -->
                <div>
                    <label class="font-semibold text-gray-700 mb-1 block text-[11px] md:text-xs">
                        Confirm Password
                    </label>

                    <div class="relative">
                        <i class="fa-solid fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>

                        <input type="password"
                            name="confrim_password"
                            placeholder="Confirm your password"
                            class="w-full h-9 rounded-lg border border-gray-300 pl-11 pr-11 outline-none focus:ring-2 focus:ring-violet-500"
                        />

                        <button type="button" class="absolute right-4 top-1/2 -translate-y-1/2">
                        
                        </button>
                    </div>
                </div>

                <!-- Sign Up -->
                <button
                    type="submit"
                    class="w-full h-9 rounded-lg bg-blue-500 text-white font-semibold text-[11px] md:text-xs hover:opacity-95 transition">
                    Register
                </button>

                <!-- Divider -->
                <div class="flex items-center gap-1.5">
                    <div class="flex-1 h-px bg-gray-300"></div>
                    <span class="text-gray-400">or</span>
                    <div class="flex-1 h-px bg-gray-300"></div>
                </div>

                <!-- Login -->
                <p class="text-center text-gray-500">
                    Already have an account?

                    <a href="signin.php" class="text-blue-600 font-semibold">
                        Sign in
                    </a>
                </p>

            </form>

        </div>
    </div>

</body>
</html>