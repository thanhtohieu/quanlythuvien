<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

include_once '../../config/db_connect.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

// Kiểm tra ID độc giả
if (empty($data->reader_id)) {
    http_response_code(400);
    echo json_encode(array("message" => "Thiếu ID độc giả cần cập nhật."));
    exit();
}

$reader_id = $data->reader_id;
$update_query = "UPDATE readers SET ";
$set_fields = [];

// Xử lý các trường cần cập nhật
if (isset($data->name)) $set_fields[] = "name = :name";
if (isset($data->student_id)) $set_fields[] = "student_id = :student_id";
if (isset($data->contact_info)) $set_fields[] = "contact_info = :contact_info";

if (empty($set_fields)) {
    http_response_code(400);
    echo json_encode(array("message" => "Không có trường nào được cung cấp để cập nhật."));
    exit();
}

$update_query .= implode(", ", $set_fields) . " WHERE reader_id = :id";
$stmt = $db->prepare($update_query);

// Gán giá trị
$stmt->bindParam(':id', $reader_id);
if (isset($data->name)) $stmt->bindParam(":name", htmlspecialchars(strip_tags($data->name)));
if (isset($data->student_id)) $stmt->bindParam(":student_id", htmlspecialchars(strip_tags($data->student_id)));
if (isset($data->contact_info)) $stmt->bindParam(":contact_info", htmlspecialchars(strip_tags($data->contact_info)));

try {
    if ($stmt->execute()) {
        http_response_code(200); // OK
        echo json_encode(array("message" => "Thông tin độc giả đã được cập nhật."));
    } else {
        http_response_code(503); // Service Unavailable
        echo json_encode(array("message" => "Không thể cập nhật thông tin độc giả."));
    }
} catch (PDOException $e) {
    if ($e->getCode() == '23000') {
        http_response_code(400);
        echo json_encode(array("message" => "Mã độc giả (Student ID) đã tồn tại trong hệ thống."));
    } else {
        http_response_code(500);
        echo json_encode(array("message" => "Lỗi CSDL: " . $e->getMessage()));
    }
}
