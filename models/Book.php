<?php
class Book
{
    private $conn;
    private $table = 'books';

    // ... (các thuộc tính)

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Hàm lấy tất cả sách
    public function read()
    {
        $query = 'SELECT * FROM ' . $this->table . ' ORDER BY book_id DESC';
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // ... (các hàm khác)
}
