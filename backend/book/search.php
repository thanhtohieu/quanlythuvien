<?php
// backend/book/search.php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');

include_once '../../config/db_connect.php';
include_once '../../models/Book.php'; // Sử dụng lại Book model

$database = new Database();
$db = $database->getConnection();

// Lấy từ khóa tìm kiếm từ URL (ví dụ: ?q=Lập trình)
$keywords = isset($_GET['q']) ? $_GET['q'] : '';

$book = new Book($db);

// Gọi hàm tìm kiếm mới trong model
$result = $book->search($keywords);
$num = $result->rowCount();

if ($num > 0) {
    $books_arr = array();
    $books_arr['data'] = array();

    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        $book_item = array(
            'book_id' => $book_id,
            'book_title' => $title, // Tên cột trong CSDL của bạn là 'title'
            'author' => $author,
            'quantity' => $quantity,
            'available_quantity' => $available_copies // Tên cột là 'available_copies'
        );
        array_push($books_arr['data'], $book_item);
    }
    http_response_code(200);
    echo json_encode($books_arr);
} else {
    http_response_code(200);
    echo json_encode(['data' => [], 'message' => 'No books found.']);
}
