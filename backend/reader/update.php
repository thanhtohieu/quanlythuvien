<?php
session_start();
header('Content-Type: application/json');

// KIỂM TRA QUYỀN ADMIN
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(["message" => "Truy cập bị từ chối."]);
    exit();
}

include_once '../../config/db_connect.php';
include_once '../../models/Reader.php';

$database = new Database();
$db = $database->getConnection();
$reader = new Reader($db);

$data = json_decode(file_get_contents("php://input"));

$reader->reader_id = $data->reader_id; // Quan trọng cho lệnh WHERE
$reader->name = $data->name;
$reader->student_id = $data->student_id;
$reader->contact_info = $data->contact_info;

if ($reader->update()) {
    http_response_code(200);
    echo json_encode(["message" => "Cập nhật thông tin độc giả thành công."]);
} else {
    http_response_code(503);
    echo json_encode(["message" => "Không thể cập nhật thông tin."]);
}
