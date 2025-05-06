<?php
session_start();
require_once './classes/database.php';
require_once './classes/Notifications.php';
require_once './classes/event.php';
require_once './classes/curs.php';
require_once './classes/Auth.php';
require_once './classes/Sluchenia.php';

$database = new Database();
$db = $database->getConnection();
$notifications = new Notifications($db);
$auth = new Auth();
$sluchenia = new Sluchenia($db);

$user = isset($_SESSION['user']) ? $_SESSION['user'] : null;
$unread = $user ? $notifications->getUnread($user['id']) : [];

$is_leader = false;
$service_id = null;

if($user) {
    $stmt = $db->prepare("SELECT COUNT(*) FROM Sluchenia WHERE Lider = ?");
    $stmt->execute([$user['id']]);
    $is_leader = $stmt->fetchColumn() > 0;

    if($is_leader) {
        $stmt = $db->prepare("SELECT id FROM Sluchenia WHERE Lider = ? LIMIT 1");
        $stmt->execute([$user['id']]);
        $service_id = $stmt->fetchColumn();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="../style/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<button id="back-to-top" title="Наверх">
    <i class="bi bi-arrow-up">↑</i>
</button>

<script>

window.addEventListener('scroll', function() {
    var scrollButton = document.getElementById('back-to-top');
    if (window.pageYOffset > 300) {
        scrollButton.style.display = 'flex';
    } else {
        scrollButton.style.display = 'none';
    }
});

document.getElementById('back-to-top').addEventListener('click', function() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
});
</script>

<?php if(isset($_SESSION['error'])): ?>
<div class="alert alert-danger alert-dismissible fade show m-3" role="alert">
    <?= $_SESSION['error'] ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php unset($_SESSION['error']); endif; ?>

<?php if(isset($_SESSION['success'])): ?>
<div class="alert alert-success alert-dismissible fade show m-3" role="alert">
    <?= $_SESSION['success'] ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php unset($_SESSION['success']); endif; ?>

<!-- Отладочный блок -->
<header class="py-3 mb-4">
    <div class="container d-flex flex-wrap justify-content-between align-items-center">
        <a href="/" class="d-flex align-items-center text-decoration-none">
            <img src="../style/img/Vector.svg" style="height: 40px;">
        </a>

        <ul class="nav">
            <li class="nav-item">
                <a href="../info.php" class="nav-link link-body-emphasis px-2">о нас</a>
            </li>
            <li class="nav-item">
                <a href="../meropr.php" class="nav-link link-body-emphasis px-2">мероприятия</a>
            </li>
            <li class="nav-item">
                <a href="../sluchenia.php" class="nav-link link-body-emphasis px-2">служения</a>
            </li>
            <li class="nav-item">
                <a href="../index.php#curs" class="nav-link link-body-emphasis px-2">курсы</a>
            </li>

            <?php if(!$user): ?>
                <li class="nav-item">
                    <a href="../Vhod.php" class="nav-link link-body-emphasis px-2">вход</a>
                </li>
                <li class="nav-item">
                    <a href="../register.php" class="nav-link link-body-emphasis px-2">регистрация</a>
                </li>
            <?php else: ?>
                <?php if($auth->isAdmin()): ?>
                <li class="nav-item">
                    <a href="../admin/" class="nav-link link-body-emphasis px-2 text-danger fw-bold">Админ-панель</a>
                </li>
                <?php endif; ?>
                
                <?php if($is_leader): ?>
                <li class="nav-item">
                    <a href="/sluchenia_manage.php?id=<?= $service_id ?>" class="nav-link">
                        Мое служение
                    </a>
                </li>
                <?php endif; ?>
                
                <li class="nav-item">
                    <a href="../profile.php" class="nav-link">
                        <img src="<?= htmlspecialchars($user['img'] ?? '../style/img/Group 61.svg') ?>" 
                             style="height: 30px; width: 30px; border-radius: 50%; object-fit: cover;">
                    </a>
                </li>
                <li class="nav-item dropdown">
    <a class="nav-link position-relative" href="#" role="button" data-bs-toggle="dropdown">
        🔔
        <?php if(count($unread) > 0): ?>
        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
            <?= count($unread) ?>
        </span>
        <?php endif; ?>
    </a>
    <ul class="dropdown-menu dropdown-menu-end">
        <?php foreach($unread as $notice): ?>
        <li>
            <a class="dropdown-item" href="#">
                <div class="d-flex justify-content-between">
                    <span><?= htmlspecialchars($notice['text']) ?></span>
                    <small class="text-muted">
                        <?= date('H:i', strtotime($notice['created_at'])) ?>
                    </small>
                </div>
            </a>
        </li>
        <li><hr class="dropdown-divider"></li>
        <?php endforeach; ?>
        
        <?php if(empty($unread)): ?>
        <li><a class="dropdown-item text-muted" href="#">Нет новых уведомлений</a></li>
        <?php endif; ?>
        
    </ul>
</li>
                </li>
            <?php endif; ?>
        </ul>
    </div>
</header>
