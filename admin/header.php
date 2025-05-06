<?php
require_once '../classes/Auth.php';
require_once '../classes/Database.php';

$auth = new Auth();
$auth->checkAdminAccess();
$db = new Database();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Админ-панель</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
<link rel="stylesheet" href="/admin/style.css">


    
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark church-header">
    <div class="container">
        <a class="navbar-brand" href="/admin/">
            <i class="bi bi-speedometer2"></i> Админ-панель
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarContent">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="/admin/users.php" id="users-link">
                        <i class="bi bi-people-fill"></i> Пользователи
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/admin/sluchenia.php" id="sluchenia-link">
                        <i class="bi bi-house-gear"></i> Служения
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/admin/curs.php">
                        <i class="bi bi-book"></i> Курсы
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/admin/event.php">
                        <i class="bi bi-calendar-event"></i> Мероприятия
                    </a>
                </li>
            </ul>
            <a href="../index.php" class="btn btn-outline-danger">
                <i class="bi bi-box-arrow-right"></i> Выйти
            </a>
        </div>
    </div>
</nav>
<div class="container mt-4">
