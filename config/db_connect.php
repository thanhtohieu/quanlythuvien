<?php
class Database
{
    private $host = "localhost";
    private $db_name = "library"; // Thay bằng tên database bạn đã tạo
    private $username = "root";   // Tên người dùng MySQL của bạn (mặc định là root trên XAMPP)
    private $password = "";       // Mật khẩu MySQL của bạn (mặc định là rỗng trên XAMPP)
    public $conn;

    public function getConnection()
    {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
        } catch (PDOException $exception) {
            echo "Lỗi kết nối CSDL: " . $exception->getMessage();
        }
        return $this->conn;
    }
}
