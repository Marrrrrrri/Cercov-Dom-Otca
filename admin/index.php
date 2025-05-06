<?php
require_once 'header.php';
require_once '../classes/Auth.php';
require_once '../classes/Database.php';
require_once '../classes/Users.php';

$auth = new Auth();
$auth->checkAdminAccess();

$db = new Database();
$users = new Users($db->getConnection());

if(isset($_POST['make_admin'])) $users->makeAdmin($_POST['user_id']);
if(isset($_POST['revoke_admin'])) $users->revokeAdmin($_POST['user_id']);

$all_users = $users->getAll()->fetchAll();
?>



        
        <div class="card-body">
            <div class="text-center mb-4">
                <h5 class="text-brown">
                    <i class="bi bi-cross church-icon"></i> 
                    Церковная административная панель
                </h5>
                <div class="church-divider my-3"></div>
            </div>
            
            <div class="row g-4">
                <!-- Инструкция -->
                <div class="col-md-6">
                    <div class="card h-100 church-card border-brown">
                        <div class="card-header church-header" style="background: #5C4033;">
                            <i class="bi bi-list-check church-icon"></i> 
                            Краткая инструкция
                        </div>
                        <div class="card-body">
                            <ol class="list-group list-group-numbered church-list">
                                <li class="list-group-item church-list-item">
                                    Все изменения фиксируются
                                </li>
                                <li class="list-group-item church-list-item">
                                    Добавление событий требует потверждение пастора
                                </li>
                                <li class="list-group-item church-list-item">
                                    При удалении данных помните о последствиях
                                </li>
                                <li class="list-group-item church-list-item">
                                    Перед выходом убедитесь в сохранении всех изменений
                                </li>
                            </ol>
                        </div>
                    </div>
                </div>

                <!-- Духовные правила -->
                <div class="col-md-6">
                    <div class="card h-100 church-card border-brown">
                        <div class="card-header church-header" style="background: #5C4033;">
                            <i class="bi bi-chat-square-heart church-icon"></i> 
                            Духовные принципы
                        </div>
                        <div class="card-body">
                            <div class="alert church-alert">
                                <h5 class="text-brown">
                                    <i class="bi bi-arrow-through-heart church-icon"></i> 
                                    Основные правила:
                                </h5>
                                <ul class="list-unstyled church-ul">
                                    <li class="mb-2">
                                        <i class="bi bi-check-circle text-success church-icon"></i> 
                                        Любовь и смирение в каждом действии
                                    </li>
                                    <li class="mb-2">
                                        <i class="bi bi-check-circle text-success church-icon"></i> 
                                        Избегай осуждения при редактировании
                                    </li>
                                    <li>
                                        <i class="bi bi-check-circle text-success church-icon"></i> 
                                        Все изменения - во славу Божию
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Блок с цитатой -->
            <div class="text-center mt-4">
                <blockquote class="church-quote py-3 px-4">
                    <p class="fs-5">
                        <i class="bi bi-quote church-icon"></i> 
                        "И всё, что делаете, делайте от души, как для Господа, а не для человеков"
                    </p>
                    <footer class="blockquote-footer text-brown">
                        Колоссянам 3:23
                    </footer>
                </blockquote>
            </div>
        </div>


   

