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
        .strength-bar { transition: width 0.3s ease, background-color 0.3s ease; }
    </style>
</head>

<body class="bg-slate-100 min-h-screen flex items-center justify-center p-4 sm:p-6 font-sans box-border">

    <div class="box-border w-full max-w-md bg-white rounded-2xl shadow-xl shadow-slate-200/60 p-6 sm:p-8 flex flex-col gap-4 items-stretch border border-slate-100">
        
        <!-- Header & Logo -->
        <div class="flex flex-col items-center mb-4">
            <a href="index.php" class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-slate-900 text-white mb-6 hover:bg-blue-600 transition-colors shadow-sm">
                <i class="fa-solid fa-bolt text-xl"></i>
            </a>
            <h2 class="text-2xl font-bold text-slate-900 tracking-tight text-center">Create an account</h2>
            <p class="text-slate-500 mt-2 text-sm text-center">Join Nova News and start exploring today</p>
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

        <!-- Registration Form -->
        <form id="register-form" class="box-border flex flex-col gap-4 items-stretch" method="post" action="register-action.php" onsubmit="return validateForm()">
            
            <!-- Full Name -->
            <div class="box-border w-full">
                <label class="block mb-2 text-sm font-semibold text-slate-700">Full Name</label>
                <div class="relative box-border w-full">
                    <i class="fa-regular fa-user absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input
                        type="text" name="name"
                        value="<?php echo htmlspecialchars($name); ?>"
                        placeholder="John Doe"
                        class="box-border w-full h-12 rounded-xl border border-slate-300 bg-white pl-11 pr-4 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600 transition-shadow">
                </div>
            </div>

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
                        placeholder="Create a password"
                        oninput="updateStrength()"
                        class="box-border w-full h-12 rounded-xl border border-slate-300 bg-white pl-11 pr-12 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600 transition-shadow">
                    <button type="button" onclick="togglePassword('password-input', 'eye-icon-1')" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700 transition" title="Toggle password visibility">
                        <i id="eye-icon-1" class="fa-regular fa-eye"></i>
                    </button>
                </div>
                <!-- Strength meter -->
                <div class="mt-2.5 flex items-center gap-2">
                    <div class="flex-1 h-1.5 bg-slate-200 rounded-full overflow-hidden">
                        <div id="strength-bar" class="strength-bar h-full w-0 bg-slate-400 rounded-full"></div>
                    </div>
                    <span id="strength-label" class="text-[11px] font-medium text-slate-500 w-16 text-right"></span>
                </div>
            </div>

            <!-- Confirm Password -->
            <div class="box-border w-full">
                <label class="block mb-2 text-sm font-semibold text-slate-700">Confirm Password</label>
                <div class="relative box-border w-full">
                    <i class="fa-solid fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input
                        id="confirm-input"
                        type="password" name="confirm_password"
                        placeholder="Re-enter your password"
                        class="box-border w-full h-12 rounded-xl border border-slate-300 bg-white pl-11 pr-12 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600 transition-shadow">
                    <button type="button" onclick="togglePassword('confirm-input', 'eye-icon-2')" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700 transition" title="Toggle password visibility">
                        <i id="eye-icon-2" class="fa-regular fa-eye"></i>
                    </button>
                </div>
                <p id="match-warning" class="text-xs text-red-500 mt-2 hidden">
                    <i class="fa-solid fa-circle-exclamation mr-1"></i> Passwords do not match
                </p>
            </div>

            <!-- Terms & Conditions -->
            <div class="pt-2 box-border">
                <label class="flex items-start gap-3 cursor-pointer group">
                    <input
                        type="checkbox" name="terms" value="1"
                        class="box-border w-4 h-4 mt-0.5 rounded border-slate-300 text-blue-600 focus:ring-blue-600 cursor-pointer shrink-0 transition">
                    <span class="text-sm font-medium text-slate-600 leading-snug">
                        I agree to the
                        <a href="#" class="font-bold text-blue-600 hover:text-slate-900 transition-colors">Terms of Service</a>
                        and
                        <a href="#" class="font-bold text-blue-600 hover:text-slate-900 transition-colors">Privacy Policy</a>
                    </span>
                </label>
                <p id="terms-warning" class="text-xs text-red-500 mt-2 ml-7 hidden">
                    <i class="fa-solid fa-circle-exclamation mr-1"></i> You must agree to the terms
                </p>
            </div>

            <!-- Sign Up Button -->
            <button
                type="submit"
                id="submit-btn"
                class="box-border w-full h-12 mt-2 rounded-xl bg-blue-600 text-white font-semibold text-sm hover:bg-slate-900 transition-all duration-200 shadow-md hover:shadow-lg flex items-center justify-center gap-2">
                <span id="btn-text">Create Account</span>
                <i id="btn-spinner" class="fa-solid fa-spinner fa-spin hidden"></i>
            </button>
        </form>

        <!-- Footer Link -->
        <p class="text-center text-sm font-medium text-slate-500 mt-8">
            Already have an account? 
            <a href="signin.php" class="font-bold text-blue-600 hover:text-slate-900 transition-colors">Sign in</a>
        </p>

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
                { w: '0%', bg: 'bg-slate-300', text: '' },
                { w: '20%', bg: 'bg-red-400', text: 'Weak' },
                { w: '40%', bg: 'bg-orange-400', text: 'Fair' },
                { w: '60%', bg: 'bg-amber-400', text: 'Good' },
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
