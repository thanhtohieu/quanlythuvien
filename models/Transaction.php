<?php
// models/Transaction.php

class Transaction
{
    private $conn;
    private $table = 'transactions';

    // Các thuộc tính
    public $transaction_id;
    public $book_id;
    public $reader_id;
    // ...

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Hàm đọc tất cả các giao dịch
    public function read()
    {
        // Câu truy vấn này sử dụng LEFT JOIN để lấy thông tin từ 3 bảng
        $query = 'SELECT
            t.transaction_id,
            b.title as book_title,
            r.name as reader_name,
            t.borrow_date,
            t.due_date,
            t.return_date,
            t.status
        FROM
            ' . $this->table . ' t
        LEFT JOIN
            books b ON t.book_id = b.book_id
        LEFT JOIN
            readers r ON t.reader_id = r.reader_id
        ORDER BY
            t.borrow_date DESC';

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }
}
