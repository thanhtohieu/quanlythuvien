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
    echo json_encode(array("message" => "Thiếu ID độc giả cần xóa."));
    exit();
}

$reader_id = $data->reader_id;

// 1. Kiểm tra xem độc giả có đang mượn sách hay không
$check_query = "SELECT COUNT(*) AS total_borrowed FROM transactions WHERE reader_id = :id AND status = 'BORROWED'";
$stmt_check = $db->prepare($check_query);
$stmt_check->bindParam(':id', $reader_id);
$stmt_check->execute();
$row = $stmt_check->fetch(PDO::FETCH_ASSOC);

if ($row['total_borrowed'] > 0) {
    http_response_code(400);
    echo json_encode(array("message" => "Không thể xóa độc giả vì họ đang mượn {$row['total_borrowed']} cuốn sách chưa trả."));
    exit();
}

// 2. Thực hiện xóa
$delete_query = "DELETE FROM readers WHERE reader_id = :id";
$stmt = $db->prepare($delete_query);
$stmt->bindParam(':id', $reader_id);

if ($stmt->execute()) {
    http_response_code(200); // OK
    echo json_encode(array("message" => "Độc giả đã được xóa thành công."));
} else {
    http_response_code(503); // Service Unavailable
    echo json_encode(array("message" => "Lỗi máy chủ: Không thể xóa độc giả."));
}
