<?php
class Event {
    private $PDO;
    private $table = "event";
    private $upload_dir = "../style/img/";
    private $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
    private $max_size = 2 * 1024 * 1024; // 2MB

    public $id;
    public $Name;
    public $Content;
    public $DataStart;
    public $DataEnd;
    public $Adress;
    public $Image;
    
    public function __construct($db) {
        $this->PDO = $db;
        
        if (!file_exists($this->upload_dir)) {
            mkdir($this->upload_dir, 0777, true);
        }
    }

    public function getAll() {
        $query = "SELECT * FROM {$this->table} ORDER BY DataStart DESC";
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
        return $stmt->fetch(PDO::FETCH_ASSOC);
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


        // Генерация уникального имени
        $new_name = uniqid() . '.' . $extension;
        $target_path = $this->upload_dir . $new_name;

        if(!move_uploaded_file($file['tmp_name'], $target_path)) {
            throw new Exception("Ошибка загрузки файла");
        }

        return $new_name;
    }

    public function add() {
        $query = "INSERT INTO {$this->table} 
            (Name, Content, DataStart, DataEnd, Adress, Image) 
            VALUES (:name, :content, :datastart, :dataend, :adress, :image)";
        
        $stmt = $this->PDO->prepare($query);
        $stmt->bindParam(':name', $this->Name);
        $stmt->bindParam(':content', $this->Content);
        $stmt->bindParam(':datastart', $this->DataStart);
        $stmt->bindParam(':dataend', $this->DataEnd);
        $stmt->bindParam(':adress', $this->Adress);
        $stmt->bindParam(':image', $this->Image);
        
        return $stmt->execute();
    }

    public function edit($id) {
        $query = "UPDATE {$this->table} SET 
            Name = :name,
            Content = :content,
            DataStart = :datastart,
            DataEnd = :dataend,
            Adress = :adress,
            Image = :image
            WHERE id = :id";
        
        $stmt = $this->PDO->prepare($query);
        $stmt->bindParam(':name', $this->Name);
        $stmt->bindParam(':content', $this->Content);
        $stmt->bindParam(':datastart', $this->DataStart);
        $stmt->bindParam(':dataend', $this->DataEnd);
        $stmt->bindParam(':adress', $this->Adress);
        $stmt->bindParam(':image', $this->Image);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }

    public function remove($id) {
        // Удаляем связанное изображение
        $event = $this->getByID($id);
        if($event && $event['Image']) {
            $file_path = $this->upload_dir . $event['Image'];
            if(file_exists($file_path)) {
                unlink($file_path);
            }
        }

        $query = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->PDO->prepare($query);
        $stmt->bindParam(1, $id);
        return $stmt->execute();
    }

    // Дополнительные методы
    public function getUpcomingEvents($limit = 5) {
        $query = "SELECT * FROM {$this->table} 
                 WHERE DataStart >= NOW() 
                 ORDER BY DataStart ASC 
                 LIMIT ?";
        $stmt = $this->PDO->prepare($query);
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function searchByName($search_term) {
        $query = "SELECT * FROM {$this->table} 
                 WHERE Name LIKE :search_term 
                 ORDER BY DataStart DESC";
        $stmt = $this->PDO->prepare($query);
        $search_term = "%{$search_term}%";
        $stmt->bindParam(':search_term', $search_term);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countEvents() {
        $query = "SELECT COUNT(*) FROM {$this->table}";
        $stmt = $this->PDO->query($query);
        return $stmt->fetchColumn();
    }
}
