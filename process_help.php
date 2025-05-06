<?php
session_start();
require_once './classes/database.php';
require_once './classes/Notifications.php';
require_once './classes/Users.php';
require_once './classes/Sluchenia.php';

// Проверка авторизации ДО всех действий
if(!isset($_SESSION['user'])) {
    die("Доступ запрещен");
}

$db = new Database();
$notifications = new Notifications($db->getConnection());
$users = new Users($db->getConnection());
$sluchenia = new Sluchenia($db->getConnection());

// Получаем ID служения из формы
$sluchenie_id = (int)($_POST['sluchenie_id'] ?? 0);
$sluchenie = $sluchenia->getByID($sluchenie_id);

// Проверка существования служения
if(!$sluchenie) {
    die("Служение не найдено");
}

// Проверка, что пользователь не лидер
if($sluchenie['Lider'] == $_SESSION['user']['id']) {
    die("Вы не можете помогать сам себе");
}

// Получаем номер и формируем сообщение
$phone = $_SESSION['user']['nomer'] ?? 'не указан';
$message = sprintf(
    "%s хочет присоединиться к служению '%s' (Телефон: %s)",
    htmlspecialchars($_SESSION['user']['Name']),
    htmlspecialchars($sluchenie['Name']),
    htmlspecialchars($phone)
);


// Перенаправление
header("Location: sluchenia.php");
exit();
