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

    // Các hàm khác như create(), update(), delete() bạn có thể tự phát triển sau...
}
