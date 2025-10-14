<?php
// models/Book.php

class Book
{
    private $conn;
    private $table = 'books';

    // --- THÊM PHẦN KHAI BÁO THUỘC TÍNH VÀO ĐÂY ---
    public $book_id;
    public $title;
    public $author;
    public $publisher;
    public $publication_year;
    public $description;
    public $isbn;
    public $genre;
    public $quantity;
    public $available_copies;
    // -----------------------------------------

    // Hàm khởi tạo
    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Hàm đọc tất cả sách
    public function read()
    {
        $query = 'SELECT * FROM ' . $this->table . ' ORDER BY book_id DESC';
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Hàm tạo sách mới
    public function create()
    {
        $query = 'INSERT INTO ' . $this->table . ' SET title = :title, author = :author, publisher = :publisher, publication_year = :publication_year, quantity = :quantity, available_copies = :quantity';

        $stmt = $this->conn->prepare($query);

        // Làm sạch dữ liệu (bạn có thể thêm các thuộc tính mới vào đây)
        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->author = htmlspecialchars(strip_tags($this->author));
        $this->publisher = htmlspecialchars(strip_tags($this->publisher));
        $this->publication_year = htmlspecialchars(strip_tags($this->publication_year));
        $this->quantity = htmlspecialchars(strip_tags($this->quantity));

        // Bind data
        $stmt->bindParam(':title', $this->title);
        $stmt->bindParam(':author', $this->author);
        $stmt->bindParam(':publisher', $this->publisher);
        $stmt->bindParam(':publication_year', $this->publication_year);
        $stmt->bindParam(':quantity', $this->quantity);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Hàm xóa sách
    public function delete()
    {
        $query = 'DELETE FROM ' . $this->table . ' WHERE book_id = :book_id';
        $stmt = $this->conn->prepare($query);

        $this->book_id = htmlspecialchars(strip_tags($this->book_id));
        $stmt->bindParam(':book_id', $this->book_id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Bạn có thể thêm hàm update() ở đây sau
}
