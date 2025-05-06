<?php
include_once "./assec/header.php";
require_once './classes/database.php';
require_once './classes/event.php';
require_once './classes/CourseRegistrations.php';

$database = new Database();
$db = $database->getConnection();
$eventObj = new Event($db);
$registrations = new CourseRegistrations($db);
?>
<section class="glav">
  <div class="content-wrapper">
    <h1 class="link-body-emphasis px-10">Церковь "Дом Отца"</h1>

  </div>
</section>
<section id="te">
  <h3>
    Приветствуем вас во имя Иисуса Христа! Мы – протестантская церковь, верующая в силу молитвы, библейские истины и важность крепкого сообщества. Наша миссия – служить Богу и нашим ближним. На нашем сайте вы найдете ресурсы для духовного роста, информацию о наших мероприятиях и миссионерских проектах. Узнайте о наших благотворительных инициативах и о том, как вы можете присоединиться к нашей семье в распространении любви и надежды. Вместе мы стремимся изменить мир к лучшему, следуя учению Христа. Будем рады видеть вас частью нашего сообщества!</h3>
    
<h3  style="text-align: center; padding-top: 30px;">
    <b>Наши воскресные служения проходят каждую неделю в 11:00 по адресу:
ул. Пушкина, 32</b></h3>
</section>

<section id="merop">
  <h3>Ближайшие мероприятия</h3>

  <div class="events-container">
    <?php
    $SQL = 'SELECT * FROM event ORDER BY DataStart ASC';
    $stmt = $db->query($SQL);
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $event) {
      $content = strip_tags($event['Content']); // Удаляем HTML-теги
      $showEllipsis = mb_strlen($content) > 300;
      $shortContent = mb_substr($content, 0, 300);
      $remainingContent = $showEllipsis ? mb_substr($content, 300) : '';

      $dates = date('d.m.Y', strtotime($event['DataStart']));
      if (!empty($event['DataEnd']) && $event['DataEnd'] != $event['DataStart']) {
        $dates .= ' - ' . date('d.m.Y', strtotime($event['DataEnd']));
      }

      echo '
    <div class="event-block" 
         onclick="openEventPage(\'meropr.php#event-' . $event['id'] . '\')">
        <p class="event-title">' . htmlspecialchars($event['Name']) . '</p>
        <div class="event-image">
            <img src="../style/img/' . htmlspecialchars($event['image']) . '" 
                 alt="' . htmlspecialchars($event['Name']) . '">
            <div class="event-overlay">
                <div class="event-details">
                  <p><strong>Адрес:</strong> ' . htmlspecialchars($event['Adress']) . '</p>
                  <p><strong>Даты:</strong> ' . $dates . '</p>
                </div>
                <div class="event-content">
                  <p>' . htmlspecialchars($shortContent);

      if ($showEllipsis) {
        echo '<span class="ellipsis">...</span>
              <span class="full-content">' . htmlspecialchars($remainingContent) . '</span>';
      }

      echo '</p></div>
            </div>
        </div>
    </div>';
    }
    ?>
  </div>
</section>

<style>
  .event-overlay {
    display: flex;
    flex-direction: column;
    overflow: hidden;
    padding: 20px;
  }

  .event-details {
    flex-shrink: 0;
    margin-bottom: 15px;
  }

  .event-content {
    flex-grow: 1;
    overflow: hidden;
    position: relative;
  }

  .event-content p {
    margin: 0;
    max-height: 150px;
    overflow: hidden;
    position: relative;
    line-height: 1.5;
  }

  .ellipsis {
    position: absolute;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.6);
    padding: 0 5px;
    cursor: pointer;
  }

  .full-content {
    display: none;
  }

  .event-content.expanded p {
    max-height: none;
    overflow: auto;
  }

  .event-content.expanded .ellipsis {
    display: none;
  }
</style>

<script>
  // Обновленный скрипт с учетом новой структуры
  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.event-content').forEach(content => {
      const p = content.querySelector('p');
      const ellipsis = content.querySelector('.ellipsis');

      // Проверяем реальное переполнение
      if (p.scrollHeight > p.clientHeight) {
        ellipsis.style.display = 'block';
      }

      // Обработчик клика для троеточия
      if (ellipsis) {
        ellipsis.addEventListener('click', (e) => {
          e.stopPropagation();
          content.classList.toggle('expanded');
          p.style.maxHeight = content.classList.contains('expanded') ?
            'none' :
            '150px';
        });
      }
    });
  });

  // Обработчик для закрытия при клике вне блока
  document.addEventListener('click', (e) => {
    if (!e.target.closest('.ellipsis')) {
      document.querySelectorAll('.event-content').forEach(content => {
        content.classList.remove('expanded');
        content.querySelector('p').style.maxHeight = '150px';
      });
    }
  });
</script>




<section id="curs" class="py-5">
  <h3 class="text-center mb-4">Активные курсы</h3>

  <?php
  $stmt = $db->query("SELECT * FROM curs");
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($courses as $course):
?>
<div class="row align-items-center bg-light mb-4 p-3 rounded shadow-sm">
  <div class="col-md-8">
    <h4 class="fw-bold"><?= htmlspecialchars($course['Name']) ?></h4>
    <p><?= nl2br(htmlspecialchars($course['Cratcoe'])) ?></p>
    <div class="d-flex justify-content-between align-items-center mt-3">
      <span class="text-muted">Длительность: <?= htmlspecialchars($course['Time']) ?></span>
      <?php if(isset($_SESSION['user']) && isset($_SESSION['user']['id'])): ?>
    <?php if($registrations->isRegistered($_SESSION['user']['id'], $course['id'])): ?>
      <span class="badge bg-success fw-bold fs-5 px-3 py-2">Вы записаны</span>
    <?php else: ?>
        <form action="process_register.php" method="POST" class="d-inline">
            <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
            <button type="submit" class="btn btn-primary me-2">Записаться</button>
        </form>
    <?php endif; ?>
	  <a href="course_detail.php?id=<?= urlencode($course['id']) ?>" class="btn btn-outline-secondary">Подробнее</a>
