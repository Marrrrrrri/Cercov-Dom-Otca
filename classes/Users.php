<?php
class Users {
    private $PDO;
    private $table = "Users";

    public $id;
    public $Name;
    public $login;
    public $password; 
    public $nomer;
    public $admin;
    public $img;
    
    public function __construct($db) {
        $this->PDO = $db;
    }

    // Получение всех пользователей
    public function getAll() {
        try {
            $query = "SELECT * FROM {$this->table} ORDER BY id";
            $stmt = $this->PDO->prepare($query);
            $stmt->execute();
            return $stmt;
        } catch (PDOException $e) {
            error_log("Ошибка в getAll(): " . $e->getMessage());
            return false;
        }
    }

    // Получение пользователя по ID
    public function getByID($id) {
        $query = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        $stmt = $this->PDO->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Добавление пользователя (ИСПРАВЛЕННЫЙ МЕТОД)
    public function add() {
        $query = "INSERT INTO {$this->table} 
        (Name, login, password, nomer, admin, img) 
        VALUES (:name, :login, :password, :nomer, :admin, :img)";
        
        $stmt = $this->PDO->prepare($query);
        
        // Привязка параметров
        $stmt->bindParam(':name', $this->Name);
        $stmt->bindParam(':login', $this->login);
        $stmt->bindParam(':password', $this->password);
        $stmt->bindParam(':nomer', $this->nomer);
        $stmt->bindParam(':admin', $this->admin, PDO::PARAM_BOOL);
        $stmt->bindParam(':img', $this->img);
        
        return $stmt->execute();
    }

    // Редактирование пользователя (ДОРАБОТАННЫЙ МЕТОД)
    public function edit($id) {
        $query = "UPDATE Users SET 
            Name = :name, 
            login = :login, 
            password = :password, 
            nomer = :nomer, 
            img = :img 
            WHERE id = :id";
        
        $stmt = $this->PDO->prepare($query);
        
        $stmt->bindParam(':name', $this->Name);
        $stmt->bindParam(':login', $this->login);
        $stmt->bindParam(':password', $this->password);
        $stmt->bindParam(':nomer', $this->nomer);
        $stmt->bindParam(':img', $this->img);  // Добавлено
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    
    // Удаление пользователя
    public function remove($id) {
        $query = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->PDO->prepare($query);
        return $stmt->execute([$id]);
    }

    public function makeAdmin($user_id) {
        try {
            $query = "UPDATE {$this->table} SET admin = 1 WHERE id = :id";
            $stmt = $this->PDO->prepare($query);
            $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Ошибка в makeAdmin(): " . $e->getMessage());
            return false;
        }
    }
    
    public function revokeAdmin($user_id) {
        try {
            $query = "UPDATE {$this->table} SET admin = 0 WHERE id = :id";
            $stmt = $this->PDO->prepare($query);
            $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Ошибка в revokeAdmin(): " . $e->getMessage());
            return false;
        }
    }
    public function makeLeader($user_id, $sluchenie_name) {
        $sluchenia = new Sluchenia($this->PDO);
        $sluchenia->Name = $sluchenie_name;
        $sluchenia->Lider = $user_id;
        return $sluchenia->add();
    }
}
