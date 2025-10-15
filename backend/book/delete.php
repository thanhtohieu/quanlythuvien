<?php
session_start();
header('Content-Type: application/json');

// --- KIỂM TRA QUYỀN ADMIN --- (Tương tự file create)
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403); // 403 Forbidden
    echo json_encode(["message" => "Truy cập bị từ chối. Bạn không có quyền thực hiện chức năng này."]);
    exit(); // Dừng thực thi ngay lập tức
}

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
