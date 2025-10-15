<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: PUT");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403); // 403 Forbidden
    echo json_encode(["message" => "Truy cập bị từ chối. Bạn không có quyền thực hiện chức năng này."]);
    exit(); // Dừng thực thi ngay lập tức
}

include_once '../../config/db_connect.php'; // Đảm bảo đường dẫn chính xác

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

    // 2. Kiểm tra giao dịch có tồn tại và đang ở trạng thái "BORROWED" không
    $trans_check_query = "SELECT book_id, status FROM transactions WHERE transaction_id = :transaction_id LIMIT 1 FOR UPDATE";
    $stmt_trans_check = $db->prepare($trans_check_query);
    $stmt_trans_check->bindParam(':transaction_id', $transaction_id);
    $stmt_trans_check->execute();
    $trans_row = $stmt_trans_check->fetch(PDO::FETCH_ASSOC);

    if (!$trans_row) {
        throw new Exception("Không tìm thấy giao dịch.");
    }

    if ($trans_row['status'] !== 'BORROWED') {
        throw new Exception("Giao dịch này không ở trạng thái đang mượn (có thể đã được trả hoặc có lỗi).");
    }

    $book_id = $trans_row['book_id'];

    // 3. Cập nhật trạng thái giao dịch
    $update_trans_query = "UPDATE transactions 
                           SET 
                               status = 'RETURNED', 
                               return_date = CURDATE() 
                           WHERE transaction_id = :transaction_id";
    $stmt_update_trans = $db->prepare($update_trans_query);
    $stmt_update_trans->bindParam(':transaction_id', $transaction_id);

    if (!$stmt_update_trans->execute()) {
        throw new Exception("Không thể cập nhật trạng thái giao dịch.");
    }

    // 4. Tăng số lượng sách có sẵn
    $update_book_query = "UPDATE books 
                          SET available_copies = available_copies + 1 
                          WHERE book_id = :book_id";
    $stmt_update_book = $db->prepare($update_book_query);
    $stmt_update_book->bindParam(':book_id', $book_id);

    if (!$stmt_update_book->execute()) {
        throw new Exception("Không thể cập nhật số lượng sách.");
    }

    $db->commit(); // KẾT THÚC GIAO DỊCH (Thành công)

    http_response_code(200); // OK
    echo json_encode(array("message" => "Trả sách thành công!"));
} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack(); // HOÀN LẠI (Thất bại)
    }
    http_response_code(500);
    echo json_encode(array("message" => "Lỗi khi trả sách: " . $e->getMessage()));
}
