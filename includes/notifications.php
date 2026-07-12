<?php
function getNotificationCounts(): array
{
    $db = getDB();
    $counts = ['pending_payments' => 0, 'draft_posts' => 0, 'new_users' => 0];

    $r = $db->query("SELECT COUNT(*) AS c FROM payments WHERE status = 'pending'");
    if ($r) $counts['pending_payments'] = (int) ($r->fetch_assoc()['c'] ?? 0);

    $r = $db->query("SELECT COUNT(*) AS c FROM posts WHERE status = 'draft'");
    if ($r) $counts['draft_posts'] = (int) ($r->fetch_assoc()['c'] ?? 0);

    $r = $db->query("SELECT COUNT(*) AS c FROM users WHERE created_at >= NOW() - INTERVAL 7 DAY");
    if ($r) $counts['new_users'] = (int) ($r->fetch_assoc()['c'] ?? 0);

    return $counts;
}
