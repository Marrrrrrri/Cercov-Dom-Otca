<?php
class Slushenia_Users {
    private $PDO;
    private $table = "Slushenia_Users";

    public $id;
    public $id_Users;
    public $id_Sluchenia;
    
    public function __construct($db) {
        $this->PDO = $db;
    }

    public function getAll() {
        $query = "SELECT * FROM {$this->table} ORDER BY id";
        $stmt = $this->PDO->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getBySlucheniaId($id) {
        $query = "SELECT * FROM {$this->table} WHERE id_Sluchenia = ?";
        $stmt = $this->PDO->prepare($query);
        $stmt->execute([$id]);
        return $stmt;
    }

    public function getByID($id) {
        $this->id = $id;
        $query = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 0,1";
        $stmt = $this->PDO->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function add() {
        $query = "INSERT INTO {$this->table} (id_Users, id_Sluchenia) VALUES (?, ?)";
        $stmt = $this->PDO->prepare($query);
        $stmt->bindParam(1, $this->id_Users);
        $stmt->bindParam(2, $this->id_Sluchenia);
        return $stmt->execute();
    }

    public function edit($id) {
        $this->id = $id;
        $query = "UPDATE {$this->table} SET id_Users = ?, id_Sluchenia = ? WHERE id = ?";
        $stmt = $this->PDO->prepare($query);
        $stmt->bindValue(1, $this->id_Users);
        $stmt->bindValue(2, $this->id_Sluchenia);
        $stmt->bindValue(3, $this->id);
        return $stmt->execute();
    }

    public function remove($id) {
        $this->id = $id;
        $query = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->PDO->prepare($query);
        $stmt->bindParam(1, $this->id);
        return $stmt->execute();
    }

    public function addMember($user_id, $sluchenia_id) {
        $this->id_Users = $user_id;
        $this->id_Sluchenia = $sluchenia_id;
        return $this->add();
    }
    
    public function removeMember($user_id, $sluchenia_id) {
        $query = "DELETE FROM {$this->table} 
                  WHERE id_Users = ? AND id_Sluchenia = ?";
        $stmt = $this->PDO->prepare($query);
        return $stmt->execute([$user_id, $sluchenia_id]);
    }
    
    public function getTeamMembers($sluchenia_id) {
        $query = "SELECT u.* FROM Users u
                  JOIN {$this->table} su ON u.id = su.id_Users
                  WHERE su.id_Sluchenia = ?";
        $stmt = $this->PDO->prepare($query);
        $stmt->execute([$sluchenia_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUserMinistries($user_id) {
        $query = "SELECT s.* FROM Sluchenia s
                  JOIN Slushenia_Users su ON s.id = su.id_Sluchenia
                  WHERE su.id_Users = ?";
        $stmt = $this->PDO->prepare($query);
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    
}




