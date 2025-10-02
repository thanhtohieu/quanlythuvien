<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

include_once '../../config/db_connect.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

// 1. Kiểm tra dữ liệu bắt buộc
if (empty($data->book_id) || empty($data->reader_id) || empty($data->due_date)) {
    http_response_code(400);
    echo json_encode(array("message" => "Thiếu Book ID, Reader ID hoặc Due Date."));
    exit();
}

$book_id = $data->book_id;
$reader_id = $data->reader_id;
$due_date = $data->due_date;

try {
    $db->beginTransaction(); // BẮT ĐẦU GIAO DỊCH

    // 2. Kiểm tra tính hợp lệ của Độc giả và Sách
    // Đảm bảo sách tồn tại và còn bản sao để mượn
    $book_check_query = "SELECT available_copies FROM books WHERE book_id = :book_id LIMIT 1 FOR UPDATE"; // Khóa hàng để tránh Race Condition
    $stmt_book_check = $db->prepare($book_check_query);
    $stmt_book_check->bindParam(':book_id', $book_id);
    $stmt_book_check->execute();
    $book_row = $stmt_book_check->fetch(PDO::FETCH_ASSOC);

    if (!$book_row) {
        http_response_code(404);
        echo json_encode(array("message" => "Lỗi: Không tìm thấy sách."));
        $db->rollBack();
        exit();
    }
    if ($book_row['available_copies'] <= 0) {
        http_response_code(400);
        echo json_encode(array("message" => "Lỗi: Sách đã hết bản sao có sẵn để mượn."));
        $db->rollBack();
        exit();
    }

    // 3. Thực hiện Mượn: INSERT vào bảng transactions
    $borrow_query = "INSERT INTO transactions 
                     SET 
                         book_id = :book_id, 
                         reader_id = :reader_id, 
                         borrow_date = CURDATE(), 
                         due_date = :due_date, 
                         status = 'BORROWED'";
    $stmt_borrow = $db->prepare($borrow_query);
    $stmt_borrow->bindParam(':book_id', $book_id);
    $stmt_borrow->bindParam(':reader_id', $reader_id);
    $stmt_borrow->bindParam(':due_date', $due_date);

    if (!$stmt_borrow->execute()) {
        throw new Exception("Không thể tạo giao dịch mượn.");
    }

    // 4. Giảm Tồn Kho: Cập nhật available_copies
    $update_book_query = "UPDATE books 
                          SET available_copies = available_copies - 1 
                          WHERE book_id = :book_id";
    $stmt_update = $db->prepare($update_book_query);
    $stmt_update->bindParam(':book_id', $book_id);

    if (!$stmt_update->execute()) {
        throw new Exception("Không thể cập nhật số lượng sách tồn kho.");
    }

    $db->commit(); // KẾT THÚC GIAO DỊCH (Thành công)

    http_response_code(201); // Created
    echo json_encode(array("message" => "Mượn sách thành công!"));
} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack(); // HOÀN LẠI (Thất bại)
    }
    http_response_code(500);
    echo json_encode(array("message" => "Lỗi giao dịch mượn sách: " . $e->getMessage()));
}
