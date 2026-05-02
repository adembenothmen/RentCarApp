<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/db.php';

function is_admin_logged_in(): bool
{
    return !empty($_SESSION['admin_logged_in']);
}

function logout_admin(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }

    session_destroy();
    header('Location: ../client/login.html');
    exit;
}

function login_admin(PDO $db): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ../client/login.html');
        exit;
    }

    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    $stmt = $db->prepare('SELECT password FROM admin WHERE username = :username LIMIT 1');
    $stmt->execute(['username' => $username]);
    $storedPassword = (string)$stmt->fetchColumn();

    if ($username !== '' && $password !== '' && $storedPassword !== '' && hash_equals($storedPassword, $password)) {
        session_regenerate_id(true);
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        header('Location: ../client/dashboard.html');
        exit;
    }

    header('Location: ../client/login.html?error=' . urlencode('Invalid username or password.'));
    exit;
}




if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    if (($_GET['action'] ?? '') === 'logout') {
        logout_admin();
    }

    login_admin($db);
}
