<?php
session_start();
include_once "./assec/header.php";
require_once './classes/database.php';
require_once './classes/Users.php';
require_once './classes/Sluchenia.php';
require_once './classes/Slushenia_Users.php';

$db = new Database();
$sluchenia = new Sluchenia($db->getConnection());
$slusheniaUsers = new Slushenia_Users($db->getConnection());
$users = new Users($db->getConnection());

$allSluchenia = $sluchenia->getAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Служения церкви</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .ministry-card {
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            overflow: hidden;
        }
        .team-member {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        .team-member img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            margin-right: 15px;
            object-fit: cover;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-5">
        <?php if(isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
            <?php unset($_SESSION['success']) ?>
        <?php endif; ?>

        <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
            <?php unset($_SESSION['error']) ?>
        <?php endif; ?>

        <h1 class="text-center mb-5">Служения нашей церкви</h1>

        <?php while($sluchenie = $allSluchenia->fetch(PDO::FETCH_ASSOC)): ?>
        <div class="card ministry-card mb-4">
            <div class="card-body">
                <h2 class="card-title"><?= htmlspecialchars($sluchenie['Name']) ?></h2>
                
                <div class="d-flex align-items-center flex-wrap gap-4 mb-4">
    <!-- Лидер -->
    <div class="d-flex align-items-center">
        <div class="me-3">
            <h5 class="mb-1">Лидер:</h5>
            <?php 
            $lider = $users->getByID($sluchenie['Lider']);
            if($lider): ?>
                <div class="d-flex align-items-center bg-light p-2 rounded">
                    <img src="<?= htmlspecialchars($lider['img'] ?? 'assets/default-avatar.jpg') ?>" 
                         alt="<?= htmlspecialchars($lider['Name']) ?>" 
                         class="rounded-circle me-2" 
                         style="width: 40px; height: 40px; object-fit: cover;">
                    <div>
                        <h6 class="mb-0"><?= htmlspecialchars($lider['Name']) ?></h6>
                        <small class="text-muted"><?= htmlspecialchars($lider['nomer']) ?></small>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-warning p-2 mb-0">Не назначен</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Команда -->
    <div class="d-flex align-items-center">
        <div>
            <h5 class="mb-1">Команда:</h5>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <?php 
                $teamMembers = $slusheniaUsers->getBySlucheniaId($sluchenie['id']);
                $teamExists = false;
                
                while($member = $teamMembers->fetch(PDO::FETCH_ASSOC)):
                    $user = $users->getByID($member['id_Users']);
                    if($user):
                        $teamExists = true; ?>
                        <div class="d-flex align-items-center bg-light p-2 rounded">
                            <img src="<?= htmlspecialchars($user['img'] ?? 'assets/default-avatar.jpg') ?>" 
                                 alt="<?= htmlspecialchars($user['Name']) ?>" 
                                 class="rounded-circle me-2" 
                                 style="width: 40px; height: 40px; object-fit: cover;">
                            <div>
                                <h6 class="mb-0"><?= htmlspecialchars($user['Name']) ?></h6>
                                <small class="text-muted"><?= htmlspecialchars($user['nomer']) ?></small>
                            </div>
                        </div>
                    <?php endif;
                endwhile;
                
                if(!$teamExists): ?>
                    <div class="alert alert-info p-2 mb-0">Формируется</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>



                <!-- Описание служения -->
                <div class="mb-4">
                    <h5>Описание:</h5>
                    <p><?= nl2br(htmlspecialchars($sluchenie['opisanie'])) ?></p>
                </div>

                <!-- Помощь служению -->
                <div class="mb-4">
                    <h5>Чем можно помочь:</h5>
                    <p><?= nl2br(htmlspecialchars($sluchenie['help'])) ?></p>
                </div>

                <!-- Кнопка помощи -->
                <div class="mb-4">
                    <?php if(isset($_SESSION['user'])): ?>
                        <form method="POST" action="want_to_help.php">
    <input type="hidden" name="user_id" value="<?= $_SESSION['user']['id'] ?>">
    <input type="hidden" name="sluchenie_id" value="<?= $sluchenie['id'] ?>">
    <button type="submit" class="btn btn-primary">Могу помочь</button>
</form>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            Для помощи служению авторизуйтесь и укажите номер телефона в профиле
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php include_once "./assec/footer.php"; ?>
