<?php
session_start();
header('Content-Type: application/json');

// --- KIỂM TRA QUYỀN ADMIN --- (Tương tự file create)

include_once '../../config/db_connect.php';
include_once '../../models/Book.php';

$database = new Database();
$db = $database->getConnection();
$book = new Book($db);

$data = json_decode(file_get_contents("php://input"));

$book->book_id = $data->book_id;

if ($book->delete()) {
    http_response_code(200);
    echo json_encode(array("message" => "Sách đã được xóa."));
} else {
    http_response_code(503);
    echo json_encode(array("message" => "Không thể xóa sách."));
}
