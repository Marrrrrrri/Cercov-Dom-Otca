<?php
session_start();
require_once './classes/database.php';
require_once './classes/CourseRegistrations.php';

if(!isset($_SESSION['user'])) {
    $_SESSION['error'] = "Для записи необходимо авторизоваться";
    header("Location: ".$_SERVER['HTTP_REFERER']);
    exit();
}

$db = new Database();
$registrations = new CourseRegistrations($db->getConnection());

$course_id = (int)$_POST['course_id'];
$user_id = $_SESSION['user']['id'];

if($registrations->register($user_id, $course_id)) {
    $_SESSION['success'] = "Вы успешно записаны на курс!";
} else {
    $_SESSION['error'] = "Ошибка записи на курс";
}

header("Location: ".$_SERVER['HTTP_REFERER']);
