<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/subscription.php';

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

/**
 * Check if the current user has an active paid subscription.
 */
function hasActiveSubscription(): bool
{
    if (!isLoggedIn() || isAdmin()) return false;
    return getActiveSubscription(currentUserId()) !== null;
}

/**
 * Require an active subscription. Shows a "pending approval" page if subscription
 * exists but is not yet paid, or redirects to subscribe page if no subscription.
 */
function requireSubscription(): void
{
    if (!isLoggedIn()) {
        header('Location: /Nova_News/public/signin.php');
        exit;
    }

    if (isAdmin()) return;

    if (hasActiveSubscription()) return;

    $sub = getUserSubscription(currentUserId());
    if ($sub && $sub['payment_status'] === 'pending') {
        include __DIR__ . '/pending-approval.php';
        exit;
    }

    header('Location: /Nova_News/user/subscribe.php');
    exit;
}
