<?php
// logout.php
session_start();

// Полная очистка сессии
$_SESSION = [];
session_unset();
session_destroy();

// Удаление куки сессии (если используется)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Редирект
header("Location: index.php");
exit();
