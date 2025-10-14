<?php
session_start();
header('Content-Type: application/json');

// --- KIỂM TRA QUYỀN ADMIN ---
// if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
//     http_response_code(403);
//     echo json_encode(["message" => "Truy cập bị từ chối."]);
//     exit();
// }

include_once '../../config/db_connect.php';
include_once '../../models/Book.php';

$database = new Database();
$db = $database->getConnection();
$book = new Book($db);

$data = json_decode(file_get_contents("php://input"));

// Gán dữ liệu vào đối tượng book
$book->title = $data->title;
$book->author = $data->author;
$book->publisher = $data->publisher;
$book->publication_year = $data->publication_year;
$book->quantity = $data->quantity;

// Tạo sách
if ($book->create()) {
    http_response_code(201);
    echo json_encode(array("message" => "Sách đã được tạo."));
} else {
    http_response_code(503);
    echo json_encode(array("message" => "Không thể tạo sách."));
}
