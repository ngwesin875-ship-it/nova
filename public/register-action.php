<?php
require_once __DIR__ . '/../config/db.php';
session_start();

// ── Only accept POST ──
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register.php');
    exit;
}

// ── Collect & sanitize inputs ──
$name            = trim($_POST['name'] ?? '');
$email           = trim($_POST['email'] ?? '');
$password        = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';
$terms           = $_POST['terms'] ?? '';

// ── Validation ──
$errors = [];

if ($name === '' || $email === '' || $password === '' || $confirmPassword === '') {
    $errors[] = 'Please fill in all required fields.';
}

if ($name !== '' && mb_strlen($name) < 2) {
    $errors[] = 'Name must be at least 2 characters long.';
}

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Please enter a valid email address.';
}

if (strlen($password) < 6) {
    $errors[] = 'Password must be at least 6 characters long.';
}

if ($password !== $confirmPassword) {
    $errors[] = 'Passwords do not match.';
}

if (empty($terms)) {
    $errors[] = 'You must agree to the Terms of Service.';
}

// ── If validation failed, redirect back with first error ──
if (!empty($errors)) {
    $params = http_build_query([
        'error'  => $errors[0],
        'name'   => $name,
        'email'  => $email,
    ]);
    header("Location: register.php?{$params}");
    exit;
}

// ── Database checks ──
$db = getDB();

// Check if email already exists
$checkStmt = $db->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
if ($checkStmt) {
    $checkStmt->bind_param('s', $email);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult && $checkResult->num_rows > 0) {
        $checkStmt->close();
        $params = http_build_query([
            'error' => 'An account with that email already exists.',
            'name'  => $name,
            'email' => $email,
        ]);
        header("Location: register.php?{$params}");
        exit;
    }
    $checkStmt->close();
}

// ── Create user ──
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

$role   = 'user';
$avatar = null;

$insertStmt = $db->prepare('INSERT INTO users (username, email, password, role, avatar) VALUES (?, ?, ?, ?, ?)');
if ($insertStmt) {
    $insertStmt->bind_param('sssss', $name, $email, $passwordHash, $role, $avatar);

    if ($insertStmt->execute()) {
        $insertStmt->close();

        // Redirect to sign-in with success message
        $params = http_build_query([
            'success' => 'Account created successfully! Please sign in.',
        ]);
        header("Location: signin.php?{$params}");
        exit;
    }
}

// ── Fallback error ──
$insertStmt->close();
$params = http_build_query([
    'error' => 'Something went wrong while creating your account. Please try again.',
    'name'  => $name,
    'email' => $email,
]);
header("Location: register.php?{$params}");
exit;
