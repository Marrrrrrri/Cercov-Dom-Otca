<?php
include_once "./assec/header.php";
require_once './classes/database.php';
require_once './classes/event.php';

?>

<section class="events">
  <div class="container">
    <h1 class="text-center mb-4">Мероприятия</h1>

    <?php 
    $SQL = 'SELECT * FROM event ORDER BY DataStart ASC';
    $stmt = $db->query($SQL);
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $event):
      $dateStart = date('d.m.Y', strtotime($event['DataStart']));
      $dateEnd = !empty($event['DataEnd']) && $event['DataEnd'] != $event['DataStart']
        ? ' - ' . date('d.m.Y', strtotime($event['DataEnd']))
        : '';
    ?>
      <div class="event-block2" id="event-<?= htmlspecialchars($event['id']) ?>">
        <div class="event-content2">
          <img src="../style/img/<?= htmlspecialchars($event['image']) ?>"
            alt="<?= htmlspecialchars($event['Name']) ?>"
            class="event-image2">

          <div class="event-details2">
            <div class="event-time-place2">
              <span class="event-time2">🕒 <?= $dateStart . $dateEnd ?></span>
              <span class="event-place2">📍 <?= htmlspecialchars($event['Adress']) ?></span>
            </div>
            <h2 class="event-title2"><?= htmlspecialchars($event['Name']) ?></h2>
            <p class="event-description2"><?= htmlspecialchars($event['Content']) ?></p>
          </div>
        </div>
      </div>
      <hr class="event-divider2">
    <?php endforeach; ?>

  </div>
</section>

<?php
include_once "./assec/footer.php";
?>