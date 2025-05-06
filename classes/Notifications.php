<?php
class Notifications {
    private $PDO;
    private $table = "Notifications";

    public function __construct($db) {
        $this->PDO = $db;
    }

    public function createForUser($user_id, $message) {
        $stmt = $this->PDO->prepare("INSERT INTO Notifications (user_id, text) VALUES (?, ?)");
        $result = $stmt->execute([$user_id, $message]);
        
        // Добавьте отладочный вывод
        error_log("Создано уведомление для user_id: $user_id, текст: $message");
        if(!$result) {
            error_log("Ошибка: " . implode(", ", $stmt->errorInfo()));
        }
        
        return $result;
    }
    public function createForSlucheniaLeader($sluchenie_id, $message) {
        $stmt = $this->PDO->prepare("SELECT Lider FROM Sluchenia WHERE id = ?");
        $stmt->execute([$sluchenie_id]);
        $leader_id = $stmt->fetchColumn();
        if ($leader_id) {
            $this->createForUser($leader_id, $message);
        }
    }

    public function getAllForUser($user_id) {
        $stmt = $this->PDO->prepare("SELECT * FROM {$this->table} WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUnreadCount($user_id) {
        $stmt = $this->PDO->prepare("SELECT COUNT(*) FROM {$this->table} WHERE user_id = ? AND is_read = 0");
        $stmt->execute([$user_id]);
        return $stmt->fetchColumn();
    }

    public function markAsRead($notification_id) {
        $stmt = $this->PDO->prepare("UPDATE {$this->table} SET is_read = 1 WHERE id = ?");
        return $stmt->execute([$notification_id]);
    }

    public function deleteAllForUser($user_id) {
        $stmt = $this->PDO->prepare("DELETE FROM {$this->table} WHERE user_id = ?");
        return $stmt->execute([$user_id]);
    }

    public function getUnread($user_id) {
        $stmt = $this->PDO->prepare("SELECT * FROM Notifications WHERE user_id = ? AND is_read = 0");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
}
