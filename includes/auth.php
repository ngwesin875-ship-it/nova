<?php
require_once __DIR__ . '/../config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!function_exists('isLoggedIn')) {
    function isLoggedIn(): bool
    {
        return !empty($_SESSION['user_id']);
    }
}

if (!function_exists('currentUserId')) {
    function currentUserId(): ?int
    {
        return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
    }
}

if (!function_exists('currentUserName')) {
    function currentUserName(): ?string
    {
        return $_SESSION['username'] ?? null;
    }
}

if (!function_exists('isAdmin')) {
    function isAdmin(): bool
    {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }
}

if (!function_exists('getAllUsers')) {
    function getAllUsers(int $page = 1, int $limit = 10): array
    {
        $db = getDB();
        $page = max(1, $page);
        $limit = max(1, $limit);
        $offset = ($page - 1) * $limit;

        $query = "SELECT u.*, COALESCE(MAX(CASE WHEN us.payment_status = 'paid' THEN us.status ELSE NULL END), 'inactive') AS sub_status
                  FROM users u
                  LEFT JOIN user_subscriptions us ON us.user_id = u.id
                  GROUP BY u.id
                  ORDER BY u.created_at DESC
                  LIMIT $limit OFFSET $offset";

        $result = $db->query($query);
        $users = [];

        if ($result && method_exists($result, 'fetch_assoc')) {
            while ($row = $result->fetch_assoc()) {
                $users[] = $row;
            }
        }

        return ['users' => $users, 'page' => $page, 'limit' => $limit, 'total' => count($users)];
    }
}

if (!function_exists('getUserById')) {
    function getUserById(int $id): ?array
    {
        $db = getDB();
        $stmt = $db->prepare('SELECT * FROM users WHERE id = ?');
        if ($stmt) {
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result && $result->num_rows > 0) {
                return $result->fetch_assoc();
            }
        }
        return null;
    }
}

if (!function_exists('createUser')) {
    function createUser(string $username, string $email, string $password, string $role = 'user', ?string $avatar = null): int|false
    {
        $db = getDB();
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $db->prepare('INSERT INTO users (username, email, password, role, avatar) VALUES (?, ?, ?, ?, ?)');
        if ($stmt) {
            $stmt->bind_param('sssss', $username, $email, $hash, $role, $avatar);
            if ($stmt->execute()) {
                return $db->insert_id;
            }
        }
        return false;
    }
}

if (!function_exists('updateUser')) {
    function updateUser(int $id, string $username, string $email, ?string $password, string $role, ?string $avatar): bool
    {
        $db = getDB();
        if ($password !== null && $password !== '') {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare('UPDATE users SET username = ?, email = ?, password = ?, role = ?, avatar = ? WHERE id = ?');
            if ($stmt) {
                $stmt->bind_param('sssssi', $username, $email, $hash, $role, $avatar, $id);
                return $stmt->execute();
            }
        } else {
            $stmt = $db->prepare('UPDATE users SET username = ?, email = ?, role = ?, avatar = ? WHERE id = ?');
            if ($stmt) {
                $stmt->bind_param('ssssi', $username, $email, $role, $avatar, $id);
                return $stmt->execute();
            }
        }
        return false;
    }
}

if (!function_exists('deleteUser')) {
    function deleteUser(int $id): bool
    {
        $db = getDB();
        $stmt = $db->prepare('DELETE FROM users WHERE id = ?');
        if ($stmt) {
            $stmt->bind_param('i', $id);
            return $stmt->execute();
        }
        return false;
    }
}

if (!function_exists('getUsersCount')) {
    function getUsersCount(string $search = ''): int
    {
        $db = getDB();
        if ($search !== '') {
            $like = '%' . $search . '%';
            $stmt = $db->prepare('SELECT COUNT(*) AS total FROM users WHERE username LIKE ? OR email LIKE ?');
            if ($stmt) {
                $stmt->bind_param('ss', $like, $like);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result) {
                    $row = $result->fetch_assoc();
                    return (int) ($row['total'] ?? 0);
                }
            }
        } else {
            $result = $db->query('SELECT COUNT(*) AS total FROM users');
            if ($result) {
                $row = $result->fetch_assoc();
                return (int) ($row['total'] ?? 0);
            }
        }
        return 0;
    }
}

if (!function_exists('getUsersPaginated')) {
    function getUsersPaginated(int $page = 1, int $limit = 10, string $search = ''): array
    {
        $db = getDB();
        $page = max(1, $page);
        $limit = max(1, $limit);
        $offset = ($page - 1) * $limit;

        if ($search !== '') {
            $like = '%' . $search . '%';
            $stmt = $db->prepare('SELECT u.*, COALESCE(MAX(CASE WHEN us.payment_status = "paid" THEN us.status ELSE NULL END), "inactive") AS sub_status FROM users u LEFT JOIN user_subscriptions us ON us.user_id = u.id WHERE u.username LIKE ? OR u.email LIKE ? GROUP BY u.id ORDER BY u.created_at DESC LIMIT ? OFFSET ?');
            if ($stmt) {
                $stmt->bind_param('ssii', $like, $like, $limit, $offset);
                $stmt->execute();
                $result = $stmt->get_result();
                $users = [];
                if ($result && method_exists($result, 'fetch_assoc')) {
                    while ($row = $result->fetch_assoc()) {
                        $users[] = $row;
                    }
                }
                return $users;
            }
        } else {
            $stmt = $db->prepare('SELECT u.*, COALESCE(MAX(CASE WHEN us.payment_status = "paid" THEN us.status ELSE NULL END), "inactive") AS sub_status FROM users u LEFT JOIN user_subscriptions us ON us.user_id = u.id GROUP BY u.id ORDER BY u.created_at DESC LIMIT ? OFFSET ?');
            if ($stmt) {
                $stmt->bind_param('ii', $limit, $offset);
                $stmt->execute();
                $result = $stmt->get_result();
                $users = [];
                if ($result && method_exists($result, 'fetch_assoc')) {
                    while ($row = $result->fetch_assoc()) {
                        $users[] = $row;
                    }
                }
                return $users;
            }
        }
        return [];
    }
}

const AVATAR_UPLOAD_DIR = __DIR__ . '/../uploads/avatars/';
const ALLOWED_AVATAR_TYPES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

if (!function_exists('uploadAvatar')) {
    function uploadAvatar(array $file): string|false
    {
        if ($file['error'] !== UPLOAD_ERR_OK) return false;
        if (!in_array($file['type'], ALLOWED_AVATAR_TYPES)) return false;
        if ($file['size'] > 2 * 1024 * 1024) return false;

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = bin2hex(random_bytes(16)) . '.' . $ext;
        $dest = AVATAR_UPLOAD_DIR . $filename;

        if (!is_dir(AVATAR_UPLOAD_DIR)) {
            mkdir(AVATAR_UPLOAD_DIR, 0755, true);
        }

        if (move_uploaded_file($file['tmp_name'], $dest)) {
            return 'uploads/avatars/' . $filename;
        }
        return false;
    }
}

if (!function_exists('deleteAvatar')) {
    function deleteAvatar(string $path): void
    {
        if ($path) {
            $full = __DIR__ . '/../' . $path;
            if (file_exists($full)) {
                @unlink($full);
            }
        }
    }
}
