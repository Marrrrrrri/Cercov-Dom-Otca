<?php
class Sluchenia {
    private $PDO;
    private $table = "Sluchenia";

    public $id;
    public $Name;
    public $Lider;
    public $opisanie;
    public $help;
    
    public function __construct($db) {
        $this->PDO = $db;
    }

    public function getAll() {
        $query = "SELECT * FROM {$this->table} ORDER BY id";
        $stmt = $this->PDO->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getByID($id) {
        $this->id = $id;
        $query = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 0,1";
        $stmt = $this->PDO->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row;
    }

    public function add() {
        $query = "INSERT INTO {$this->table} 
        (Name, Lider, opisanie, help) 
        VALUES (?, ?, ?, ?)";
        $stmt = $this->PDO->prepare($query);
        $stmt->bindParam(1, $this->Name);
        $stmt->bindParam(2, $this->Lider);
        $stmt->bindParam(3, $this->opisanie);
        $stmt->bindParam(4, $this->help);
        return $stmt->execute();
    }

    public function edit($id) {
        $query = "UPDATE {$this->table} SET 
            Name = :name,
            Lider = :lider,
            opisanie = :opisanie,
            help = :help
            WHERE id = :id";

        $stmt = $this->PDO->prepare($query);
        $stmt->bindValue(':name', $this->Name);
        $stmt->bindValue(':lider', $this->Lider, PDO::PARAM_INT); // Добавьте эту строку
        $stmt->bindValue(':opisanie', $this->opisanie);
        $stmt->bindValue(':help', $this->help);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function edit2($id) {
        $query = "UPDATE {$this->table} SET 
            Name = :name,
            opisanie = :opisanie,
            help = :help
            WHERE id = :id";

        $stmt = $this->PDO->prepare($query);
        $stmt->bindValue(':name', $this->Name);
        $stmt->bindValue(':opisanie', $this->opisanie);
        $stmt->bindValue(':help', $this->help);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function remove($id) {
        $this->id = $id;
        $query = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->PDO->prepare($query);
        $stmt->bindParam(1, $this->id);
        return $stmt->execute();
    }

    public function getByLeaderId($leader_id) {
        $stmt = $this->PDO->prepare("SELECT * FROM Sluchenia WHERE Lider = ?");
        $stmt->execute([$leader_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Метод для обновления лидера (дополнение)
    public function updateLeader($sluchenie_id, $new_leader_id) {
        $query = "UPDATE {$this->table} SET Lider = ? WHERE id = ?";
        $stmt = $this->PDO->prepare($query);
        $stmt->bindParam(1, $new_leader_id);
        $stmt->bindParam(2, $sluchenie_id);
        return $stmt->execute();
    }

    // Метод для проверки существования служения
    public function exists($id) {
        $stmt = $this->PDO->prepare("SELECT COUNT(*) FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetchColumn() > 0;
    }

    // Метод для поиска по названию
    public function searchByName($name) {
        $stmt = $this->PDO->prepare("SELECT * FROM {$this->table} WHERE Name LIKE ?");
        $searchTerm = "%$name%";
        $stmt->bindParam(1, $searchTerm);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Метод для получения количества служений
    public function count() {
        $stmt = $this->PDO->query("SELECT COUNT(*) FROM {$this->table}");
        return $stmt->fetchColumn();
    }

    // Метод для проверки, является ли пользователь лидером
    public function isLeader($user_id, $sluchenie_id) {
        $stmt = $this->PDO->prepare("SELECT COUNT(*) FROM {$this->table} WHERE id = ? AND Lider = ?");
        $stmt->execute([$sluchenie_id, $user_id]);
        return $stmt->fetchColumn() > 0;
    }
}
