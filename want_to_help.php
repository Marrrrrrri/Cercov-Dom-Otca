<?php
session_start();
require_once './classes/Database.php';
require_once './classes/HelpRequests.php';
require_once './classes/Notifications.php';

$db = new Database();
$conn = $db->getConnection();
$helpRequests = new HelpRequests($conn);
$notifications = new Notifications($conn);

try {
    $user_id = (int)($_POST['user_id'] ?? 0);
    $sluchenie_id = (int)($_POST['sluchenie_id'] ?? 0);

    if ($user_id < 1 || $sluchenie_id < 1) {
        throw new Exception("Неверные параметры запроса");
    }

    if ($helpRequests->exists($user_id, $sluchenie_id)) {
        throw new Exception("Вы уже подали заявку на это служение");
    }

    $helpRequests->create($user_id, $sluchenie_id);

    // Получаем название служения для уведомления
    $stmt = $conn->prepare("SELECT Name FROM Sluchenia WHERE id = ?");
    $stmt->execute([$sluchenie_id]);
    $service_name = $stmt->fetchColumn();

    $notifications->createForSlucheniaLeader($sluchenie_id, "Новая заявка на служение \"$service_name\"");

    $_SESSION['success'] = "Заявка отправлена! Лидер свяжется с вами.";
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
}

header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'sluchenia.php'));
exit;
