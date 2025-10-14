<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

include_once '../../config/db_connect.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

// Kiểm tra ID sách
if (empty($data->book_id)) {
    http_response_code(400);
    echo json_encode(array("message" => "Thiếu ID sách cần xóa."));
    exit();
}

$book_id = $data->book_id;

// 1. Kiểm tra xem sách có đang được mượn hay không
$check_query = "SELECT quantity, available_copies FROM books WHERE book_id = :id LIMIT 0,1";
$stmt_check = $db->prepare($check_query);
$stmt_check->bindParam(':id', $book_id);
$stmt_check->execute();
$row = $stmt_check->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    http_response_code(404);
    echo json_encode(array("message" => "Không tìm thấy sách."));
    exit();
}

if ($row['quantity'] != $row['available_copies']) {
    http_response_code(400);
    echo json_encode(array("message" => "Không thể xóa sách vì có bản sao đang được mượn."));
    exit();
}

// 2. Thực hiện xóa
$delete_query = "DELETE FROM books WHERE book_id = :id";
$stmt = $db->prepare($delete_query);
$stmt->bindParam(':id', $book_id);

if ($stmt->execute()) {
    http_response_code(200); // OK
    echo json_encode(array("message" => "Sách đã được xóa thành công."));
} else {
    http_response_code(503); // Service Unavailable
    echo json_encode(array("message" => "Lỗi máy chủ: Không thể xóa sách."));
}
