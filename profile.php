<?php
session_start();
include_once "./assec/header.php";
require_once './classes/Database.php';
require_once './classes/Users.php';
require_once './classes/Sluchenia.php';
require_once './classes/Slushenia_Users.php';
require_once './classes/Curs.php';
require_once './classes/Notifications.php';

$db = new Database();
$users = new Users($db->getConnection());
$sluchenia = new Sluchenia($db->getConnection());
$slusheniaUsers = new Slushenia_Users($db->getConnection());
$curs = new Curs($db->getConnection());
$notifications = new Notifications($db->getConnection());

// Обработка очистки уведомлений
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['clear_notifications'])) {
    $notifications->deleteAllForUser($_SESSION['user']['id']);
    $_SESSION['success'] = 'Уведомления успешно очищены';
    header('Location: profile.php');
    exit;
}

// Получение данных пользователя
$user = isset($_SESSION['user']) ? $users->getByID($_SESSION['user']['id']) : null;
if(!$user) {
    header('Location: Vhod.php');
    exit;
}

$userNotifications = $notifications->getAllForUser($user['id']);
$userCourses = $curs->getUserCourses($user['id']);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Профиль пользователя</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .profile-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        .avatar-preview {
            width: 200px;
            height: 200px;
            border-radius: 15px;
            object-fit: cover;
            border: 3px solid #dee2e6;
        }
        .ministry-badge {
            background: #f8f9fa;
            border-radius: 20px;
            padding: 5px 15px;
            margin-right: 10px;
            margin-bottom: 10px;
            display: inline-block;
        }
        .leader-badge {
            background: #d4edda;
            color: #155724;
        }
        .notification-list {
            max-height: 300px;
            overflow-y: auto;
        }
        .course-card {
            transition: transform 0.2s;
        }
        .course-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body class="bg-light">
<div class="profile-container">
    <!-- Уведомления об успехе/ошибке -->
    <?php if(isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= $_SESSION['success'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <div class="text-center mb-4">
        <img src="<?= htmlspecialchars($user['img'] ?? './assets/default-avatar.jpg') ?>" 
             class="avatar-preview mb-3" 
             alt="Аватар">
        <h1><?= htmlspecialchars($user['Name']) ?></h1>
        <p class="text-muted"><?= $user['admin'] ? 'Администратор' : 'Пользователь' ?></p>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-person"></i> Основная информация</h5>
                    <p><strong>Логин:</strong> <?= htmlspecialchars($user['login']) ?></p>
                    <p><strong>Телефон:</strong> <?= $user['nomer'] ? htmlspecialchars($user['nomer']) : 'Не указан' ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-gear"></i> Действия</h5>
                    <div class="d-grid gap-2">
                        <a href="edit_profile.php" class="btn btn-primary">
                            <i class="bi bi-pencil"></i> Редактировать профиль
                        </a>
                        <a href="logout.php" class="btn btn-outline-danger">
                            <i class="bi bi-box-arrow-right"></i> Выйти
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Блок уведомлений -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="card-title"><i class="bi bi-bell"></i> Уведомления</h5>
                <?php if(!empty($userNotifications)): ?>
                    <form method="POST">
                        <button type="submit" name="clear_notifications" class="btn btn-sm btn-outline-danger">
                            <i class="bi bi-trash"></i> Очистить все
                        </button>
                    </form>
                <?php endif; ?>
            </div>
            
            <?php if(empty($userNotifications)): ?>
                <div class="alert alert-info">Нет уведомлений</div>
            <?php else: ?>
                <ul class="list-group notification-list">
                    <?php foreach($userNotifications as $note): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center <?= $note['is_read'] ? '' : 'list-group-item-primary' ?>">
                            <span><?= htmlspecialchars($note['text']) ?></span>
                            <small class="text-muted"><?= date('d.m.Y H:i', strtotime($note['created_at'])) ?></small>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>

    <!-- Блок служений -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title"><i class="bi bi-people"></i> Служения</h5>
            
            <?php
            $userMinistries = $slusheniaUsers->getUserMinistries($user['id']);
            $ledMinistries = $sluchenia->getByLeaderId($user['id']);
            $hasMinistries = false;
            ?>
            
            <?php if(!empty($userMinistries) || !empty($ledMinistries)): ?>
                <div class="mb-3">
                    <?php foreach($ledMinistries as $ministry): ?>
                        <span class="ministry-badge leader-badge">
                            <i class="bi bi-star"></i> Лидер: <?= htmlspecialchars($ministry['Name']) ?>
                        </span>
                    <?php endforeach; ?>
                    
                    <?php foreach($userMinistries as $ministry): ?>
                        <span class="ministry-badge">
                            <i class="bi bi-check-circle"></i> <?= htmlspecialchars($ministry['Name']) ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-muted">вы не состоит в служениях</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Блок курсов -->
    <div class="card">
        <div class="card-body">
            <h5 class="card-title"><i class="bi bi-book"></i> Мои курсы</h5>
            
            <?php if(!empty($userCourses)): ?>
                <div class="row row-cols-1 row-cols-md-3 g-4">
                    <?php foreach($userCourses as $course): ?>
                        <div class="col">
                            <div class="card h-100 course-card">
                                <div class="card-body">
                                    <h6 class="card-title"><?= htmlspecialchars($course['Name']) ?></h6>
                                    <p class="card-text text-muted small">
                                        <?= date('d.m.Y', strtotime($course['created_at'])) ?>
                                    </p>
                                    <a href="course_detail.php?id=<?= $course['id'] ?>" class="btn btn-sm btn-outline-primary">
                                        Подробнее
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-muted">вы не записаны на курсы</p>
                <a href="courses.php" class="btn btn-sm btn-primary">Выбрать курс</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php include_once "./assec/footer.php"; ?>
