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

$reader->name = $data->name;
$reader->student_id = $data->student_id;
$reader->contact_info = $data->contact_info;

// Sửa lại đoạn code này trong backend/reader/create.php

try {
    if ($reader->create()) {
        http_response_code(201);
        echo json_encode(["message" => "Độc giả đã được tạo."]);
    } else {
        // Trường hợp execute() trả về false mà không rõ lý do
        http_response_code(503);
        echo json_encode(["message" => "Không thể tạo độc giả do lỗi không xác định."]);
    }
} catch (PDOException $e) {
    http_response_code(409); // 409 Conflict
    echo json_encode([
        "message" => "Không thể tạo độc giả. Có thể Mã số sinh viên đã tồn tại.",
        "error" => $e->getMessage() // Thêm dòng này để xem lỗi chi tiết
    ]);
}
