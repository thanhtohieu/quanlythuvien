<?php
// backend/transaction/update.php
session_start();
header('Content-Type: application/json');

// --- KIỂM TRA QUYỀN ADMIN ---
// if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { ... }

include_once '../../config/db_connect.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

if (empty($data->transaction_id) || empty($data->new_status)) {
    http_response_code(400);
    echo json_encode(["message" => "Dữ liệu không hợp lệ."]);
    exit();
}

$db->beginTransaction();

try {
    // Lấy thông tin book_id trước khi cập nhật
    $stmt_select = $db->prepare("SELECT book_id, status FROM transactions WHERE transaction_id = ?");
    $stmt_select->execute([$data->transaction_id]);
    $transaction = $stmt_select->fetch(PDO::FETCH_ASSOC);

    if (!$transaction) {
        throw new Exception("Không tìm thấy giao dịch.");
    }

    $book_id = $transaction['book_id'];
    $old_status = $transaction['status'];

    // Cập nhật bảng transactions
    $return_date_sql = ($data->new_status == 'RETURNED') ? "CURDATE()" : "NULL";
    $query_trans = "UPDATE transactions SET status = ?, return_date = " . $return_date_sql . " WHERE transaction_id = ?";
    $stmt_trans = $db->prepare($query_trans);
    $stmt_trans->execute([$data->new_status, $data->transaction_id]);

    // Nếu trạng thái cũ chưa trả và trạng thái mới là đã trả -> tăng số lượng sách
    if ($old_status !== 'RETURNED' && $data->new_status == 'RETURNED') {
        $query_book = "UPDATE books SET available_copies = available_copies + 1 WHERE book_id = ?";
        $stmt_book = $db->prepare($query_book);
        $stmt_book->execute([$book_id]);
    }
    // Nếu trạng thái cũ là đã trả và trạng thái mới là chưa trả -> giảm số lượng sách
    else if ($old_status === 'RETURNED' && $data->new_status !== 'RETURNED') {
        $query_book = "UPDATE books SET available_copies = available_copies - 1 WHERE book_id = ?";
        $stmt_book = $db->prepare($query_book);
        $stmt_book->execute([$book_id]);
    }

    $db->commit();
    http_response_code(200);
    echo json_encode(["message" => "Cập nhật trạng thái thành công."]);
} catch (Exception $e) {
    $db->rollBack();
    http_response_code(500);
    echo json_encode(["message" => "Lỗi: " . $e->getMessage()]);
}
