<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST"); // Dùng POST/PUT

include_once '../../config/db_connect.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

// Kiểm tra ID sách
if (empty($data->book_id)) {
    http_response_code(400);
    echo json_encode(array("message" => "Thiếu ID sách cần cập nhật."));
    exit();
}

$book_id = $data->book_id;
$new_quantity = isset($data->quantity) ? (int)$data->quantity : null;

try {
    $db->beginTransaction(); // Bắt đầu giao dịch để đảm bảo tính toàn vẹn

    // 1. Lấy thông tin hiện tại của sách
    $select_query = "SELECT quantity, available_copies FROM books WHERE book_id = :id LIMIT 0,1";
    $stmt_select = $db->prepare($select_query);
    $stmt_select->bindParam(':id', $book_id);
    $stmt_select->execute();
    $row = $stmt_select->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        http_response_code(404);
        echo json_encode(array("message" => "Không tìm thấy sách."));
        $db->rollBack();
        exit();
    }

    $old_quantity = $row['quantity'];
    $old_available = $row['available_copies'];
    $borrowed = $old_quantity - $old_available; // Số sách đang được mượn

    $update_query = "UPDATE books SET ";
    $set_fields = [];

    // 2. Xử lý cập nhật Số lượng (Quantity)
    if ($new_quantity !== null) {
        if ($new_quantity < $borrowed) {
            http_response_code(400);
            echo json_encode(array("message" => "Số lượng mới không thể nhỏ hơn số sách đang được mượn ({$borrowed})."));
            $db->rollBack();
            exit();
        }

        $new_available = $new_quantity - $borrowed; // Tính lại số lượng còn mượn được
        $set_fields[] = "quantity = :quantity";
        $set_fields[] = "available_copies = :available_copies";
    }

    // 3. Xử lý cập nhật các trường khác
    if (isset($data->title)) $set_fields[] = "title = :title";
    if (isset($data->author)) $set_fields[] = "author = :author";
    if (isset($data->publisher)) $set_fields[] = "publisher = :publisher";
    if (isset($data->publication_year)) $set_fields[] = "publication_year = :year";

    if (empty($set_fields)) {
        http_response_code(400);
        echo json_encode(array("message" => "Không có trường nào được cung cấp để cập nhật."));
        $db->rollBack();
        exit();
    }

    $update_query .= implode(", ", $set_fields) . " WHERE book_id = :id";
    $stmt = $db->prepare($update_query);

    // Gán giá trị
    $stmt->bindParam(':id', $book_id);
    if (isset($data->title)) $stmt->bindParam(":title", htmlspecialchars(strip_tags($data->title)));
    if (isset($data->author)) $stmt->bindParam(":author", htmlspecialchars(strip_tags($data->author)));
    if (isset($data->publisher)) $stmt->bindParam(":publisher", htmlspecialchars(strip_tags($data->publisher)));
    if (isset($data->publication_year)) $stmt->bindParam(":year", htmlspecialchars(strip_tags($data->publication_year)));

    if ($new_quantity !== null) {
        $stmt->bindParam(":quantity", $new_quantity);
        $stmt->bindParam(":available_copies", $new_available);
    }

    $stmt->execute();
    $db->commit(); // Hoàn tất giao dịch

    http_response_code(200); // OK
    echo json_encode(array("message" => "Thông tin sách đã được cập nhật."));
} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    http_response_code(500);
    echo json_encode(array("message" => "Lỗi khi cập nhật sách: " . $e->getMessage()));
}
