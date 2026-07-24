<?php
require_once __DIR__ . '/../config/db.php';
session_start();

$name = trim($_GET['name'] ?? $_POST['name'] ?? '');
$email = trim($_GET['email'] ?? $_POST['email'] ?? '');
$errorMessage = $_GET['error'] ?? '';
$successMessage = $_GET['success'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova News — Create Account</title>

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
        .check-input:checked {
            background-color: #5B41FF;
            border-color: #5B41FF;
        }
        .strength-bar { transition: width 0.3s ease, background-color 0.3s ease; }
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
                        <i class="fa-solid fa-rocket text-emerald-400"></i>
                        Start Your Journey
                    </div>
                    <h1 class="text-3xl xl:text-4xl font-extrabold leading-tight">
                        Join the community of<br>
                        <span class="bg-gradient-to-r from-purple-300 via-blue-200 to-purple-300 bg-clip-text text-transparent">informed readers</span>
                    </h1>
                    <p class="text-white/60 mt-4 text-sm xl:text-base leading-relaxed max-w-md">
                        Create your free account and get instant access to breaking news, exclusive stories, and personalized content — all in one place.
                    </p>
                </div>

                <!-- Stats -->
                <div class="grid grid-cols-3 gap-4">
                    <div class="glass-card rounded-2xl p-4 text-center cursor-default">
                        <div class="text-2xl font-extrabold">50K+</div>
                        <div class="text-white/50 text-xs mt-1">Active Readers</div>
                    </div>
                    <div class="glass-card rounded-2xl p-4 text-center cursor-default">
                        <div class="text-2xl font-extrabold">12K+</div>
                        <div class="text-white/50 text-xs mt-1">Articles Published</div>
                    </div>
                    <div class="glass-card rounded-2xl p-4 text-center cursor-default">
                        <div class="text-2xl font-extrabold">24/7</div>
                        <div class="text-white/50 text-xs mt-1">Live Coverage</div>
                    </div>
                </div>

                <!-- Feature cards -->
                <div class="space-y-3">
                    <div class="glass-card rounded-2xl p-4 flex items-center gap-4 group hover:bg-white/10 transition cursor-default">
                        <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-emerald-400 to-teal-500 flex items-center justify-center shrink-0 shadow-lg shadow-emerald-500/25">
                            <i class="fa-solid fa-check text-white text-sm"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-sm">Free to Get Started</h3>
                            <p class="text-white/50 text-xs mt-0.5">No credit card required — sign up in seconds</p>
                        </div>
                    </div>

                    <div class="glass-card rounded-2xl p-4 flex items-center gap-4 group hover:bg-white/10 transition cursor-default">
                        <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-blue-400 to-indigo-500 flex items-center justify-center shrink-0 shadow-lg shadow-blue-500/25">
                            <i class="fa-solid fa-bell text-white text-sm"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-sm">Personalized Feed</h3>
                            <p class="text-white/50 text-xs mt-0.5">Follow topics that matter to you most</p>
                        </div>
                    </div>

                    <div class="glass-card rounded-2xl p-4 flex items-center gap-4 group hover:bg-white/10 transition cursor-default">
                        <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center shrink-0 shadow-lg shadow-amber-500/25">
                            <i class="fa-solid fa-crown text-white text-sm"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-sm">Upgrade Anytime</h3>
                            <p class="text-white/50 text-xs mt-0.5">Unlock premium content whenever you're ready</p>
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
        <!-- RIGHT SIDE — Registration Form                 -->
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

            <div class="w-full max-w-[440px] fade-in">

                <!-- Heading -->
                <div class="mb-6">
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">Create your account</h2>
                    <p class="text-gray-400 mt-2 text-sm">Join Nova News and start exploring today</p>
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

                <!-- Registration Form -->
                <form id="register-form" class="space-y-4" method="post" action="register-action.php" onsubmit="return validateForm()">

                    <!-- Full Name -->
                    <div>
                        <label class="block mb-1.5 text-sm font-semibold text-gray-700">Full Name</label>
                        <div class="relative">
                            <i class="fa-regular fa-user absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-300 text-sm"></i>
                            <input
                                type="text" name="name"
                                value="<?php echo htmlspecialchars($name); ?>"
                                placeholder="John Doe"
                                class="input-focus w-full h-11 rounded-xl border border-gray-200 bg-gray-50/50 pl-10 pr-4 text-sm text-gray-900 placeholder:text-gray-300 focus:outline-none focus:bg-white">
                        </div>
                    </div>

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
                                placeholder="Create a password"
                                oninput="updateStrength()"
                                class="input-focus w-full h-11 rounded-xl border border-gray-200 bg-gray-50/50 pl-10 pr-11 text-sm text-gray-900 placeholder:text-gray-300 focus:outline-none focus:bg-white">
                            <button type="button" onclick="togglePassword('password-input', 'eye-icon-1')" class="password-toggle absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-300 hover:text-nova-primary transition text-sm">
                                <i id="eye-icon-1" class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                        <!-- Strength meter -->
                        <div class="mt-2 flex items-center gap-2">
                            <div class="flex-1 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                <div id="strength-bar" class="strength-bar h-full w-0 bg-gray-300 rounded-full"></div>
                            </div>
                            <span id="strength-label" class="text-[11px] font-medium text-gray-400 w-16 text-right"></span>
                        </div>
                        <p class="text-[11px] text-gray-400 mt-1.5">
                            Must be at least 6 characters with a mix of letters, numbers & symbols.
                        </p>
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label class="block mb-1.5 text-sm font-semibold text-gray-700">Confirm Password</label>
                        <div class="relative">
                            <i class="fa-solid fa-lock absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-300 text-sm"></i>
                            <input
                                id="confirm-input"
                                type="password" name="confirm_password"
                                placeholder="Re-enter your password"
                                class="input-focus w-full h-11 rounded-xl border border-gray-200 bg-gray-50/50 pl-10 pr-11 text-sm text-gray-900 placeholder:text-gray-300 focus:outline-none focus:bg-white">
                            <button type="button" onclick="togglePassword('confirm-input', 'eye-icon-2')" class="password-toggle absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-300 hover:text-nova-primary transition text-sm">
                                <i id="eye-icon-2" class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                        <p id="match-warning" class="text-[11px] text-red-500 mt-1.5 hidden">
                            <i class="fa-solid fa-circle-exclamation mr-1"></i> Passwords do not match
                        </p>
                    </div>

                    <!-- Terms & Conditions -->
                    <div class="pt-1">
                        <label class="flex items-start gap-3 cursor-pointer group">
                            <input
                                type="checkbox" name="terms" value="1"
                                class="check-input w-4 h-4 mt-0.5 rounded border-gray-300 text-nova-primary focus:ring-nova-primary/30 cursor-pointer shrink-0">
                            <span class="text-sm text-gray-500 leading-snug">
                                I agree to the
                                <a href="#" class="font-semibold text-nova-primary hover:text-nova-accent transition">Terms of Service</a>
                                and
                                <a href="#" class="font-semibold text-nova-primary hover:text-nova-accent transition">Privacy Policy</a>
                            </span>
                        </label>
                        <p id="terms-warning" class="text-[11px] text-red-500 mt-1.5 ml-7 hidden">
                            <i class="fa-solid fa-circle-exclamation mr-1"></i> You must agree to the terms
                        </p>
                    </div>

                    <!-- Sign Up Button -->
                    <button
                        type="submit"
                        id="submit-btn"
                        class="w-full h-12 rounded-xl bg-gradient-to-r from-nova-primary to-nova-accent text-white font-bold text-sm hover:shadow-lg hover:shadow-nova-primary/30 hover:-translate-y-0.5 active:translate-y-0 transition-all duration-200 flex items-center justify-center gap-2">
                        <span id="btn-text">Create Account</span>
                        <i id="btn-spinner" class="fa-solid fa-spinner fa-spin hidden"></i>
                    </button>

                </form>

                <!-- Sign in link -->
                <p class="text-center text-sm text-gray-400 mt-6">
                    Already have an account?
                    <a href="signin.php" class="font-bold text-nova-primary hover:text-nova-accent transition ml-1">Sign in</a>
                </p>

            </div>
        </div>

    </div>

    <script>
        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'fa-regular fa-eye-slash';
            } else {
                input.type = 'password';
                icon.className = 'fa-regular fa-eye';
            }
        }

        function updateStrength() {
            const pw = document.getElementById('password-input').value;
            const bar = document.getElementById('strength-bar');
            const label = document.getElementById('strength-label');
            let score = 0;

            if (pw.length >= 6) score++;
            if (pw.length >= 10) score++;
            if (/[A-Z]/.test(pw)) score++;
            if (/[0-9]/.test(pw)) score++;
            if (/[^A-Za-z0-9]/.test(pw)) score++;

            const levels = [
                { w: '0%', bg: 'bg-gray-300', text: '' },
                { w: '20%', bg: 'bg-red-400', text: 'Weak' },
                { w: '40%', bg: 'bg-orange-400', text: 'Fair' },
                { w: '60%', bg: 'bg-yellow-400', text: 'Good' },
                { w: '80%', bg: 'bg-emerald-400', text: 'Strong' },
                { w: '100%', bg: 'bg-emerald-500', text: 'Very Strong' },
            ];

            const level = levels[score];
            bar.className = 'strength-bar h-full rounded-full ' + level.bg;
            bar.style.width = level.w;
            label.textContent = level.text;
        }

        function validateForm() {
            let valid = true;

            // Check terms checkbox
            const terms = document.querySelector('input[name="terms"]');
            const termsWarning = document.getElementById('terms-warning');
            if (!terms.checked) {
                termsWarning.classList.remove('hidden');
                valid = false;
            } else {
                termsWarning.classList.add('hidden');
            }

            // Check password match
            const pw = document.getElementById('password-input').value;
            const cpw = document.getElementById('confirm-input').value;
            const matchWarning = document.getElementById('match-warning');
            if (pw !== cpw || cpw === '') {
                matchWarning.classList.remove('hidden');
                valid = false;
            } else {
                matchWarning.classList.add('hidden');
            }

            // Show spinner
            if (valid) {
                document.getElementById('btn-text').textContent = 'Creating account...';
                document.getElementById('btn-spinner').classList.remove('hidden');
                document.getElementById('submit-btn').disabled = true;
            }

            return valid;
        }

        // Live password match check
        document.getElementById('confirm-input').addEventListener('input', function () {
            const pw = document.getElementById('password-input').value;
            const matchWarning = document.getElementById('match-warning');
            if (this.value && this.value !== pw) {
                matchWarning.classList.remove('hidden');
            } else {
                matchWarning.classList.add('hidden');
            }
        });
    </script>

</body>
</html>
