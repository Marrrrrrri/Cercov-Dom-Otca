<?php
require_once 'header.php';
require_once '../classes/Curs.php';

$curs = new Curs($db->getConnection());

// Обработка POST-запросов
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if(isset($_POST['delete'])) {
            $curs->delete($_POST['id']);
        }
        elseif(isset($_POST['edit'])) {
            $curs->id = $_POST['id'];
            $curs->Name = $_POST['name'];
            $curs->Cratcoe = $_POST['cratcoe'];
            $curs->Content = $_POST['content'];
            $curs->Time = $_POST['time'];
            $curs->Avtor = $_POST['avtor'];
            $curs->DataStart = $_POST['datastart'];
            
            if(!empty($_FILES['img']['name'])) {
                $curs->img = $curs->uploadImage($_FILES['img']);
            } else {
                $curs->img = $_POST['existing_img'];
            }
            
            $curs->edit();
        }
        elseif(isset($_POST['create'])) {
            $curs->Name = $_POST['name'];
            $curs->Cratcoe = $_POST['cratcoe'];
            $curs->Content = $_POST['content'];
            $curs->Time = $_POST['time'];
            $curs->Avtor = $_POST['avtor'];
            $curs->DataStart = $_POST['datastart'];
            $curs->img = $curs->uploadImage($_FILES['img']);
            
            $curs->create();
        }
    } catch(Exception $e) {
        $error_message = $e->getMessage();
    }
}

$all_curs = $curs->getAll()->fetchAll();
?>

<h2 class="church-admin-title">Управление курсами</h2>

<!-- Форма создания курса -->
<form method="POST" enctype="multipart/form-data" class="mb-4 church-service-form">
    <h3 class="form-section-title">Создать новый курс</h3>
    
    <div class="form-grid">
        <!-- Название -->
        <div class="form-group-church">
            <label class="form-label-church">
                <i class="bi bi-book"></i> Название курса
            </label>
            <input type="text" name="name" class="form-control form-control-church" 
                   placeholder="Введите название курса" required>
        </div>

        <!-- Краткое описание -->
        <div class="form-group-church">
            <label class="form-label-church">
                <i class="bi bi-card-text"></i> Краткое описание
            </label>
            <input type="text" name="cratcoe" class="form-control form-control-church" 
                   placeholder="Введите краткое описание" required>
        </div>

        <!-- Полное содержание -->
        <div class="form-group-church">
            <label class="form-label-church">
                <i class="bi bi-justify-left"></i> Полное содержание
            </label>
            <textarea name="content" class="form-control form-control-church" 
                      rows="3" placeholder="Введите полное содержание" required></textarea>
        </div>

        <!-- Длительность (часы) -->
        <div class="form-group-church">
            <label class="form-label-church">
                <i class="bi bi-clock"></i> Длительность (часы)
            </label>
            <input type="number" name="time" class="form-control form-control-church" 
                   placeholder="Введите длительность (часы)" required>
        </div>

        <!-- Автор курса -->
        <div class="form-group-church">
            <label class="form-label-church">
                <i class="bi bi-person"></i> Автор курса
            </label>
            <input type="text" name="avtor" class="form-control form-control-church" 
                   placeholder="Введите автора курса" required>
        </div>

        <!-- Дата старта -->
        <div class="form-group-church">
            <label class="form-label-church">
                <i class="bi bi-calendar"></i> Дата старта
            </label>
            <input type="date" name="datastart" class="form-control form-control-church" 
                   required>
        </div>

        <!-- Изображение -->
        <div class="form-group-church">
            <label class="form-label-church">
                <i class="bi bi-image"></i> Изображение курса
            </label>
            <input type="file" name="img" class="form-control form-control-church" required>
        </div>
    </div>

    <div class="text-end mt-3">
        <button type="submit" name="create" class="btn btn-create-church">
            <i class="bi bi-plus-circle"></i> Создать курс
        </button>
    </div>
</form>

<!-- Таблица курсов -->
<table class="table table-striped church-service-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Название</th>
            <th>Краткое описание</th>
            <th>Изображение</th>
            <th>Автор</th>
            <th>Дата старта</th>
            <th>Действия</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($all_curs as $curs_item): ?>
        <tr>
            <td><?= $curs_item['id'] ?></td>
            <td><?= htmlspecialchars($curs_item['Name']) ?></td>
            <td><?= htmlspecialchars($curs_item['Cratcoe']) ?></td>
            <td>
                <?php if($curs_item['img']): ?>
                    <img src="/uploads/courses/<?= htmlspecialchars($curs_item['img']) ?>" 
                         class="img-thumbnail" 
                         style="max-height: 50px;">
                <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($curs_item['Avtor']) ?></td>
            <td><?= htmlspecialchars($curs_item['DataStart']) ?></td>
            <td>
                <form method="POST" class="d-inline">
                    <input type="hidden" name="id" value="<?= $curs_item['id'] ?>">
                    <button type="button" class="btn btn-sm btn-primary btn-church-primary" data-bs-toggle="modal" 
                        data-bs-target="#editModal<?= $curs_item['id'] ?>">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button type="submit" name="delete" class="btn btn-sm btn-danger" 
                        onclick="return confirm('Удалить курс?')">
                        <i class="bi bi-trash"></i>
                    </button>
                </form>

                <!-- Модальное окно редактирования -->
                <div class="modal fade church-modal" id="editModal<?= $curs_item['id'] ?>" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <form method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="id" value="<?= $curs_item['id'] ?>">
                                <input type="hidden" name="existing_img" value="<?= $curs_item['img'] ?>">
                                <div class="modal-header">
                                    <h5 class="modal-title">Редактировать курс</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label-church">Название курса</label>
                                            <input type="text" name="name" class="form-control form-control-church" 
                                                value="<?= htmlspecialchars($curs_item['Name']) ?>" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label-church">Краткое описание</label>
                                            <input type="text" name="cratcoe" class="form-control form-control-church" 
                                                value="<?= htmlspecialchars($curs_item['Cratcoe']) ?>" required>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label-church">Полное содержание</label>
                                            <textarea name="content" class="form-control form-control-church" rows="5" required><?= 
                                                htmlspecialchars($curs_item['Content']) ?></textarea>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label-church">Длительность (часы)</label>
                                            <input type="number" name="time" class="form-control form-control-church" 
                                                value="<?= htmlspecialchars($curs_item['Time']) ?>" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label-church">Автор курса</label>
                                            <input type="text" name="avtor" class="form-control form-control-church" 
                                                value="<?= htmlspecialchars($curs_item['Avtor']) ?>" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label-church">Дата старта</label>
                                            <input type="date" name="datastart" class="form-control form-control-church" 
                                                value="<?= htmlspecialchars($curs_item['DataStart']) ?>" required>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label-church">Текущее изображение</label>
                                            <?php if($curs_item['img']): ?>
                                                <img src="/uploads/courses/<?= htmlspecialchars($curs_item['img']) ?>" 
                                                     class="img-thumbnail mb-2" 
                                                     style="max-height: 150px;">
                                            <?php endif; ?>
                                            <input type="file" name="img" class="form-control form-control-church">
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
