<?
class Auth {
    private $user;

    public function __construct() {
        session_start();
        $this->user = $_SESSION['user'] ?? null;
    }

    public function isAdmin() {
        return $this->user && $this->user['admin'] == 1;
    }

    // Добавляем недостающий метод
    public function checkAdminAccess() {
        if(!$this->isAdmin()) {
            header('Location: /login.php?admin=1');
            exit();
        }
    }

    public function isSlucheniaLeader($sluchenia_id) {
        if(!$this->user) return false;
        
        $db = new Database();
        $conn = $db->getConnection();
        
        // Двойная проверка: существование служения и лидерство
        $stmt = $conn->prepare(
            "SELECT COUNT(*) FROM Sluchenia WHERE id = ? AND Lider = ?"
        );
        $stmt->execute([$sluchenia_id, $this->user['id']]);
        
        return $stmt->fetchColumn() > 0;
    }

    
}
