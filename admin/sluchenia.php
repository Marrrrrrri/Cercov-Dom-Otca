<?php
require_once 'header.php';
require_once '../classes/Sluchenia.php';
require_once '../classes/Users.php';

$sluchenia = new Sluchenia($db->getConnection());
$users = new Users($db->getConnection());

// Обработка POST-запросов
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete'])) {
        $sluchenia->remove($_POST['id']);
    } elseif (isset($_POST['edit'])) {
        // Используем существующие методы класса Sluchenia
        $sluchenie_id = $_POST['id'];

        // Получаем служение по ID для проверки существования
        $existing_sluchenie = $sluchenia->getByID($sluchenie_id);

        if ($existing_sluchenie) {
            $sluchenia->id = $sluchenie_id;
            $sluchenia->Name = $_POST['name'];
            $sluchenia->Lider = $_POST['lider_id'];
            $sluchenia->opisanie = $_POST['opisanie'];
            $sluchenia->help = $_POST['help'];
            $sluchenia->edit($sluchenie_id);
        } else {
            echo "<script>alert('Служение не найдено.');</script>";
        }
    } elseif (isset($_POST['create'])) {
        // Используем существующие методы класса Sluchenia
        $sluchenia->Name = $_POST['name'];
        $sluchenia->Lider = $_POST['lider_id'];
        $sluchenia->opisanie = $_POST['opisanie'];
        $sluchenia->help = $_POST['help'];
        $sluchenia->add();
    }
}

$all_sluchenia = $sluchenia->getAll()->fetchAll();
$all_users = $users->getAll()->fetchAll();
?>

<h2 class="church-admin-title">Управление служениями</h2>

<!-- Форма создания служения -->
<form method="POST" class="mb-4 church-service-form">
    <h3 class="form-section-title">Создать новое служение</h3>
    
    <div class="form-grid">
        <!-- Название -->
        <div class="form-group-church">
            <label class="form-label-church">
                <i class="bi bi-bookmark-check"></i> Название служения
            </label>
            <input type="text" name="name" class="form-control form-control-church" 
                   placeholder="Введите название" required>
        </div>

        <!-- Лидер -->
        <div class="form-group-church">
            <label class="form-label-church">
                <i class="bi bi-person-badge"></i> Руководитель
            </label>
            <select name="lider_id" class="form-select form-control-church" required>
                <option value="" disabled selected>Выберите лидера</option>
                <?php foreach ($all_users as $user): ?>
                    <option value="<?= $user['id'] ?>">
                        <?= htmlspecialchars($user['Name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Описание -->
        <div class="form-group-church">
            <label class="form-label-church">
                <i class="bi bi-card-text"></i> Описание
            </label>
            <textarea name="opisanie" class="form-control form-control-church" 
                      rows="3" placeholder="Краткое описание"></textarea>
        </div>

        <!-- Помощь -->
        <div class="form-group-church">
            <label class="form-label-church">
                <i class="bi bi-question-circle"></i> Необходимая помощь
            </label>
            <textarea name="help" class="form-control form-control-church" 
                      rows="3" placeholder="Тип требуемой помощи"></textarea>
        </div>
    </div>

    <div class="text-end mt-3">
        <button type="submit" name="create" class="btn btn-create-church">
            <i class="bi bi-plus-circle"></i> Создать служение
        </button>
    </div>
</form>

<!-- Таблица служений -->
<table class="table table-striped church-service-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Название</th>
            <th>Лидер</th>
            <th>Описание</th>
            <th>Помощь</th>
            <th>Действия</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($all_sluchenia as $sluchenie): ?>
            <tr>
                <td><?= $sluchenie['id'] ?></td>
                <td><?= htmlspecialchars($sluchenie['Name']) ?></td>
                <td>
                    <?php 
                    $leader = $users->getByID($sluchenie['Lider']);
                    echo $leader ? htmlspecialchars($leader['Name']) : 'Не назначен';
                    ?>
                </td>
                <td><?= nl2br(htmlspecialchars($sluchenie['opisanie'] ?? '-')) ?></td>
                <td><?= nl2br(htmlspecialchars($sluchenie['help'] ?? '-')) ?></td>
                <td>
                    <form method="POST" class="d-inline">
                        <input type="hidden" name="id" value="<?= $sluchenie['id'] ?>">
                        <button type="button" class="btn btn-sm btn-primary btn-church-primary" data-bs-toggle="modal" 
                            data-bs-target="#editModal<?= $sluchenie['id'] ?>">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button type="submit" name="delete" class="btn btn-sm btn-danger" 
                            onclick="return confirm('Удалить служение?')">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>

                    <!-- Модальное окно редактирования -->
                    <div class="modal fade church-modal" id="editModal<?= $sluchenie['id'] ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="POST">
                                    <input type="hidden" name="id" value="<?= $sluchenie['id'] ?>">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Редактировать служение</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label">Название</label>
                                            <input type="text" name="name" class="form-control church-input" 
                                                value="<?= htmlspecialchars($sluchenie['Name']) ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Описание</label>
                                            <textarea name="opisanie" class="form-control church-input" rows="3"><?= 
                                                htmlspecialchars($sluchenie['opisanie'] ?? '') ?></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Помощь</label>
                                            <textarea name="help" class="form-control church-input" rows="3"><?= 
                                                htmlspecialchars($sluchenie['help'] ?? '') ?></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Лидер</label>
                                            <select name="lider_id" class="form-select church-input">
                                                <option value="">Без лидера</option>
                                                <?php foreach ($all_users as $user): ?>
                                                    <option value="<?= $user['id'] ?>" <?= 
                                                        $user['id'] == $sluchenie['Lider'] ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($user['Name']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-church-secondary" data-bs-dismiss="modal">Закрыть</button>
                                        <button type="submit" name="edit" class="btn btn-church-primary">Сохранить</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
    
    </tbody>
</table>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
