<?php
require_once __DIR__ . '/../config/db.php';
session_start();

$errorMessage = '';
$successMessage = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $errorMessage = 'Please enter both email and password.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMessage = 'Please enter a valid email address.';
    } else {
        $db = getDB();
        $stmt = $db->prepare('SELECT id, username, password, role FROM users WHERE email = ? LIMIT 1');

        if ($stmt) {
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows > 0) {
                $user = $result->fetch_assoc();
                $passwordIsValid = password_verify($password, $user['password']) || $user['password'] === $password;

                if ($passwordIsValid) {
                    $_SESSION['user_id'] = (int) $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'] ?? 'user';

                    if (($user['role'] ?? 'user') === 'admin') {
                        header('Location: ../admin/index.php');
                    } else {
                        header('Location: ../user/index.php');
                    }
                    exit;
                } else {
                    $errorMessage = 'Incorrect email or password.';
                }
            } else {
                $errorMessage = 'No account found with that email. Please sign up first.';
            }

            $stmt->close();
        } else {
            $errorMessage = 'Database error. Please try again later.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova News Login</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Awesome -->
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        body{
            font-family:'Poppins',sans-serif;
        }
    </style>

</head>
<body class="bg-[#F7F6FD] min-h-screen flex items-center justify-center p-4 md:p-6">

<div class="w-full max-w-md">

    <!-- Card -->
    <div class="bg-white rounded-[24px] shadow-xl p-6 md:p-8">

        <!-- Logo -->
        <div class="text-center">

            <h1 class="mt-4 text-3xl md:text-4xl font-bold">
                <span class="text-slate-900">Nova</span>
                <span class="text-blue-600"> News</span>
            </h1>

            <p class="text-gray-500 mt-2 text-sm md:text-base">
                Stay informed. Stay ahead.
            </p>

            <h2 class="mt-8 text-2xl md:text-3xl font-bold text-slate-900">
                Welcome back
            </h2>

            <p class="text-gray-500 mt-2 text-sm md:text-base">
                Please sign in to your account to continue
            </p>

        </div>

        <!-- Form -->
        <form class="mt-8 space-y-4" method="post" action="">

            <?php if ($successMessage): ?>
                <div class="rounded-lg border border-green-200 bg-green-50 px-3 py-2 text-sm text-green-700">
                    <?php echo htmlspecialchars($successMessage); ?>
                </div>
            <?php endif; ?>

            <?php if ($errorMessage): ?>
                <div class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
                    <?php echo htmlspecialchars($errorMessage); ?>
                </div>
            <?php endif; ?>

            <!-- Email -->
            <div>

                <label class="block mb-2 font-semibold text-sm md:text-base">
                    Email
                </label>

                <div class="relative">

                    <i class="fa-regular fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>

                    <input
                        type="email" name="email"
                        value="<?php echo htmlspecialchars($email); ?>"
                        placeholder="Enter your email"
                        class="w-full h-11 rounded-xl border border-gray-300 pl-12 pr-4 focus:outline-none focus:ring-2 focus:ring-violet-500">

                </div>

            </div>

            <!-- Password -->
            <div>

                <label class="block mb-2 font-semibold text-sm md:text-base">
                    Password
                </label>

                <div class="relative">

                    <i class="fa-solid fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>

                    <input
                        type="password" name="password"
                        placeholder="Enter your password"
                        class="w-full h-11 rounded-xl border border-gray-300 pl-12 pr-12 focus:outline-none focus:ring-2 focus:ring-violet-500">

                    <button
                        type="button"
                        class="absolute right-4 top-1/2 -translate-y-1/2">

                       
                    </button>

                </div>

            </div>

            <!-- Button -->

            <button
                type="submit"
                class="w-full h-11 rounded-xl bg-blue-500 text-white text-sm md:text-base font-semibold hover:opacity-95">

                Sign In

            </button>

            <!-- Divider -->

            <div class="flex items-center gap-4">

                <div class="flex-1 h-px bg-gray-300"></div>

                <span class="text-gray-500">
                    or
                </span>

                <div class="flex-1 h-px bg-gray-300"></div>

            </div>

            <!-- Bottom -->

                <p class="text-center text-gray-500 text-sm md:text-base">

                Don't have an account?

                <a
                    href="register.php"
                    class="text-blue-600 font-semibold hover:underline">

                    Register

                </a>

            </p>

        </form>

    </div>

</div>

</body>
</html>