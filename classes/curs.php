<?php
class Curs {
    private $PDO;
    private $table = "curs";
    private $upload_dir = "../uploads/courses/";
    private $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
    private $max_size = 2 * 1024 * 1024; // 2MB

    public $id;
    public $Name;
    public $Cratcoe;
    public $Content;
    public $Time;
    public $Avtor;
    public $DataStart;
    public $img;
    
    public function __construct($db) {
        $this->PDO = $db;
        
        if (!file_exists($this->upload_dir)) {
            mkdir($this->upload_dir, 0777, true);
        }
    }

    public function uploadImage($file) {
        // Проверка типа файла
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        $allowed_mimes = ['image/jpeg', 'image/png', 'image/gif'];
        
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if(!in_array($extension, $this->allowed_types) || !in_array($mime, $allowed_mimes)) {
            throw new Exception("Допустимы только JPG, PNG и GIF файлы");
        }

        // Проверка размера
        if($file['size'] > $this->max_size) {
            throw new Exception("Максимальный размер файла 2MB");
        }

        // Генерация уникального имени
        $new_name = uniqid() . '.' . $extension;
        $target_path = $this->upload_dir . $new_name;

        if(!move_uploaded_file($file['tmp_name'], $target_path)) {
            throw new Exception("Ошибка загрузки файла");
        }

        return $new_name;
    }

    public function getAll() {
        $query = "SELECT * FROM {$this->table} ORDER BY id";
        $stmt = $this->PDO->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getByID($id) {
        $query = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        $stmt = $this->PDO->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create() {
        $query = "INSERT INTO {$this->table} 
            (Name, Cratcoe, Content, Time, Avtor, DataStart, img) 
            VALUES (:name, :cratcoe, :content, :time, :avtor, :datastart, :img)";
        
        $stmt = $this->PDO->prepare($query);
        $stmt->bindParam(':name', $this->Name);
        $stmt->bindParam(':cratcoe', $this->Cratcoe);
        $stmt->bindParam(':content', $this->Content);
        $stmt->bindParam(':time', $this->Time);
        $stmt->bindParam(':avtor', $this->Avtor);
        $stmt->bindParam(':datastart', $this->DataStart);
        $stmt->bindParam(':img', $this->img);
        
        return $stmt->execute();
    }

    public function edit() {
        $query = "UPDATE {$this->table} SET 
            Name = :name,
            Cratcoe = :cratcoe,
            Content = :content,
            Time = :time,
            Avtor = :avtor,
            DataStart = :datastart,
            img = :img
            WHERE id = :id";
        
        $stmt = $this->PDO->prepare($query);
        $stmt->bindParam(':name', $this->Name);
        $stmt->bindParam(':cratcoe', $this->Cratcoe);
        $stmt->bindParam(':content', $this->Content);
        $stmt->bindParam(':time', $this->Time);
        $stmt->bindParam(':avtor', $this->Avtor);
        $stmt->bindParam(':datastart', $this->DataStart);
        $stmt->bindParam(':img', $this->img);
        $stmt->bindParam(':id', $this->id);
        
        return $stmt->execute();
    }

    public function delete($id) {
        // Удаляем связанное изображение
        $course = $this->getByID($id);
        if($course && $course['img']) {
            $file_path = $this->upload_dir . $course['img'];
            if(file_exists($file_path)) {
                unlink($file_path);
            }
        }

        $query = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->PDO->prepare($query);
        return $stmt->execute([$id]);
    }

    public function getUserCourses($user_id) {
        $query = "SELECT c.* FROM curs c
                 JOIN course_registrations cr ON c.id = cr.course_id
                 WHERE cr.user_id = ?";
        $stmt = $this->PDO->prepare($query);
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
