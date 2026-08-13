<?php
require_once __DIR__ . '/../config/db.php';
session_start();

$errorMessage = '';
$successMessage = $_GET['success'] ?? '';
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
    <title>Nova News — Sign In</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] }
                }
            }
        }
    </script>

    <style>
        .fade-in {
            animation: fadeIn 0.6s ease forwards;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>

<body class="bg-slate-100 min-h-screen flex items-center justify-center p-4 sm:p-6 font-sans box-border">

    <div class="box-border w-full max-w-md bg-white rounded-2xl shadow-xl shadow-slate-200/60 p-6 sm:p-8 flex flex-col gap-4 items-stretch border border-slate-100">
        
        <!-- Header & Logo -->
        <div class="flex flex-col items-center mb-4">
            <a href="index.php" class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-slate-900 text-white mb-6 hover:bg-blue-600 transition-colors shadow-sm">
                <i class="fa-solid fa-bolt text-xl"></i>
            </a>
            <h2 class="text-2xl font-bold text-slate-900 tracking-tight text-center">Welcome back</h2>
            <p class="text-slate-500 mt-2 text-sm text-center">Sign in to your account to continue</p>
        </div>

        <!-- Error message -->
        <?php if ($errorMessage): ?>
        <div class="box-border mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 flex items-center gap-2.5">
            <i class="fa-solid fa-circle-exclamation text-red-500"></i>
            <?php echo htmlspecialchars($errorMessage); ?>
        </div>
        <?php endif; ?>

        <!-- Success message -->
        <?php if ($successMessage): ?>
        <div class="box-border mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 flex items-center gap-2.5">
            <i class="fa-solid fa-circle-check text-emerald-500"></i>
            <?php echo htmlspecialchars($successMessage); ?>
        </div>
        <?php endif; ?>

        
        <!-- Form -->
        <form class="box-border flex flex-col gap-4 items-stretch" method="post" action="">
            <!-- Email -->
            <div class="box-border w-full">
                <label class="block mb-2 text-sm font-semibold text-slate-700">Email</label>
                <div class="relative box-border w-full">
                    <i class="fa-regular fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input
                        type="email" name="email"
                        value="<?php echo htmlspecialchars($email); ?>"
                        placeholder="name@example.com"
                        class="box-border w-full h-12 rounded-xl border border-slate-300 bg-white pl-11 pr-4 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600 transition-shadow">
                </div>
            </div>

            <!-- Password -->
            <div class="box-border w-full">
                <label class="block mb-2 text-sm font-semibold text-slate-700">Password</label>
                <div class="relative box-border w-full">
                    <i class="fa-solid fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input
                        id="password-input"
                        type="password" name="password"
                        placeholder="••••••••"
                        class="box-border w-full h-12 rounded-xl border border-slate-300 bg-white pl-11 pr-12 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600 transition-shadow">
                    <button type="button" onclick="togglePassword()" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700 transition" title="Toggle password visibility">
                        <i id="eye-icon" class="fa-regular fa-eye"></i>
                    </button>
                </div>
            </div>

            <!-- Remember / Forgot -->
            <div class="box-border flex items-center justify-between mt-2">
                <label class="flex items-center gap-2.5 cursor-pointer group">
                    <input type="checkbox" class="box-border w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-600 cursor-pointer transition">
                    <span class="text-sm font-medium text-slate-600 group-hover:text-slate-900 transition">Remember me</span>
                </label>
                <a href="#" class="text-sm font-semibold text-blue-600 hover:text-slate-900 transition-colors">Forgot password?</a>
            </div>

            <!-- Submit Button -->
            <button
                type="submit"
                class="box-border w-full h-12 mt-2 rounded-xl bg-blue-600 text-white font-semibold text-sm hover:bg-slate-900 transition-all duration-200 shadow-md hover:shadow-lg">
                Sign In
            </button>
        </form>

        <!-- Footer Link -->
        <p class="text-center text-sm font-medium text-slate-500 mt-8">
            Don't have an account? 
            <a href="register.php" class="font-bold text-slate-900 hover:text-blue-600 transition-colors">Sign up</a>
        </p>

    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('password-input');
            const icon = document.getElementById('eye-icon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'fa-regular fa-eye-slash';
            } else {
                input.type = 'password';
                icon.className = 'fa-regular fa-eye';
            }
        }
    </script>

</body>
</html>
