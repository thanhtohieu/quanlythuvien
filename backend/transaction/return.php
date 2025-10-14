<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

include_once '../../config/db_connect.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

// 1. Kiểm tra dữ liệu bắt buộc
if (empty($data->transaction_id)) {
    http_response_code(400);
    echo json_encode(array("message" => "Thiếu Transaction ID."));
    exit();
}

$transaction_id = $data->transaction_id;

try {
    $db->beginTransaction(); // BẮT ĐẦU GIAO DỊCH

    // 2. Lấy thông tin giao dịch hiện tại
    $select_query = "SELECT book_id, status FROM transactions WHERE transaction_id = :id LIMIT 1 FOR UPDATE";
    $stmt_select = $db->prepare($select_query);
    $stmt_select->bindParam(':id', $transaction_id);
    $stmt_select->execute();
    $transaction_row = $stmt_select->fetch(PDO::FETCH_ASSOC);

    if (!$transaction_row) {
        http_response_code(404);
        echo json_encode(array("message" => "Không tìm thấy giao dịch."));
        $db->rollBack();
        exit();
    }
    if ($transaction_row['status'] != 'BORROWED') {
        http_response_code(400);
        echo json_encode(array("message" => "Giao dịch này đã được trả trước đó."));
        $db->rollBack();
        exit();
    }

    $book_id = $transaction_row['book_id'];

    // 3. Thực hiện Trả: Cập nhật bảng transactions
    $return_query = "UPDATE transactions 
                     SET 
                         return_date = CURDATE(), 
                         status = 'RETURNED' 
                     WHERE 
                         transaction_id = :id";
    $stmt_return = $db->prepare($return_query);
    $stmt_return->bindParam(':id', $transaction_id);

    if (!$stmt_return->execute()) {
        throw new Exception("Không thể cập nhật trạng thái trả sách.");
    }

    // 4. Tăng Tồn Kho: Cập nhật available_copies
    $update_book_query = "UPDATE books 
                          SET available_copies = available_copies + 1 
                          WHERE book_id = :book_id";
    $stmt_update = $db->prepare($update_book_query);
    $stmt_update->bindParam(':book_id', $book_id);

    if (!$stmt_update->execute()) {
        throw new Exception("Không thể cập nhật số lượng sách tồn kho.");
    }

    $db->commit(); // KẾT THÚC GIAO DỊCH (Thành công)

    http_response_code(200); // OK
    echo json_encode(array("message" => "Trả sách thành công!"));
} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack(); // HOÀN LẠI (Thất bại)
    }
    http_response_code(500);
    echo json_encode(array("message" => "Lỗi giao dịch trả sách: " . $e->getMessage()));
}
