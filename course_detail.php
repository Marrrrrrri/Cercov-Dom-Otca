<?php
session_start(); // Добавлено в начало
include_once "./assec/header.php";
require_once './classes/database.php';
require_once './classes/event.php';
require_once './classes/CourseRegistrations.php'; // Добавлено подключение класса

$database = new Database();
$db = $database->getConnection();

// Получаем id курса в самом начале
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Инициализация объекта регистраций после подключения класса
$registrations = new CourseRegistrations($db);

// Проверка авторизации перед вызовом методов
$isRegistered = isset($_SESSION['user']['id']) 
    ? $registrations->isRegistered($_SESSION['user']['id'], $id) 
    : false;

$participants = $registrations->getRegistrations($id);

// Защита от некорректного id
if ($id <= 0) {
    echo "<div class='container py-5'><h2>Курс не найден</h2></div>";
    include_once "./assec/footer.php";
    exit;
}

$stmt = $db->prepare("SELECT * FROM curs WHERE id = ?");
$stmt->execute([$id]);
$course = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$course) {
    echo "<div class='container py-5'><h2>Курс не найден</h2></div>";
    include_once "./assec/footer.php";
    exit;
}
?>

<style>
/* Общие стили */
body {
  margin: 0;
  padding: 0;
  font-family: 'Segoe UI', system-ui, sans-serif;
}

/* Верхний блок */
.teacher-card {
  padding: 2rem 0;
  margin-bottom: 0;
}

.teacher-img {
  width: 200px;
  height: 200px;
  object-fit: cover;
  border-radius: 10px;
  margin: 0 auto 1rem;
  display: block;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.teacher-name {
  text-align: center;
  font-size: 1.5rem;
  color: #003366;
  margin: 1rem 0;
}

.short-description {
  font-size: 1.1rem;
  line-height: 1.6;
  color: #444;
  padding-left: 1.5rem;
  border-left: 3px solid #007bff;
}

/* Нижний блок */
.full-width-container {
  background: rgb(234, 234, 234);
  width: 100vw;
  position: relative;
  left: 50%;
  right: 50%;
  margin-left: -50vw;
  margin-right: -50vw;
}

.inner-description-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 2.5rem 15px;
}

.description-heading {
  color:rgb(0, 0, 0);
  font-size: 1.25rem;
  margin: 1.5rem 0 0.8rem;
  padding-bottom: 0.5rem;
  border-bottom: 2px dashed #dee2e6;
}

.description-heading:first-child {
  margin-top: 0;
}

.description-content {
  line-height: 1.7;
  color: #333;
  padding-left: 1.2rem;
}

.back-btn {
  margin-top: 2rem;
  width: 200px;
  border-radius: 50px;
  padding: 0.6rem;
  transition: all 0.3s;
}

.back-btn:hover {
  transform: translateX(-5px);
}

/* Стили для контента из БД */
.course-html-content h3 {
  color: #2c3e50;
  margin: 1.5rem 0 1rem;
  border-bottom: 2px solid #f1f1f1;
  padding-bottom: 0.5rem;
}

.course-html-content ul {
  padding-left: 1.5rem;
  margin-bottom: 1.5rem;
}

.course-html-content li {
  margin-bottom: 0.5rem;
  line-height: 1.6;
}

.course-html-content p {
  margin-bottom: 1rem;
  line-height: 1.7;
}

.course-html-content strong {
  color: #2c3e50;
}

@media (max-width: 768px) {
  .teacher-card {
    padding: 1rem 0;
  }
  .teacher-img {
    width: 150px;
    height: 150px;
  }
  .inner-description-container {
    padding: 1.5rem 15px;
  }
}
</style>

<section class="py-5">
  <div class="container course-detail-container">
  <h1 class="course-title mb-4 text-center">Описание курса: <?= htmlspecialchars($course['Name']) ?></h1>
    <!-- Верхний блок -->
    <div class="teacher-card">
      <div class="row align-items-center">
        <div class="col-md-4 text-center">
         <img src="/uploads/courses/<?= htmlspecialchars($course['img']) ?>" 
               alt="Курс: <?= htmlspecialchars($course['Name']) ?>" 
               class="teacher-img">
          <h3 class="teacher-name"><?= htmlspecialchars($course['Avtor']) ?></h3>
        </div>
        <div class="col-md-8">
          <div class="short-description">
            <?= nl2br(htmlspecialchars($course['Cratcoe'])) ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Нижний блок с полной шириной -->
  <div class="full-width-container" style="padding-bottom: 20px;">
    <div class="inner-description-container">
      <h4 class="description-heading">Программа курса</h4>
      <div class="description-content course-html-content">
        <?= $course['Content'] ?>
      </div>

      <div class="mt-4">
    <h5>Участники курса:</h5>
    <div class="d-flex flex-wrap">
        <?php if(count($participants) > 0): ?>
            <?php foreach($participants as $user): ?>
                <div class="ministry-badge me-2 mb-2">
                    <img src="<?= htmlspecialchars($user['img'] ?? '../style/img/Group 61.svg') ?>" 
                         class="rounded-circle me-2" 
                         width="50" 
                         height="50" 
                         alt="<?= htmlspecialchars($user['Name']) ?>"
                         style="object-fit: cover">
                    <?= htmlspecialchars($user['Name']) ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-muted">Пока никто не записался на курс</p>
        <?php endif; ?>
    </div>
</div>

      </div>

      <div class="d-flex justify-content-around align-items-center mt-3">
        <?php if(isset($_SESSION['user'])): ?>
            <?php if($isRegistered): ?>
              <span class="badge bg-success fw-bold fs-5 px-3 py-2">Вы записаны</span>
            <?php else: ?>
                <form action="process_register.php" method="POST">
                    <input type="hidden" name="course_id" value="<?= $id ?>">
                    <button type="submit" class="btn btn-primary">Записаться</button>
                </form>
            <?php endif; ?>
        <?php else: ?>
            <button class="btn btn-primary" onclick="showAuthAlert()">Записаться</button>
        <?php endif; ?>
        <a href="javascript:history.back()" class="btn btn-outline-secondary">Назад</a>
      </div>
    </div>
  </div>
</section>

<script>
function showAuthAlert() {
    alert('Для записи необходимо авторизоваться');
    window.location.href = 'Vhod.php';
}
</script>

<?php
include_once "./assec/footer.php";
?>
