<?php
session_start();
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods: POST');
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// BƯỚC 1: KIỂM TRA ĐĂNG NHẬP
if (!isset($_SESSION['user_id'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(["message" => "Vui lòng đăng nhập để thực hiện chức năng này."]);
    exit();
}

include_once '../../config/db_connect.php';
include_once '../../models/Book.php'; // Cần model Book để cập nhật số lượng
include_once '../../models/Transaction.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

// BƯỚC 2: XÁC ĐỊNH DỮ LIỆU
if (empty($data->book_id)) {
    http_response_code(400);
    echo json_encode(["message" => "Thiếu thông tin sách cần mượn."]);
    exit();
}

// Nếu frontend gửi reader_id (trường hợp admin), dùng nó.
// Nếu không, lấy reader_id từ session (trường hợp độc giả tự mượn).
$reader_id = !empty($data->reader_id) ? $data->reader_id : $_SESSION['user_id'];
$book_id = $data->book_id;

// Bắt đầu một transaction của CSDL để đảm bảo an toàn dữ liệu
$db->beginTransaction();

try {
    // BƯỚC 3: KIỂM TRA SÁCH CÓ TỒN TẠI VÀ CÒN HÀNG KHÔNG
    $book_check_query = "SELECT available_copies FROM books WHERE book_id = :book_id FOR UPDATE";
    $stmt_book_check = $db->prepare($book_check_query);
    $stmt_book_check->bindParam(':book_id', $book_id);
    $stmt_book_check->execute();
    $book_row = $stmt_book_check->fetch(PDO::FETCH_ASSOC);

    if (!$book_row) {
        throw new Exception('Sách không tồn tại.');
    }
    if ($book_row['available_copies'] < 1) {
        throw new Exception('Sách đã được mượn hết.');
    }

    // BƯỚC 4: TẠO GIAO DỊCH MƯỢN SÁCH
    $borrow_query = "INSERT INTO transactions SET book_id = :book_id, reader_id = :reader_id, borrow_date = CURDATE(), due_date = DATE_ADD(CURDATE(), INTERVAL 14 DAY), status = 'BORROWED'";
    $stmt_borrow = $db->prepare($borrow_query);
    $stmt_borrow->bindParam(':book_id', $book_id);
    $stmt_borrow->bindParam(':reader_id', $reader_id);
    if (!$stmt_borrow->execute()) {
        throw new Exception('Không thể tạo giao dịch mượn sách.');
    }

    // BƯỚC 5: CẬP NHẬT SỐ LƯỢNG SÁCH
    $update_book_query = "UPDATE books SET available_copies = available_copies - 1 WHERE book_id = :book_id";
    $stmt_update = $db->prepare($update_book_query);
    $stmt_update->bindParam(':book_id', $book_id);
    if (!$stmt_update->execute()) {
        throw new Exception('Không thể cập nhật số lượng sách.');
    }

    // Nếu mọi thứ thành công, commit transaction
    $db->commit();

    http_response_code(201);
    echo json_encode(['message' => 'Mượn sách thành công.']);
} catch (Exception $e) {
    // Nếu có lỗi, rollback tất cả thay đổi
    $db->rollBack();
    http_response_code(400); // Bad Request
    echo json_encode(['message' => $e->getMessage()]);
}
