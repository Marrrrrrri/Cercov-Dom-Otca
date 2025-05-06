<?php
require_once 'header.php';
require_once '../classes/Event.php';

// Проверка подключения к БД
if(!isset($db)) {
    die("Ошибка подключения к базе данных");
}

$event = new Event($db->getConnection());

// Обработка POST-запросов
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if(isset($_POST['remove'])) {
            if(!$event->remove((int)$_POST['id'])) {
                throw new Exception("Ошибка удаления");
            }
        }
        elseif(isset($_POST['edit'])) {
            $event->id = (int)$_POST['id'];
            $event->Name = htmlspecialchars($_POST['name'] ?? '');
            $event->Adress = htmlspecialchars($_POST['adress'] ?? '');
            $event->Content = htmlspecialchars($_POST['content'] ?? '');
            $event->DataStart = $_POST['datastart'] ?? '';
            $event->DataEnd = $_POST['dataend'] ?? '';
            
            if(!empty($_FILES['image']['name'])) {
                $event->Image = $event->uploadImage($_FILES['image']);
            } else {
                $event->Image = $_POST['existing_image'] ?? '';
            }
            
            if(!$event->edit($_POST['id'])) {
                throw new Exception("Ошибка при редактировании");
            }
        }
        elseif(isset($_POST['add'])) {
            $event->Name = htmlspecialchars($_POST['name'] ?? '');
            $event->Adress = htmlspecialchars($_POST['adress'] ?? '');
            $event->Content = htmlspecialchars($_POST['content'] ?? '');
            $event->DataStart = $_POST['datastart'] ?? '';
            $event->DataEnd = $_POST['dataend'] ?? '';
            
            if(empty($_FILES['image']['name'])) {
                throw new Exception("Изображение обязательно");
            }
            
            $event->Image = $event->uploadImage($_FILES['image']);
            
            if(!$event->add()) {
                throw new Exception("Ошибка при создании");
            }
        }
    } catch(Exception $e) {
        $error_message = $e->getMessage();
    }
}

// Получение данных
try {
    $result = $event->getAll();
    $all_events = $result ? $result->fetchAll() : [];
} catch(Exception $e) {
    $all_events = [];
    $error_message = "Ошибка загрузки данных: " . $e->getMessage();
}
?>

<h2 class="church-admin-title">Управление мероприятиями</h2>

<!-- Блок ошибок -->
<?php if(!empty($error_message)): ?>
<div class="alert alert-danger church-alert">
    <i class="bi bi-exclamation-triangle"></i> 
    <?= htmlspecialchars($error_message) ?>
</div>
<?php endif; ?>

<!-- Форма создания -->
<form method="POST" enctype="multipart/form-data" class="mb-4 church-service-form">
    <h3 class="form-section-title">Создать новое мероприятие</h3>

    <div class="form-grid">
        <!-- Название -->
        <div class="form-group-church">
            <label class="form-label-church">
                <i class="bi bi-bookmark-check"></i> Название
            </label>
            <input type="text" name="name" class="form-control form-control-church"
                   placeholder="Введите название" required>
        </div>

        <!-- Изображение -->
        <div class="form-group-church">
            <label class="form-label-church">
                <i class="bi bi-image"></i> Изображение
            </label>
            <input type="file" name="image" class="form-control form-control-church" required>
        </div>

        <!-- Дата начала -->
        <div class="form-group-church">
            <label class="form-label-church">
                <i class="bi bi-calendar-event"></i> Дата начала
            </label>
            <input type="datetime-local" name="datastart" class="form-control form-control-church" required>
        </div>

        <!-- Дата окончания -->
        <div class="form-group-church">
            <label class="form-label-church">
                <i class="bi bi-calendar-check"></i> Дата окончания
            </label>
            <input type="datetime-local" name="dataend" class="form-control form-control-church" required>
        </div>

        <!-- Адрес -->
        <div class="form-group-church">
            <label class="form-label-church">
                <i class="bi bi-geo-alt"></i> Адрес
            </label>
            <input type="text" name="adress" class="form-control form-control-church"
                   placeholder="Введите адрес" required>
        </div>

        <!-- Описание -->
        <div class="form-group-church">
            <label class="form-label-church">
                <i class="bi bi-card-text"></i> Описание
            </label>
            <textarea name="content" class="form-control form-control-church"
                      rows="3" placeholder="Введите описание" required></textarea>
        </div>
    </div>

    <div class="text-end mt-3">
        <button type="submit" name="add" class="btn btn-create-church">
            <i class="bi bi-plus-circle"></i> Создать
        </button>
    </div>
</form>

<!-- Таблица мероприятий -->
<table class="table table-striped church-service-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Название</th>
            <th>Дата начала</th>
            <th>Адрес</th>
            <th>Изображение</th>
            <th>Действия</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($all_events as $event_item): ?>
        <tr>
            <td><?= htmlspecialchars($event_item['id']) ?></td>
            <td><?= htmlspecialchars($event_item['Name']) ?></td>
            <td><?= date('d.m.Y', strtotime($event_item['DataStart'])) ?></td>
            <td><?= htmlspecialchars($event_item['Adress']) ?></td>
            <td>
                <?php if(!empty($event_item['Image'])): ?>
                    <img src="/uploads/events/<?= htmlspecialchars($event_item['Image']) ?>"
                         class="img-thumbnail"
                         style="max-height: 50px;">
                <?php endif; ?>
            </td>
            <td>
                <form method="POST" class="d-inline" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?= $event_item['id'] ?>">
                    <input type="hidden" name="existing_image" value="<?= $event_item['Image'] ?>">
                    <button type="button" class="btn btn-sm btn-primary btn-church-primary" data-bs-toggle="modal"
                            data-bs-target="#editModal<?= $event_item['id'] ?>">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button type="submit" name="remove" class="btn btn-sm btn-danger"
                            onclick="return confirm('Удалить мероприятие?')">
                        <i class="bi bi-trash"></i>
                    </button>
                </form>

                <!-- Модальное окно редактирования -->
                <div class="modal fade church-modal" id="editModal<?= $event_item['id'] ?>" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <form method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="id" value="<?= $event_item['id'] ?>">
                                <input type="hidden" name="existing_image" value="<?= $event_item['Image'] ?>">
                                <div class="modal-header">
                                    <h5 class="modal-title">Редактировать</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label-church">Название</label>
                                            <input type="text" name="name" class="form-control form-control-church"
                                                   value="<?= htmlspecialchars($event_item['Name']) ?>" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label-church">Изображение</label>
                                            <input type="file" name="image" class="form-control form-control-church">
                                            <?php if(!empty($event_item['Image'])): ?>
                                                <small class="text-muted">Текущее: <?= htmlspecialchars($event_item['Image']) ?></small>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label-church">Дата начала</label>
                                            <input type="datetime-local" name="datastart" class="form-control form-control-church"
                                                   value="<?= date('Y-m-d\TH:i', strtotime($event_item['DataStart'])) ?>" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label-church">Дата окончания</label>
                                            <input type="datetime-local" name="dataend" class="form-control form-control-church"
                                                   value="<?= date('Y-m-d\TH:i', strtotime($event_item['DataEnd'])) ?>" required>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label-church">Адрес</label>
                                            <input type="text" name="adress" class="form-control form-control-church"
                                                   value="<?= htmlspecialchars($event_item['Adress']) ?>" required>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label-church">Описание</label>
                                            <textarea name="content" class="form-control form-control-church" rows="5" required><?=
                                                htmlspecialchars($event_item['Content']) ?></textarea>
                                        </div>
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
