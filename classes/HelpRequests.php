<?php
class HelpRequests {
    private $PDO;
    private $table = "HelpRequests";

    public function __construct($db) {
        $this->PDO = $db;
        $this->createTableIfNotExists();
    }

    private function createTableIfNotExists() {
        $this->PDO->exec("
            CREATE TABLE IF NOT EXISTS {$this->table} (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                sluchenie_id INT NOT NULL,
                status ENUM('pending','approved','rejected') DEFAULT 'pending',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES Users(id),
                FOREIGN KEY (sluchenie_id) REFERENCES Sluchenia(id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
    }

    public function create($user_id, $sluchenie_id) {
        $stmt = $this->PDO->prepare("INSERT INTO {$this->table} (user_id, sluchenie_id) VALUES (?, ?)");
        return $stmt->execute([$user_id, $sluchenie_id]);
    }

    public function exists($user_id, $sluchenie_id) {
        $stmt = $this->PDO->prepare("SELECT id FROM {$this->table} WHERE user_id = ? AND sluchenie_id = ? AND status = 'pending'");
        $stmt->execute([$user_id, $sluchenie_id]);
        return $stmt->fetch() !== false;
    }

    public function approve($request_id) {
        $stmt = $this->PDO->prepare("UPDATE {$this->table} SET status = 'approved' WHERE id = ?");
        return $stmt->execute([$request_id]);
    }

    public function reject($request_id) {
        $stmt = $this->PDO->prepare("UPDATE {$this->table} SET status = 'rejected' WHERE id = ?");
        return $stmt->execute([$request_id]);
    }

    public function getPendingForSluchenie($sluchenia_id) {
        $stmt = $this->PDO->prepare("SELECT * FROM {$this->table} WHERE sluchenie_id = ? AND status = 'pending'");
        $stmt->execute([$sluchenia_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
     public function getById($id) {
        $stmt = $this->PDO->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
