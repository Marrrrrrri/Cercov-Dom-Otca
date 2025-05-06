<?php
session_start();
include_once "./assec/header.php";
require_once './classes/database.php';
require_once './classes/Users.php';
?>

<?php


$db = new Database();
$users = new Users($db->getConnection());

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = htmlspecialchars($_POST['login']);
    $password = $_POST['password'];

    $user = $db->getConnection()->prepare("SELECT * FROM Users WHERE login = ?");
    $user->execute([$login]);
    $user = $user->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = $user;
        header('Location: profile.php');
        exit;
    } else {
        $error = 'Неверный логин или пароль';
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Вход</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5" style="max-width: 400px; padding-bottom: 200px;">
    <div class="card shadow">
        <div class="card-body">
            <h2 class="card-title text-center mb-4">Вход</h2>
            
            <?php if($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <input type="text" name="login" class="form-control" placeholder="Логин" required>
                </div>
                <div class="mb-3">
                    <input type="password" name="password" class="form-control" placeholder="Пароль" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Войти</button>
            </form>
            
            <div class="mt-3 text-center">
                Нет аккаунта? <a href="register.php">Зарегистрироваться</a>
            </div>
        </div>
    </div>
</div>
</body>
</html>


<?php
include_once "./assec/footer.php";
?>