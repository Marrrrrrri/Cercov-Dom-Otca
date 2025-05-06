<?php
session_start();
include_once "./assec/header.php";
require_once './classes/database.php';
require_once './classes/Users.php';

$db = new Database();
$users = new Users($db->getConnection());
$user = $_SESSION['user'] ?? [];
$error = $success = '';

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $users->id = $user['id'];
    $users->Name = htmlspecialchars($_POST['name']);
    $users->login = htmlspecialchars($_POST['login']);
    $users->nomer = htmlspecialchars($_POST['phone']);
    
    // Пароль
    $users->password = !empty($_POST['password']) 
        ? password_hash($_POST['password'], PASSWORD_DEFAULT) 
        : $user['password'];
    
    // Инициализация img перед обработкой файла
    $users->img = $user['img'] ?? 'assets/default-avatar.jpg';

    // Загрузка аватара
    if ($_FILES['avatar']['error'] === 0) {
        $uploadDir = 'uploads/avatars/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        
        $ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
        $filename = uniqid() . '.' . $ext;
        $maxSize = 2 * 1024 * 1024;
        
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif']) && $_FILES['avatar']['size'] <= $maxSize) {
            move_uploaded_file($_FILES['avatar']['tmp_name'], $uploadDir . $filename);
            $users->img = $uploadDir . $filename;
            if ($user['img'] && $user['img'] !== 'assets/default-avatar.jpg') {
                @unlink($user['img']);
            }
        } else {
            $error = 'Файл должен быть JPG/PNG/GIF и не больше 2MB';
        }
    }

    if (!$error && $users->edit($users->id)) {
        $_SESSION['user'] = $users->getByID($user['id']);
        header("Location: profile.php");
        exit();
    } else {
        $error = $error ?: 'Ошибка обновления';
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Редактирование профиля</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .edit-container { 
            max-width: 600px; 
            margin: 2rem auto; 
            padding: 2rem; 
            background: white; 
            border-radius: 15px; 
            box-shadow: 0 4px 20px rgba(0,0,0,0.08); 
        }
        .avatar-preview { 
            width: 150px; 
            height: 150px; 
            border-radius: 50%; 
            object-fit: cover; 
            margin: 0 auto 1rem; 
            display: block; 
        }
    </style>
</head>
<body class="bg-light">
    <div class="edit-container">
        <h1 class="text-center mb-4">Редактирование профиля</h1>
        
        <?php if($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        
        <?php if($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="text-center">
            <img src="<?= htmlspecialchars($user['img'] ?? 'assets/default-avatar.jpg') ?>" 
                 class="avatar-preview mb-3"
                 id="avatarPreview">

            <input type="file" 
                   name="avatar" 
                   id="avatarInput" 
                   class="form-control mb-3" 
                   accept="image/*">
            <div class="form-text mb-4">JPG/PNG/GIF до 2MB</div>

            <input type="text" 
                   name="name" 
                   class="form-control mb-3" 
                   placeholder="Имя" 
                   value="<?= htmlspecialchars($user['Name'] ?? '') ?>" 
                   required>

            <input type="text" 
                   name="login" 
                   class="form-control mb-3" 
                   placeholder="Логин" 
                   value="<?= htmlspecialchars($user['login'] ?? '') ?>" 
                   required>

            <input type="password" 
                   name="password" 
                   class="form-control mb-3" 
                   placeholder="Новый пароль (оставьте пустым)">

            <input type="tel" 
                   name="phone" 
                   class="form-control mb-3" 
                   placeholder="Телефон" 
                   value="<?= htmlspecialchars($user['nomer'] ?? '') ?>">

            <button type="submit" class="btn btn-primary w-100 mb-2">Сохранить</button>
            <a href="profile.php" class="btn btn-secondary w-100">Назад</a>
        </form>
    </div>

    <script>
        document.getElementById('avatarInput').onchange = function(e) {
            const reader = new FileReader();
            reader.onload = function() {
                document.getElementById('avatarPreview').src = reader.result;
            }
            if (e.target.files[0]) {
                reader.readAsDataURL(e.target.files[0]);
            }
        }
    </script>
</body>
</html>

<?php include_once "./assec/footer.php"; ?>