<?php else: ?>
    <button class="btn btn-primary me-2" 
            onclick="showAuthAlert()">Записаться</button>
			<a href="./course_detail.php?id=<?= urlencode($course['id']) ?>" class="btn btn-outline-secondary">Подробнее</a>
<?php endif; ?>

<script>
function showAuthAlert() {
    alert('Для записи необходимо авторизоваться');
    window.location.href = 'Vhod.php';
}
</script>
    </div>
  </div>
  <div class="col-md-4">
  <img src="/uploads/courses/<?= htmlspecialchars($course['img']) ?>"  alt="<?= htmlspecialchars($course['Name']) ?>" class="img-fluid rounded">
  </div>
</div>
<hr>
<?php endforeach; ?>

</section>

<section>
<div class="faq-block">
        <h3><i class="bi bi-question-circle-fill"></i> Часто задаваемые вопросы</h3>
        
        <div class="faq-item">
            <div class="faq-question">
                <span>Как записаться на крещение?</span>
                <i class="bi bi-chevron-down"></i>
            </div>
            <div class="faq-answer">
                Записаться на крещение можно по номеру телефона <b>+7 235 32 24</b> или в личных сообщениях в Telegram/vatsap по этому номеру.
            </div>
        </div>
        
        <div class="faq-item">
            <div class="faq-question">
                <span>Когда проходит Вечеря Господня?</span>
                <i class="bi bi-chevron-down"></i>
            </div>
            <div class="faq-answer">
                Вечеря проводится в первое воскресенье каждого месяца.
            </div>
        </div>
        
        <div class="faq-item">
            <div class="faq-question">
                <span>Где проходят богослужения?</span>
                <i class="bi bi-chevron-down"></i>
            </div>
            <div class="faq-answer">
                Богослужения проходят по адресу, Пушкина 32, каждое воскресенье в 11:00.
            </div>
        </div>
        
        <div class="faq-item">
            <div class="faq-question">
                <span>Можно ли присоединиться к домашней группе?</span>
                <i class="bi bi-chevron-down"></i>
            </div>
        </div>
        
        <div class="faq-item">
            <div class="faq-question">
                <span>Как можно пожертвовать?</span>
                <i class="bi bi-chevron-down"></i>
            </div>
            <div class="faq-answer">
                Пожертвования принимаются на богослужениях или через реквизиты, указанные на сайте в разделе «Пожертвования».
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('.faq-question').forEach(question => {
            question.addEventListener('click', () => {
                const item = question.parentElement;
                item.classList.toggle('active');
                
                // Закрытие других открытых элементов
                document.querySelectorAll('.faq-item').forEach(el => {
                    if(el !== item && el.classList.contains('active')) {
                        el.classList.remove('active');
                    }
                });
            });
        });
    </script>
</section>

<section id="contacts" class="py-5">
  <div class="container">
    <h3 class="text-center mb-4">Контактная информация</h3>
    <div class="row">
      <!-- Левая часть: текстовая информация -->
      <div class="col-md-6 mb-4">
        <div class="contact-item mb-3">
          <i class="fas fa-map-marker-alt contact-icon"></i>
          <div>
            <h4 class="fw-bold">Наш адрес</h4>
            <p>г. Челябинск, ул. Пушкина, д. 32</p>
          </div>
        </div>

        <div class="contact-item mb-3">
          <i class="fas fa-phone-alt contact-icon"></i>
          <div>
            <h4 class="fw-bold">Телефон</h4>
            <p><a href="tel:+79991234567" class="contact-link">+7 (999) 123-45-67</a></p>
          </div>
        </div>

        <div class="contact-item mb-3">
          <i class="fas fa-envelope contact-icon"></i>
          <div>
            <h4 class="fw-bold">Электронная почта</h4>
            <p><a href="mailto:info@example.com" class="contact-link">Cercov@example.com</a></p>
          </div>
        </div>

        <div class="contact-item mb-3">
          <i class="fas fa-clock contact-icon"></i>
          <div>
            <h4 class="fw-bold">Режим работы</h4>
            <p>Пн-Пт: выходной <br>Сб-Вс: 9:00 - 21:00</p>
          </div>
        </div>
      </div>

      <!-- Правая часть: карта -->
      <div class="col-md-6">
        <!-- Карта -->
        <div class="map-container">
          <iframe
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2244.329007382728!2d37.61729931591268!3d55.75582698055295!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x414abcced5e7c8b5%3A0x8f8e7c8b5e7c8b5d!2sMoscow%2C%20Russia!5e0!3m2!1sen!2sru!4v1615976325398!5m2!1sen!2sru"
            width="100%"
            height="300"
            style="border:0;"
            allowfullscreen=""
            loading="lazy">
          </iframe>
        </div>
      </div>
    </div>
  </div>
</section>

<script>
  function openEventPage(url) {
    window.location.href = url;
  }
</script>
<?php
include_once "./assec/footer.php";
?>
