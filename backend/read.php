<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');

// ĐƯỜNG DẪN ĐÚNG KHI FILE NẰM TRONG THƯ MỤC 'backend'
include_once '../config/db_connect.php';
include_once '../models/Book.php';

$database = new Database();
$db = $database->getConnection();

$book = new Book($db);

$result = $book->read();
$num = $result->rowCount();

if ($num > 0) {
    $books_arr = array();
    $books_arr['data'] = array();

    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        $book_item = array(
            'book_id' => $book_id,
            'book_title' => $title,
            'author' => $author,
            'quantity' => $quantity,
            'available_quantity' => $available_copies
        );
        array_push($books_arr['data'], $book_item);
    }
    http_response_code(200);
    echo json_encode($books_arr);
} else {
    // Nếu không có sách nào, vẫn trả về JSON hợp lệ với mảng rỗng
    http_response_code(200);
    echo json_encode(['data' => [], 'message' => 'No Books Found']);
}
