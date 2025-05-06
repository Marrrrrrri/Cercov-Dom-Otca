<?php
class Database
{
    private $host = "localhost";
    private $db_name = "Cercov";
    private $username = "root";
    private $password = "";
    private $charset = "utf8";
    private $port = "3306";
    private $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    public $conn;

    public function getConnection()
    {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host={$this->host};dbname={$this->db_name};charset={$this->charset};port={$this->port}",
                $this->username,
                $this->password,
                $this->options
            );
            // echo "БД подключена";
        } catch (PDOException $exception) {
            echo "Ошибка подключения: " . $exception->getMessage();
        }
        return $this->conn;
    }

    // Метод закрытия подключения БД
    public function closeConnection()
    {
        $this->conn = NULL;
        return NULL;
    }

    // Метод возврата PDO для разработчика, подключение запроса напрямую
    public function getInfPDO()
    {
        return $this->conn;
    }
}