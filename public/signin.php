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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        nova: {
                            dark: '#1E224F',
                            primary: '#5B41FF',
                            accent: '#7C5CFC',
                        }
                    }
                }
            }
        }
    </script>

    <style>
        .split-left {
            background: linear-gradient(135deg, #1E224F 0%, #2D1B69 50%, #5B41FF 100%);
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.06);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .input-focus {
            transition: all 0.2s ease;
        }
        .input-focus:focus {
            border-color: #5B41FF;
            box-shadow: 0 0 0 3px rgba(91, 65, 255, 0.15);
        }

        .fade-in {
            animation: fadeIn 0.6s ease forwards;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .float-orb {
            animation: float 6s ease-in-out infinite;
        }
        .float-orb-delay {
            animation: float 6s ease-in-out 2s infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0) scale(1); }
            50% { transform: translateY(-20px) scale(1.05); }
        }
        .password-toggle:hover {
            color: #5B41FF;
        }
    </style>
</head>

<body class="bg-gray-50 min-h-screen">

    <div class="flex min-h-screen">

        <!-- ═══════════════════════════════════════════════ -->
        <!-- LEFT SIDE — Promo / Brand                      -->
        <!-- ═══════════════════════════════════════════════ -->
        <div class="split-left hidden lg:flex lg:w-[48%] xl:w-[50%] relative overflow-hidden p-10 xl:p-14 flex-col justify-between text-white">

            <!-- Decorative orbs -->
            <div class="absolute top-20 left-10 w-72 h-72 bg-purple-400/20 rounded-full blur-3xl float-orb"></div>
            <div class="absolute bottom-32 right-16 w-56 h-56 bg-blue-400/15 rounded-full blur-3xl float-orb-delay"></div>
            <div class="absolute top-1/2 left-1/3 w-40 h-40 bg-indigo-300/10 rounded-full blur-2xl float-orb-delay"></div>

            <!-- Logo -->
            <div class="relative z-10">
                <a href="index.php" class="inline-flex items-center gap-3 group">
                    <div class="w-10 h-10 bg-white/15 rounded-xl flex items-center justify-center backdrop-blur-sm border border-white/20 group-hover:bg-white/25 transition">
                        <i class="fa-solid fa-bolt text-lg"></i>
                    </div>
                    <span class="text-xl font-black tracking-wide">NOVA <span class="text-purple-300">NEWS</span></span>
                </a>
            </div>

            <!-- Main content -->
            <div class="relative z-10 space-y-8">
                <div>
                    <div class="inline-flex items-center gap-2 bg-white/10 backdrop-blur-sm border border-white/15 rounded-full px-4 py-1.5 text-xs font-semibold text-purple-200 mb-6">
                        <i class="fa-solid fa-crown text-amber-400"></i>
                        Go Premium Today
                    </div>
                    <h1 class="text-3xl xl:text-4xl font-extrabold leading-tight">
                        Your gateway to<br>
                        <span class="bg-gradient-to-r from-purple-300 via-blue-200 to-purple-300 bg-clip-text text-transparent">premium journalism</span>
                    </h1>
                    <p class="text-white/60 mt-4 text-sm xl:text-base leading-relaxed max-w-md">
                        Join thousands of readers who trust Nova News for in-depth reporting, exclusive stories, and an ad-free reading experience.
                    </p>
                </div>

                <!-- Feature cards -->
                <div class="space-y-3">
                    <div class="glass-card rounded-2xl p-4 flex items-center gap-4 group hover:bg-white/10 transition cursor-default">
                        <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center shrink-0 shadow-lg shadow-amber-500/25">
                            <i class="fa-solid fa-lock text-white text-sm"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-sm">Exclusive Articles</h3>
                            <p class="text-white/50 text-xs mt-0.5">In-depth analysis from award-winning journalists</p>
                        </div>
                    </div>

                    <div class="glass-card rounded-2xl p-4 flex items-center gap-4 group hover:bg-white/10 transition cursor-default">
                        <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-emerald-400 to-teal-500 flex items-center justify-center shrink-0 shadow-lg shadow-emerald-500/25">
                            <i class="fa-solid fa-eye-slash text-white text-sm"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-sm">100% Ad-Free</h3>
                            <p class="text-white/50 text-xs mt-0.5">Pure reading experience without interruptions</p>
                        </div>
                    </div>

                    <div class="glass-card rounded-2xl p-4 flex items-center gap-4 group hover:bg-white/10 transition cursor-default">
                        <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-blue-400 to-indigo-500 flex items-center justify-center shrink-0 shadow-lg shadow-blue-500/25">
                            <i class="fa-solid fa-bolt text-white text-sm"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-sm">Breaking News First</h3>
                            <p class="text-white/50 text-xs mt-0.5">Get notified before anyone else sees it</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom -->
            <div class="relative z-10 flex items-center justify-between text-white/40 text-xs">
                <span>&copy; 2026 Nova News. All rights reserved.</span>
                <div class="flex items-center gap-4">
                    <a href="#" class="hover:text-white/70 transition">Privacy</a>
                    <a href="#" class="hover:text-white/70 transition">Terms</a>
                </div>
            </div>
        </div>

        <!-- ═══════════════════════════════════════════════ -->
        <!-- RIGHT SIDE — Sign In Form                      -->
        <!-- ═══════════════════════════════════════════════ -->
        <div class="flex-1 flex items-center justify-center p-6 sm:p-8 bg-white relative">

            <!-- Mobile logo -->
            <div class="absolute top-6 left-6 lg:hidden">
                <a href="index.php" class="inline-flex items-center gap-2">
                    <div class="w-9 h-9 bg-nova-primary rounded-xl flex items-center justify-center">
                        <i class="fa-solid fa-bolt text-white text-sm"></i>
                    </div>
                    <span class="text-lg font-black text-nova-dark tracking-wide">NOVA <span class="text-nova-primary">NEWS</span></span>
                </a>
            </div>

            <div class="w-full max-w-[420px] fade-in">

                <!-- Heading -->
                <div class="mb-8">
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">Welcome back</h2>
                    <p class="text-gray-400 mt-2 text-sm">Sign in to your account to continue reading</p>
                </div>

                <!-- Error message -->
                <?php if ($errorMessage): ?>
                <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600 flex items-center gap-2.5 fade-in">
                    <i class="fa-solid fa-circle-exclamation text-red-400"></i>
                    <?php echo htmlspecialchars($errorMessage); ?>
                </div>
                <?php endif; ?>

                <!-- Success message -->
                <?php if ($successMessage): ?>
                <div class="mb-5 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-600 flex items-center gap-2.5 fade-in">
                    <i class="fa-solid fa-circle-check text-green-400"></i>
                    <?php echo htmlspecialchars($successMessage); ?>
                </div>
                <?php endif; ?>

                <!-- Email/Password Form -->
                <form class="space-y-4" method="post" action="">

                    <!-- Email -->
                    <div>
                        <label class="block mb-1.5 text-sm font-semibold text-gray-700">Email address</label>
                        <div class="relative">
                            <i class="fa-regular fa-envelope absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-300 text-sm"></i>
                            <input
                                type="email" name="email"
                                value="<?php echo htmlspecialchars($email); ?>"
                                placeholder="you@example.com"
                                class="input-focus w-full h-11 rounded-xl border border-gray-200 bg-gray-50/50 pl-10 pr-4 text-sm text-gray-900 placeholder:text-gray-300 focus:outline-none focus:bg-white">
                        </div>
                    </div>

                    <!-- Password -->
                    <div>
                        <label class="block mb-1.5 text-sm font-semibold text-gray-700">Password</label>
                        <div class="relative">
                            <i class="fa-solid fa-lock absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-300 text-sm"></i>
                            <input
                                id="password-input"
                                type="password" name="password"
                                placeholder="Enter your password"
                                class="input-focus w-full h-11 rounded-xl border border-gray-200 bg-gray-50/50 pl-10 pr-11 text-sm text-gray-900 placeholder:text-gray-300 focus:outline-none focus:bg-white">
                            <button type="button" onclick="togglePassword()" class="password-toggle absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-300 hover:text-nova-primary transition text-sm">
                                <i id="eye-icon" class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Remember / Forgot -->
                    <div class="flex items-center justify-between pt-1">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" class="w-4 h-4 rounded border-gray-300 text-nova-primary focus:ring-nova-primary/30 cursor-pointer">
                            <span class="text-sm text-gray-500">Remember me</span>
                        </label>
                        <a href="#" class="text-sm font-semibold text-nova-primary hover:text-nova-accent transition">Forgot password?</a>
                    </div>

                    <!-- Sign In Button -->
                    <button
                        type="submit"
                        class="w-full h-12 rounded-xl bg-gradient-to-r from-nova-primary to-nova-accent text-white font-bold text-sm hover:shadow-lg hover:shadow-nova-primary/30 hover:-translate-y-0.5 active:translate-y-0 transition-all duration-200">
                        Sign In
                    </button>

                </form>

                <!-- Register link -->
                <p class="text-center text-sm text-gray-400 mt-7">
                    Don't have an account?
                    <a href="register.php" class="font-bold text-nova-primary hover:text-nova-accent transition ml-1">Create one now</a>
                </p>

            </div>
        </div>

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
