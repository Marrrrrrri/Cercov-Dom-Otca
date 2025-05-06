<?php
include_once "./assec/header.php";
require_once './classes/Auth.php';
require_once './classes/Database.php';
require_once './classes/Sluchenia.php';
require_once './classes/Slushenia_Users.php';
require_once './classes/Users.php';
require_once './classes/HelpRequests.php';
require_once './classes/Notifications.php';

session_start();

$auth = new Auth();
$sluchenia_id = (int)($_GET['id'] ?? 0);
if(!$sluchenia_id) die('ID служения не указан');
if(!$auth->isSlucheniaLeader($sluchenia_id)) die('Доступ запрещен');

$db = new Database();
$conn = $db->getConnection();

$sluchenia = new Sluchenia($conn);
$slusheniaUsers = new Slushenia_Users($conn);
$users = new Users($conn);
$helpRequests = new HelpRequests($conn);
$notifications = new Notifications($conn);

// Обработка действий
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Обновление информации о служении
    if (isset($_POST['save_sluchenia'])) {
        $sluchenia->id = $sluchenia_id;
        $sluchenia->Name = $_POST['Name'] ?? '';
        $sluchenia->opisanie = $_POST['opisanie'] ?? '';
        $sluchenia->help = $_POST['help'] ?? '';

        if ($sluchenia->edit2($sluchenia_id)) {
            $_SESSION['message'] = ['type' => 'success', 'text' => 'Данные служения обновлены'];
        } else {
            $_SESSION['message'] = ['type' => 'danger', 'text' => 'Ошибка при обновлении'];
        }

        header("Location: sluchenia_manage.php?id=$sluchenia_id");
        exit;
    }
    // Принятие заявки
    if(isset($_POST['approve_request'])) {
        $request_id = (int)$_POST['request_id'];
        $request = $helpRequests->getById($request_id);
      
        if ($request) {
          $helpRequests->approve($request_id);
            $slusheniaUsers->addMember($request['user_id'], $sluchenia_id);
            $userInfo=$users->getByID($request['user_id']);
            $slucheniaInfo=$sluchenia->getByID($sluchenia_id);
          
            $message = "Ваша заявка на служение '".$slucheniaInfo['Name']."' одобрена!";
            $notifications->createForUser($request['user_id'], $message);
            

            $_SESSION['success'] = "Заявка одобрена. Пользователь добавлен в команду.";
          }
        header("Location: sluchenia_manage.php?id=".$sluchenia_id);
        exit();
    }

    // Отклонение заявки
    if(isset($_POST['reject_request'])) {
        $request_id = (int)$_POST['request_id'];
        $helpRequests->reject($request_id);
        $_SESSION['message'] = 'Заявка отклонена';
    }

    // Удаление участника
    if(isset($_POST['remove_member'])) {
        $user_id = (int)$_POST['user_id'];
        $slusheniaUsers->removeMember($user_id, $sluchenia_id);
        $_SESSION['message'] = 'Пользователь удален из команды';
    }

    header("Location: sluchenia_manage.php?id=$sluchenia_id");
    exit;
}

$sluchenia_data = $sluchenia->getByID($sluchenia_id);
$team_members = $slusheniaUsers->getTeamMembers($sluchenia_id);
$pending_requests = $helpRequests->getPendingForSluchenie($sluchenia_id);
?>

<div class="container mt-4">
    <?php if(isset($_SESSION['message'])): ?>
        <div class="alert alert-success"><?= $_SESSION['message'] ?></div>
        <?php unset($_SESSION['message']) ?>
    <?php endif; ?>
    
    <?php if(isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
        <?php unset($_SESSION['error']) ?>
    <?php endif; ?>

    <h1>Управление служением: <?= htmlspecialchars($sluchenia_data['Name']) ?></h1>

    <!-- Форма редактирования служения -->
    <form method="POST" class="mb-5">
        <!-- Поля редактирования служения -->
        <div class="mb-3">
            <label class="form-label">Название</label>
            <input type="text" name="Name" value="<?= htmlspecialchars($sluchenia_data['Name']) ?>" 
                   class="form-control" required>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Описание</label>
            <textarea name="opisanie" class="form-control" rows="5" required>
                <?= htmlspecialchars($sluchenia_data['opisanie']) ?>
            </textarea>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Чем можно помочь</label>
            <textarea name="help" class="form-control" rows="5" required>
                <?= htmlspecialchars($sluchenia_data['help']) ?>
            </textarea>
        </div>
        
        <button type="submit" name="save_sluchenia" class="btn btn-primary">
            Сохранить изменения
        </button>
    </form>

    <!-- Секция заявок -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h3 class="mb-0">Заявки на участие</h3>
        </div>
        
        <div class="card-body">
            <?php if(empty($pending_requests)): ?>
                <div class="alert alert-info">Новых заявок нет</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Имя</th>
                                <th>Телефон</th>
                                <th>Дата</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($pending_requests as $request): ?>
                            <?php $user = $users->getByID($request['user_id']); ?>
                            <tr>
                                <td><?= htmlspecialchars($user['Name']) ?></td>
                                <td><?= htmlspecialchars($user['nomer']) ?></td>
                                <td><?= date('d.m.Y H:i', strtotime($request['created_at'])) ?></td>
                                <td>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="user_id" value="<?= $request['user_id'] ?>">
                                        <input type="hidden" name="request_id" value="<?= $request['id'] ?>">
                                        <button type="submit" name="approve_request" 
                                                class="btn btn-success btn-sm">
                                            Принять
                                        </button>
                                    </form>
                                    
                                    <form method="POST" class="d-inline ms-2">
                                        <input type="hidden" name="request_id" value="<?= $request['id'] ?>">
                                        <button type="submit" name="reject_request" 
                                                class="btn btn-danger btn-sm">
                                            Отклонить
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Секция текущей команды -->
    <div class="card">
        <div class="card-header bg-success text-white">
            <h3 class="mb-0">Текущая команда</h3>
        </div>
        
        <div class="card-body">
            <?php if(empty($team_members)): ?>
                <div class="alert alert-info">В команде пока нет участников</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Имя</th>
                                <th>Телефон</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($team_members as $member): ?>
                            <?php $user = $users->getByID($member['id']); ?>
                            <tr>
                                <td><?= htmlspecialchars($user['Name']) ?></td>
                                <td><?= htmlspecialchars($user['nomer']) ?></td>
                                <td>
                                    <form method="POST">
                                        <input type="hidden" name="user_id" value="<?= $member['id'] ?>">
                                        <button type="submit" name="remove_member" 
                                                class="btn btn-danger btn-sm">
                                            Удалить
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include_once "./assec/footer.php"; ?>
