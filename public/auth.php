<?php
require_once __DIR__ . '/../config/db.php';
session_start();

$errorMessage = '';
$successMessage = $_GET['success'] ?? '';
$email = '';
$name = '';
$activeTab = $_GET['tab'] === 'register' ? 'register' : 'signin';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['form_action'] ?? '';

    if ($action === 'signin') {
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
                    if (password_verify($password, $user['password']) || $user['password'] === $password) {
                        $_SESSION['user_id'] = (int) $user['id'];
                        $_SESSION['username'] = $user['username'];
                        $_SESSION['role'] = $user['role'] ?? 'user';
                        header('Location: ' . (($user['role'] ?? 'user') === 'admin' ? '../admin/index.php' : '../user/index.php'));
                        exit;
                    } else {
                        $errorMessage = 'Incorrect email or password.';
                    }
                } else {
                    $errorMessage = 'No account found with that email.';
                }
                $stmt->close();
            } else {
                $errorMessage = 'Database error. Please try again later.';
            }
        }
        $activeTab = 'signin';
    } elseif ($action === 'register') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        $terms = $_POST['terms'] ?? '';

        if ($name === '' || $email === '' || $password === '') {
            $errorMessage = 'All fields are required.';
            $activeTab = 'register';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errorMessage = 'Please enter a valid email address.';
            $activeTab = 'register';
        } elseif (strlen($password) < 6) {
            $errorMessage = 'Password must be at least 6 characters.';
            $activeTab = 'register';
        } elseif ($password !== $confirm) {
            $errorMessage = 'Passwords do not match.';
            $activeTab = 'register';
        } elseif (!$terms) {
            $errorMessage = 'You must agree to the terms.';
            $activeTab = 'register';
        } else {
            $db = getDB();
            $check = $db->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
            $check->bind_param('s', $email);
            $check->execute();
            if ($check->get_result()->num_rows > 0) {
                $errorMessage = 'An account with that email already exists.';
                $activeTab = 'register';
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $db->prepare('INSERT INTO users (username, email, password, role, created_at) VALUES (?, ?, ?, ?, NOW())');
                $stmt->bind_param('sss', $name, $email, $hashedPassword);
                if ($stmt->execute()) {
                    header('Location: auth.php?tab=signin&success=' . urlencode('Account created! Please sign in.'));
                    exit;
                } else {
                    $errorMessage = 'Registration failed. Please try again.';
                    $activeTab = 'register';
                }
                $stmt->close();
            }
            $check->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova News — Sign In / Register</title>

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
                            dark: '#0a0a12',
                            deeper: '#060609',
                            card: 'rgba(15, 15, 25, 0.65)',
                            primary: '#00d4ff',
                            accent: '#7c3aed',
                            glow: 'rgba(0, 212, 255, 0.15)',
                        }
                    }
                }
            }
        }
    </script>

    <style>
        :root {
            --cyan: #00d4ff;
            --violet: #7c3aed;
            --card-bg: rgba(15, 15, 25, 0.65);
            --glass-border: rgba(255, 255, 255, 0.08);
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: #060609;
            overflow-x: hidden;
        }

        /* ── Animated mesh background ── */
        .mesh-bg {
            position: fixed; inset: 0; z-index: 0;
            background:
                radial-gradient(ellipse 80% 60% at 20% 30%, rgba(0,212,255,0.08) 0%, transparent 60%),
                radial-gradient(ellipse 70% 50% at 80% 70%, rgba(124,58,237,0.07) 0%, transparent 60%),
                radial-gradient(ellipse 50% 40% at 50% 50%, rgba(0,212,255,0.04) 0%, transparent 50%);
            animation: meshShift 20s ease-in-out infinite alternate;
        }
        @keyframes meshShift {
            0%   { background-position: 0% 0%, 100% 100%, 50% 50%; }
            100% { background-position: 100% 100%, 0% 0%, 50% 50%; }
        }

        .mesh-orb {
            position: absolute; border-radius: 50%; filter: blur(80px); pointer-events: none;
        }
        .orb-1 {
            width: 500px; height: 500px; top: -10%; left: -5%;
            background: radial-gradient(circle, rgba(0,212,255,0.12), transparent 70%);
            animation: orbFloat1 18s ease-in-out infinite alternate;
        }
        .orb-2 {
            width: 400px; height: 400px; bottom: -10%; right: -5%;
            background: radial-gradient(circle, rgba(124,58,237,0.10), transparent 70%);
            animation: orbFloat2 22s ease-in-out infinite alternate;
        }
        .orb-3 {
            width: 300px; height: 300px; top: 50%; left: 60%;
            background: radial-gradient(circle, rgba(0,212,255,0.06), transparent 70%);
            animation: orbFloat3 15s ease-in-out infinite alternate;
        }
        @keyframes orbFloat1 {
            0%   { transform: translate(0, 0) scale(1); }
            100% { transform: translate(60px, 40px) scale(1.15); }
        }
        @keyframes orbFloat2 {
            0%   { transform: translate(0, 0) scale(1); }
            100% { transform: translate(-50px, -30px) scale(1.1); }
        }
        @keyframes orbFloat3 {
            0%   { transform: translate(0, 0) scale(1); }
            100% { transform: translate(30px, -40px) scale(1.08); }
        }

        /* ── Glass card ── */
        .glass-card {
            background: var(--card-bg);
            backdrop-filter: blur(24px) saturate(1.2);
            -webkit-backdrop-filter: blur(24px) saturate(1.2);
            border: 1px solid var(--glass-border);
            box-shadow:
                0 0 0 1px rgba(255,255,255,0.03),
                0 8px 32px rgba(0,0,0,0.4),
                inset 0 1px 0 rgba(255,255,255,0.05);
        }

        /* ── Input styling ── */
        .auth-input {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.08);
            color: #e2e8f0;
            transition: all 0.2s ease;
        }
        .auth-input::placeholder { color: rgba(255,255,255,0.25); }
        .auth-input:focus {
            outline: none;
            border-color: var(--cyan);
            box-shadow: 0 0 0 3px rgba(0,212,255,0.12), 0 0 20px rgba(0,212,255,0.06);
            background: rgba(255,255,255,0.06);
        }

        /* ── Tab buttons ── */
        .tab-btn {
            transition: all 0.25s ease;
            position: relative;
        }
        .tab-btn.active {
            color: #00d4ff;
            background: rgba(0,212,255,0.08);
            border-color: rgba(0,212,255,0.2);
        }
        .tab-btn:not(.active):hover {
            background: rgba(255,255,255,0.04);
        }

        /* ── Strength meter ── */
        .strength-track {
            height: 4px;
            background: rgba(255,255,255,0.06);
            border-radius: 4px;
            overflow: hidden;
        }
        .strength-fill {
            height: 100%;
            border-radius: 4px;
            transition: width 0.35s ease, background-color 0.35s ease;
        }

        /* ── Submit button ── */
        .submit-btn {
            background: linear-gradient(135deg, #00d4ff 0%, #7c3aed 100%);
            transition: all 0.25s ease;
            position: relative;
            overflow: hidden;
        }
        .submit-btn::before {
            content: '';
            position: absolute; inset: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.15) 0%, transparent 50%);
            opacity: 0;
            transition: opacity 0.25s ease;
        }
        .submit-btn:hover::before { opacity: 1; }
        .submit-btn:hover {
            box-shadow: 0 0 30px rgba(0,212,255,0.25), 0 0 60px rgba(124,58,237,0.15);
            transform: translateY(-1px);
        }
        .submit-btn:active { transform: translateY(0); }

        /* ── Checkbox ── */
        .auth-check {
            appearance: none;
            width: 18px; height: 18px;
            border: 1.5px solid rgba(255,255,255,0.15);
            border-radius: 5px;
            background: rgba(255,255,255,0.04);
            cursor: pointer;
            transition: all 0.2s ease;
            position: relative;
            flex-shrink: 0;
        }
        .auth-check:checked {
            background: var(--cyan);
            border-color: var(--cyan);
        }
        .auth-check:checked::after {
            content: '';
            position: absolute;
            left: 5px; top: 2px;
            width: 5px; height: 9px;
            border: solid #060609;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }

        /* ── Panel transitions ── */
        .auth-panel {
            animation: panelIn 0.35s ease forwards;
        }
        @keyframes panelIn {
            from { opacity: 0; transform: translateY(8px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .toggle-icon { transition: all 0.2s ease; }

        /* ── Reduced motion ── */
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: 0.01ms !important;
                transition-duration: 0.01ms !important;
            }
        }

        /* ── Scrollbar ── */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 3px; }
    </style>
</head>

<body class="min-h-screen bg-nova-deeper text-slate-200 flex items-center justify-center">

    <!-- Animated background -->
    <div class="mesh-bg" aria-hidden="true">
        <div class="mesh-orb orb-1"></div>
        <div class="mesh-orb orb-2"></div>
        <div class="mesh-orb orb-3"></div>
    </div>

    <!-- ── Card ── -->
    <div class="relative z-10 w-full max-w-md mx-4 sm:mx-6">

        <!-- Logo -->
        <a href="index.php" class="flex items-center gap-2.5 mb-8 justify-center group">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, rgba(0,212,255,0.15), rgba(124,58,237,0.15)); border: 1px solid rgba(255,255,255,0.08);">
                <i class="fa-solid fa-bolt text-nova-primary text-lg"></i>
            </div>
            <span class="text-xl font-extrabold tracking-tight text-white">NOVA<span class="text-nova-primary">NEWS</span></span>
        </a>

        <!-- Glass card -->
        <div class="glass-card rounded-2xl p-6 sm:p-8">

            <!-- Tab switcher -->
            <div class="flex gap-2 p-1 rounded-xl mb-6" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05);">
                <button type="button"
                    onclick="switchTab('signin')"
                    id="tab-signin"
                    class="tab-btn flex-1 py-2.5 rounded-lg text-sm font-semibold border border-transparent <?php echo $activeTab === 'signin' ? 'active' : 'text-white/40'; ?>">
                    Sign In
                </button>
                <button type="button"
                    onclick="switchTab('register')"
                    id="tab-register"
                    class="tab-btn flex-1 py-2.5 rounded-lg text-sm font-semibold border border-transparent <?php echo $activeTab === 'register' ? 'active' : 'text-white/40'; ?>">
                    Register
                </button>
            </div>

            <!-- Error message -->
            <?php if ($errorMessage): ?>
            <div class="mb-5 rounded-xl px-4 py-3 text-sm flex items-center gap-2.5 auth-panel" style="background: rgba(239,68,68,0.08); border: 1px solid rgba(239,68,68,0.15); color: #fca5a5;">
                <i class="fa-solid fa-circle-exclamation text-red-400"></i>
                <?php echo htmlspecialchars($errorMessage); ?>
            </div>
            <?php endif; ?>

            <!-- Success message -->
            <?php if ($successMessage): ?>
            <div class="mb-5 rounded-xl px-4 py-3 text-sm flex items-center gap-2.5 auth-panel" style="background: rgba(34,197,94,0.08); border: 1px solid rgba(34,197,94,0.15); color: #86efac;">
                <i class="fa-solid fa-circle-check text-emerald-400"></i>
                <?php echo htmlspecialchars($successMessage); ?>
            </div>
            <?php endif; ?>

            <!-- ═══════════════════════════════════════════ -->
            <!-- SIGN IN PANEL                               -->
            <!-- ═══════════════════════════════════════════ -->
            <div id="panel-signin" class="<?php echo $activeTab !== 'signin' ? 'hidden' : ''; ?>">
                <form method="post" action="" class="space-y-4">
                    <input type="hidden" name="form_action" value="signin">

                    <div class="mb-2">
                        <h2 class="text-xl font-bold text-white">Welcome back</h2>
                        <p class="text-sm text-white/40 mt-1">Sign in to continue reading</p>
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block mb-1.5 text-xs font-semibold text-white/50 uppercase tracking-wider">Email</label>
                        <div class="relative">
                            <i class="fa-regular fa-envelope absolute left-3.5 top-1/2 -translate-y-1/2 text-white/20 text-sm"></i>
                            <input type="email" name="email"
                                value="<?php echo htmlspecialchars($email); ?>"
                                placeholder="you@example.com"
                                class="auth-input w-full h-11 rounded-xl pl-10 pr-4 text-sm">
                        </div>
                    </div>

                    <!-- Password -->
                    <div>
                        <label class="block mb-1.5 text-xs font-semibold text-white/50 uppercase tracking-wider">Password</label>
                        <div class="relative">
                            <i class="fa-solid fa-lock absolute left-3.5 top-1/2 -translate-y-1/2 text-white/20 text-sm"></i>
                            <input id="signin-pw" type="password" name="password"
                                placeholder="Enter your password"
                                class="auth-input w-full h-11 rounded-xl pl-10 pr-11 text-sm">
                            <button type="button" onclick="togglePw('signin-pw','signin-eye')"
                                class="absolute right-3.5 top-1/2 -translate-y-1/2 text-white/20 hover:text-nova-primary transition text-sm">
                                <i id="signin-eye" class="fa-regular fa-eye toggle-icon"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Remember / Forgot -->
                    <div class="flex items-center justify-between pt-0.5">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="remember" class="auth-check">
                            <span class="text-sm text-white/40">Remember me</span>
                        </label>
                        <a href="#" class="text-xs font-semibold text-nova-primary hover:text-cyan-300 transition">Forgot password?</a>
                    </div>

                    <button type="submit" class="submit-btn w-full h-12 rounded-xl text-white font-bold text-sm mt-2">
                        Sign In
                    </button>
                </form>
            </div>

            <!-- ═══════════════════════════════════════════ -->
            <!-- REGISTER PANEL                             -->
            <!-- ═══════════════════════════════════════════ -->
            <div id="panel-register" class="<?php echo $activeTab !== 'register' ? 'hidden' : ''; ?>">
                <form method="post" action="" id="register-form" class="space-y-4" onsubmit="return validateRegister()">
                    <input type="hidden" name="form_action" value="register">

                    <div class="mb-2">
                        <h2 class="text-xl font-bold text-white">Create account</h2>
                        <p class="text-sm text-white/40 mt-1">Join Nova News today</p>
                    </div>

                    <!-- Full Name -->
                    <div>
                        <label class="block mb-1.5 text-xs font-semibold text-white/50 uppercase tracking-wider">Full Name</label>
                        <div class="relative">
                            <i class="fa-regular fa-user absolute left-3.5 top-1/2 -translate-y-1/2 text-white/20 text-sm"></i>
                            <input type="text" name="name"
                                value="<?php echo htmlspecialchars($name); ?>"
                                placeholder="John Doe"
                                class="auth-input w-full h-11 rounded-xl pl-10 pr-4 text-sm">
                        </div>
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block mb-1.5 text-xs font-semibold text-white/50 uppercase tracking-wider">Email</label>
                        <div class="relative">
                            <i class="fa-regular fa-envelope absolute left-3.5 top-1/2 -translate-y-1/2 text-white/20 text-sm"></i>
                            <input type="email" name="email"
                                value="<?php echo htmlspecialchars($email); ?>"
                                placeholder="you@example.com"
                                class="auth-input w-full h-11 rounded-xl pl-10 pr-4 text-sm">
                        </div>
                    </div>

                    <!-- Password -->
                    <div>
                        <label class="block mb-1.5 text-xs font-semibold text-white/50 uppercase tracking-wider">Password</label>
                        <div class="relative">
                            <i class="fa-solid fa-lock absolute left-3.5 top-1/2 -translate-y-1/2 text-white/20 text-sm"></i>
                            <input id="reg-pw" type="password" name="password"
                                placeholder="Min 6 characters"
                                oninput="checkStrength()"
                                class="auth-input w-full h-11 rounded-xl pl-10 pr-11 text-sm">
                            <button type="button" onclick="togglePw('reg-pw','reg-eye')"
                                class="absolute right-3.5 top-1/2 -translate-y-1/2 text-white/20 hover:text-nova-primary transition text-sm">
                                <i id="reg-eye" class="fa-regular fa-eye toggle-icon"></i>
                            </button>
                        </div>
                        <!-- Strength meter -->
                        <div class="mt-2 flex items-center gap-2.5">
                            <div class="strength-track flex-1">
                                <div id="str-bar" class="strength-fill" style="width:0%"></div>
                            </div>
                            <span id="str-label" class="text-[11px] font-medium text-white/30 min-w-[60px] text-right"></span>
                        </div>
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label class="block mb-1.5 text-xs font-semibold text-white/50 uppercase tracking-wider">Confirm Password</label>
                        <div class="relative">
                            <i class="fa-solid fa-lock absolute left-3.5 top-1/2 -translate-y-1/2 text-white/20 text-sm"></i>
                            <input id="reg-cpw" type="password" name="confirm_password"
                                placeholder="Re-enter password"
                                class="auth-input w-full h-11 rounded-xl pl-10 pr-11 text-sm">
                            <button type="button" onclick="togglePw('reg-cpw','reg-eye2')"
                                class="absolute right-3.5 top-1/2 -translate-y-1/2 text-white/20 hover:text-nova-primary transition text-sm">
                                <i id="reg-eye2" class="fa-regular fa-eye toggle-icon"></i>
                            </button>
                        </div>
                        <p id="pw-match-err" class="text-[11px] text-red-400 mt-1.5 hidden">
                            <i class="fa-solid fa-circle-exclamation mr-1"></i>Passwords do not match
                        </p>
                    </div>

                    <!-- Terms -->
                    <div class="pt-0.5">
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input type="checkbox" name="terms" value="1" class="auth-check mt-0.5">
                            <span class="text-xs text-white/35 leading-relaxed">
                                I agree to the
                                <a href="#" class="font-semibold text-nova-primary hover:text-cyan-300 transition">Terms</a>
                                and
                                <a href="#" class="font-semibold text-nova-primary hover:text-cyan-300 transition">Privacy Policy</a>
                            </span>
                        </label>
                        <p id="terms-err" class="text-[11px] text-red-400 mt-1.5 hidden">
                            <i class="fa-solid fa-circle-exclamation mr-1"></i>You must agree to the terms
                        </p>
                    </div>

                    <button type="submit" id="reg-submit"
                        class="submit-btn w-full h-12 rounded-xl text-white font-bold text-sm mt-2 flex items-center justify-center gap-2">
                        <span id="reg-btn-text">Create Account</span>
                        <i id="reg-spinner" class="fa-solid fa-spinner fa-spin hidden"></i>
                    </button>
                </form>
            </div>

        </div>

        <!-- Footer -->
        <p class="text-center text-xs text-white/20 mt-6">&copy; 2026 Nova News. All rights reserved.</p>
    </div>

    <script>
        /* ── Tab switching ── */
        function switchTab(tab) {
            document.getElementById('panel-signin').classList.toggle('hidden', tab !== 'signin');
            document.getElementById('panel-register').classList.toggle('hidden', tab !== 'register');

            document.getElementById('tab-signin').classList.toggle('active', tab === 'signin');
            document.getElementById('tab-signin').classList.toggle('text-white/40', tab !== 'signin');
            document.getElementById('tab-register').classList.toggle('active', tab === 'register');
            document.getElementById('tab-register').classList.toggle('text-white/40', tab !== 'register');

            // Re-trigger panel animation
            const panel = document.getElementById('panel-' + tab);
            panel.classList.remove('auth-panel');
            void panel.offsetWidth;
            panel.classList.add('auth-panel');
        }

        /* ── Password visibility toggle ── */
        function togglePw(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'fa-regular fa-eye-slash toggle-icon';
            } else {
                input.type = 'password';
                icon.className = 'fa-regular fa-eye toggle-icon';
            }
        }

        /* ── Password strength meter ── */
        function checkStrength() {
            const pw = document.getElementById('reg-pw').value;
            const bar = document.getElementById('str-bar');
            const label = document.getElementById('str-label');
            let score = 0;

            if (pw.length >= 6)  score++;
            if (pw.length >= 10) score++;
            if (/[A-Z]/.test(pw))  score++;
            if (/[0-9]/.test(pw))  score++;
            if (/[^A-Za-z0-9]/.test(pw)) score++;

            const levels = [
                { w: '0%',   color: 'transparent',               text: '' },
                { w: '20%',  color: '#ef4444',                   text: 'Weak' },
                { w: '40%',  color: '#f97316',                   text: 'Fair' },
                { w: '60%',  color: '#eab308',                   text: 'Good' },
                { w: '80%',  color: '#22c55e',                   text: 'Strong' },
                { w: '100%', color: '#00d4ff',                   text: 'Very Strong' },
            ];

            const level = levels[score];
            bar.style.width = level.w;
            bar.style.backgroundColor = level.color;
            label.textContent = level.text;
            label.style.color = level.color === 'transparent' ? 'rgba(255,255,255,0.3)' : level.color;
        }

        /* ── Live password match ── */
        document.getElementById('reg-cpw').addEventListener('input', function() {
            const pw = document.getElementById('reg-pw').value;
            const err = document.getElementById('pw-match-err');
            err.classList.toggle('hidden', !this.value || this.value === pw);
        });

        /* ── Form validation ── */
        function validateRegister() {
            let valid = true;

            const terms = document.querySelector('input[name="terms"]');
            const termsErr = document.getElementById('terms-err');
            if (!terms.checked) { termsErr.classList.remove('hidden'); valid = false; }
            else { termsErr.classList.add('hidden'); }

            const pw = document.getElementById('reg-pw').value;
            const cpw = document.getElementById('reg-cpw').value;
            const matchErr = document.getElementById('pw-match-err');
            if (pw !== cpw || cpw === '') { matchErr.classList.remove('hidden'); valid = false; }
            else { matchErr.classList.add('hidden'); }

            if (valid) {
                document.getElementById('reg-btn-text').textContent = 'Creating...';
                document.getElementById('reg-spinner').classList.remove('hidden');
                document.getElementById('reg-submit').disabled = true;
            }
            return valid;
        }
    </script>
</body>
</html>
