<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST"); // Chỉ chấp nhận POST

include_once '../../config/db_connect.php';

$database = new Database();
$db = $database->getConnection();

// Lấy dữ liệu từ body của yêu cầu POST (dạng JSON)
$data = json_decode(file_get_contents("php://input"));

// Kiểm tra dữ liệu bắt buộc
if (
    !empty($data->title) &&
    !empty($data->author) &&
    isset($data->quantity) && is_numeric($data->quantity) && $data->quantity >= 0
) {

    // Câu lệnh SQL: available_copies = quantity
    $query = "INSERT INTO books 
              SET 
                  title=:title, 
                  author=:author, 
                  publisher=:publisher, 
                  publication_year=:year, 
                  quantity=:quantity,
                  available_copies=:quantity"; // Khóa quan trọng!

    $stmt = $db->prepare($query);

    // Làm sạch dữ liệu và gán tham số
    $title = htmlspecialchars(strip_tags($data->title));
    $author = htmlspecialchars(strip_tags($data->author));
    $publisher = htmlspecialchars(strip_tags($data->publisher));
    $year = htmlspecialchars(strip_tags($data->publication_year));
    $quantity = (int)$data->quantity;

    $stmt->bindParam(":title", $title);
    $stmt->bindParam(":author", $author);
    $stmt->bindParam(":publisher", $publisher);
    $stmt->bindParam(":year", $year);
    $stmt->bindParam(":quantity", $quantity);

    if ($stmt->execute()) {
        http_response_code(201); // Created
        echo json_encode(array("message" => "Sách đã được thêm thành công."));
    } else {
        http_response_code(503); // Service Unavailable
        echo json_encode(array("message" => "Lỗi máy chủ: Không thể thêm sách."));
    }
} else {
    http_response_code(400); // Bad Request
    echo json_encode(array("message" => "Thiếu dữ liệu bắt buộc hoặc số lượng không hợp lệ."));
}
