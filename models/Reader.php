<?php
// models/Reader.php

class Reader
{
    private $conn;
    private $table = 'readers';

    // Các thuộc tính của Độc giả
    public $reader_id;
    public $name;
    public $student_id;
    public $contact_info;

    // Hàm khởi tạo, nhận vào kết nối CSDL
    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Hàm đọc tất cả độc giả
    public function read()
    {
        // Tạo câu truy vấn
        $query = 'SELECT * FROM ' . $this->table . ' ORDER BY reader_id DESC';

        // Chuẩn bị câu lệnh
        $stmt = $this->conn->prepare($query);

        // Thực thi câu lệnh
        $stmt->execute();

        return $stmt;
    }

    // Hàm tạo độc giả mới
    public function create()
    {
        $query = 'INSERT INTO ' . $this->table . ' SET name = :name, student_id = :student_id, contact_info = :contact_info';
        $stmt = $this->conn->prepare($query);

        // Làm sạch dữ liệu
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->student_id = htmlspecialchars(strip_tags($this->student_id));
        $this->contact_info = htmlspecialchars(strip_tags($this->contact_info));

        // Bind data
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':student_id', $this->student_id);
        $stmt->bindParam(':contact_info', $this->contact_info);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Hàm xóa độc giả
    public function delete()
    {
        $query = 'DELETE FROM ' . $this->table . ' WHERE reader_id = :reader_id';
        $stmt = $this->conn->prepare($query);

        $this->reader_id = htmlspecialchars(strip_tags($this->reader_id));
        $stmt->bindParam(':reader_id', $this->reader_id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Các hàm khác như create(), update(), delete() bạn có thể tự phát triển sau...
}
