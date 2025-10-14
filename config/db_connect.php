<?php
class Database
{
    private $host = "127.0.0.1";
    private $port = "3306"; // Thêm dòng port
    private $db_name = "library";
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection()
    {
        $this->conn = null;
        try {
            // Xây dựng chuỗi DSN đúng chuẩn, có cả host và port
            $dsn = "mysql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name;

            $this->conn = new PDO($dsn, $this->username, $this->password);

            // Cách làm hiện đại để set charset
            $this->conn->exec("set names utf8mb4");

            // Thiết lập chế độ báo lỗi để dễ debug
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $exception) {
            // Tạm thời hiển thị lỗi để debug, sau này nên đổi thành ghi log
            die("Lỗi kết nối CSDL: " . $exception->getMessage());
        }
        return $this->conn;
    }
}
