<?php
session_start();
include_once "./assec/header.php";
require_once './classes/database.php';
require_once './classes/Users.php';

$db = new Database();
$users = new Users($db->getConnection());

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $users->Name = htmlspecialchars($_POST['name']);
    $users->login = htmlspecialchars($_POST['login']);
    $users->password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $users->nomer = !empty($_POST['phone']) ? htmlspecialchars($_POST['phone']) : null; // Необязательное поле
    $users->admin = 0; // Добавляем значение по умолчанию
    $users->img = '';  // Пустое значение по умолчанию

    // Проверка уникальности логина
    $check = $db->getConnection()->prepare("SELECT id FROM Users WHERE login = ?");
    $check->execute([$users->login]);

    if ($check->rowCount() > 0) {
        $error = 'Логин уже занят';
    } else {
        if ($users->add()) {
            $query = "SELECT id FROM Users WHERE login = ?";
            $id_user = $db->getConnection()->prepare($query)->execute([$users->login])["id"];
            $_SESSION["user"] = $users->getByID($id_user);
            header('Location: profile.php');
            exit;
        } else {
            $error = 'Ошибка регистрации';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <title>Регистрация</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container mt-5" style="max-width: 400px; padding-bottom: 200px;">
        <div class="card shadow">
            <div class="card-body">
                <h2 class="card-title text-center mb-4">Регистрация</h2>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <input type="text" name="name" class="form-control" placeholder="Имя" required>
                    </div>
                    <div class="mb-3">
                        <input type="text" name="login" class="form-control" placeholder="Логин" required>
                    </div>
                    <div class="mb-3">
                        <input type="password" name="password" class="form-control" placeholder="Пароль" required>
                    </div>
                    <div class="mb-3">
                        <input type="tel" name="phone" class="form-control" placeholder="Телефон (необязательно)">
                        <div class="form-text text-muted mt-1">Номер телефона можно добавить позже</div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Зарегистрироваться</button>
                </form>

                <div class="mt-3 text-center">
                    Уже есть аккаунт? <a href="./Vhod.php">Войти</a>
                </div>
            </div>
        </div>
    </div>
</body>

</html>

<?php
include_once "./assec/footer.php";
?>