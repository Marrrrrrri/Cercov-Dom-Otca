<?php
class Otsiv {
    private $PDO;
    private $table = "Otsiv";

    public $id;
    public $id_Users;
    public $text;
    public $reiting;
    
    public function __construct($db)
    {
        $this->PDO = $db;
    }

    // Методы
    public function getAll()
    {
        $query = "SELECT * FROM {$this->table} ORDER BY id";
        $stmt = $this->PDO->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getByID($id)
    {
        $this->id = $id;
        $query = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 0,1";
        $stmt = $this->PDO->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row;
    }

    public function add()
    {
        $query = "INSERT INTO {$this->table} 
        (id_Users, text, reiting) 
        VALUES (?,?,?)";
        $stmt = $this->PDO->prepare($query);
        $stmt->bindParam(1, $this->id_Users);
        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public function edit($id)
    {
        $this->id = $id;
        $query = "UPDATE {$this->table} SET id_Users = ? WHERE id = ?";
        $stmt = $this->PDO->prepare($query);
        $stmt->bindValue(1, $this->id_Users);
        $stmt->bindValue(2, $this->id);
        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public function remove($id)
    {
        $this->id = $id;
        $query = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->PDO->prepare($query);
        $stmt->bindParam(1, $this->id);
        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }
}

