<?php
// backend/book/read_single.php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');

include_once '../../config/db_connect.php';
include_once '../../models/Book.php';

$database = new Database();
$db = $database->getConnection();
$book = new Book($db);

// Lấy ID từ URL (ví dụ: ?id=5)
$book->book_id = isset($_GET['id']) ? $_GET['id'] : die();

// Lấy thông tin sách
if ($book->read_single()) {
    // Tạo mảng dữ liệu sách
    $book_arr = array(
        "book_id" => $book->book_id,
        "title" => $book->title,
        "author" => $book->author,
        "publisher" => $book->publisher,
        "publication_year" => $book->publication_year,
        "quantity" => $book->quantity,
        "available_copies" => $book->available_copies,
        "description" => $book->description,
        "isbn" => $book->isbn,
        "genre" => $book->genre
    );

    http_response_code(200);
    echo json_encode($book_arr);
} else {
    http_response_code(404);
    echo json_encode(array("message" => "Không tìm thấy sách."));
}
