<?php
require_once __DIR__ . '/../config/session.php';

/**
 * Redirect unauthenticated users to the sign-in page.
 */
function requireLogin(): void
{
    if (!isLoggedIn()) {
        header('Location: /Nova_News/public/signin.php');
        exit;
    }
}

/**
 * Redirect non-admin users away from admin-only pages.
 */
function requireAdmin(): void
{
    if (!isLoggedIn()) {
        header('Location: /Nova_News/public/signin.php');
        exit;
    }

    if (!isAdmin()) {
        header('Location: /Nova_News/public/index.php');
        exit;
    }
}
