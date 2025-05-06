<?php
require_once 'header.php';
require_once '../classes/users.php';

$users = new Users($db->getConnection());

// Обработка действий
if(isset($_POST['make_admin'])) {
    $users->makeAdmin($_POST['user_id']);
}

if(isset($_POST['revoke_admin'])) {
    $users->revokeAdmin($_POST['user_id']);
}

$all_users = $users->getAll()->fetchAll();
?>

<!-- Управление пользователями -->
<div class="card church-admin-card">
    <div class="card-header">
        <i class="bi bi-people"></i> Управление администраторами
    </div>
    <div class="card-body">
        <table class="table table-hover church-admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Имя</th>
                    <th>Роль</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($all_users as $user): ?>
                <tr>
                    <td><?= $user['id'] ?></td>
                    <td><?= htmlspecialchars($user['Name']) ?></td>
                    <td>
                        <?php if($user['admin']): ?>
                            <span class="badge badge-admin">Администратор</span>
                        <?php else: ?>
                            <span class="badge badge-user">Пользователь</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                            <?php if(!$user['admin']): ?>
                                <button type="submit" name="make_admin" class="btn btn-sm btn-church-outline btn-church-success">
                                    <i class="bi bi-shield-plus"></i> Назначить
                                </button>
                            <?php else: ?>
                                <button type="submit" name="revoke_admin" class="btn btn-sm btn-church-outline btn-church-warning">
                                    <i class="bi bi-shield-lock"></i> Отозвать
                                </button>
                            <?php endif; ?>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="text-center mt-4">
    <blockquote class="church-quote py-3 px-4">
        <p class="fs-5">
            <i class="bi bi-quote church-icon"></i> 
            "Управляйте народом Божиим не господствуя над наследием Его, но подавая пример стаду"
        </p>
        <footer class="blockquote-footer text-brown">
            1 Петра 5:3
        </footer>
    </blockquote>
</div>
