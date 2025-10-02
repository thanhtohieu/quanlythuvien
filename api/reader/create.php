<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

include_once '../../config/db_connect.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

// Kiểm tra dữ liệu bắt buộc
if (!empty($data->name) && !empty($data->student_id)) {

    $query = "INSERT INTO readers 
              SET 
                  name=:name, 
                  student_id=:student_id, 
                  contact_info=:contact_info";

    $stmt = $db->prepare($query);

    // Làm sạch dữ liệu và gán tham số
    $name = htmlspecialchars(strip_tags($data->name));
    $student_id = htmlspecialchars(strip_tags($data->student_id));
    $contact_info = htmlspecialchars(strip_tags($data->contact_info));

    $stmt->bindParam(":name", $name);
    $stmt->bindParam(":student_id", $student_id);
    $stmt->bindParam(":contact_info", $contact_info);

    try {
        if ($stmt->execute()) {
            http_response_code(201); // Created
            echo json_encode(array("message" => "Độc giả đã được thêm thành công."));
        } else {
            http_response_code(503); // Service Unavailable
            echo json_encode(array("message" => "Lỗi máy chủ: Không thể thêm độc giả."));
        }
    } catch (PDOException $e) {
        // Lỗi 23000 là mã lỗi phổ biến cho trùng lặp UNIQUE KEY (student_id)
        if ($e->getCode() == '23000') {
            http_response_code(400);
            echo json_encode(array("message" => "Mã độc giả (Student ID) đã tồn tại."));
        } else {
            http_response_code(500);
            echo json_encode(array("message" => "Lỗi CSDL: " . $e->getMessage()));
        }
    }
} else {
    http_response_code(400); // Bad Request
    echo json_encode(array("message" => "Thiếu tên hoặc mã độc giả bắt buộc."));
}
