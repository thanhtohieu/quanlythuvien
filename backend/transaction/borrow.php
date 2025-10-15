<?php
session_start();
header('Content-Type: application/json');

// KIỂM TRA QUYỀN ADMIN
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(["message" => "Truy cập bị từ chối."]);
    exit();
}

include_once '../../config/db_connect.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

if (empty($data->book_id) || empty($data->reader_id)) {
    http_response_code(400);
    echo json_encode(["message" => "Vui lòng chọn sách và độc giả."]);
    exit();
}

// Bắt đầu một transaction của CSDL để đảm bảo an toàn dữ liệu
$db->beginTransaction();

try {
    // 1. Kiểm tra xem sách có còn để cho mượn không
    $stmt_check = $db->prepare("SELECT available_copies FROM books WHERE book_id = :book_id FOR UPDATE");
    $stmt_check->bindParam(':book_id', $data->book_id);
    $stmt_check->execute();
    $book = $stmt_check->fetch(PDO::FETCH_ASSOC);

    if (!$book || $book['available_copies'] <= 0) {
        throw new Exception("Sách đã được mượn hết hoặc không tồn tại.");
    }

    // 2. Tạo giao dịch mượn sách mới
    $stmt_insert = $db->prepare(
        "INSERT INTO transactions (book_id, reader_id, borrow_date, due_date, status) 
         VALUES (:book_id, :reader_id, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 14 DAY), 'BORROWED')"
    );
    $stmt_insert->bindParam(':book_id', $data->book_id);
    $stmt_insert->bindParam(':reader_id', $data->reader_id);
    $stmt_insert->execute();

    // 3. Cập nhật số lượng sách còn lại
    $stmt_update = $db->prepare("UPDATE books SET available_copies = available_copies - 1 WHERE book_id = :book_id");
    $stmt_update->bindParam(':book_id', $data->book_id);
    $stmt_update->execute();

    // Nếu mọi thứ thành công, commit transaction
    $db->commit();

    http_response_code(201);
    echo json_encode(['message' => 'Cho mượn sách thành công!']);
} catch (Exception $e) {
    // Nếu có lỗi, rollback tất cả thay đổi
    $db->rollBack();
    http_response_code(500);
    echo json_encode(['message' => 'Lỗi server: ' . $e->getMessage()]);
}
