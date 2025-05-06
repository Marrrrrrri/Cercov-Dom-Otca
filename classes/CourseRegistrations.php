<?php
class CourseRegistrations {
    private $PDO;
    private $table = "course_registrations";

    public function __construct($db) {
        $this->PDO = $db;
    }

    public function register($user_id, $course_id) {
        $query = "INSERT INTO {$this->table} (user_id, course_id) VALUES (?, ?)";
        $stmt = $this->PDO->prepare($query);
        return $stmt->execute([$user_id, $course_id]);
    }

    public function getRegistrations($course_id) {
        $query = "SELECT u.* FROM Users u
                 JOIN {$this->table} cr ON u.id = cr.user_id
                 WHERE cr.course_id = ?";
        $stmt = $this->PDO->prepare($query);
        $stmt->execute([$course_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function isRegistered($user_id, $course_id) {
        $query = "SELECT id FROM {$this->table} 
                 WHERE user_id = ? AND course_id = ?";
        $stmt = $this->PDO->prepare($query);
        $stmt->execute([$user_id, $course_id]);
        return $stmt->rowCount() > 0;
    }
}
