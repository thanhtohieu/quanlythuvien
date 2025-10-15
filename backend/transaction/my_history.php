<?php
session_start();
header('Content-Type: application/json; charset=UTF-8');

// KIỂM TRA XEM NGƯỜI DÙNG ĐÃ ĐĂNG NHẬP CHƯA
if (!isset($_SESSION['user_id'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(["message" => "Vui lòng đăng nhập để xem lịch sử."]);
    exit();
}

include_once '../../config/db_connect.php';
include_once '../../models/Transaction.php'; // Sử dụng lại model Transaction

$database = new Database();
$db = $database->getConnection();
$transaction = new Transaction($db);

// Lấy user_id từ session để biết ai đang yêu cầu
$reader_id = $_SESSION['user_id'];

// Tạo một hàm mới trong model hoặc viết truy vấn trực tiếp ở đây
$query = 'SELECT
            t.transaction_id,
            b.title as book_title,
            t.borrow_date,
            t.due_date,
            t.return_date,
            t.status
        FROM
            transactions t
        LEFT JOIN
            books b ON t.book_id = b.book_id
        WHERE
            t.reader_id = ? -- Chỉ lấy các giao dịch của người dùng này
        ORDER BY
            t.borrow_date DESC';

$stmt = $db->prepare($query);
$stmt->execute([$reader_id]);
$num = $stmt->rowCount();

if ($num > 0) {
    $transactions_arr = array();
    $transactions_arr['data'] = array();

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        $transaction_item = array(
            'book_title' => $book_title,
            'borrow_date' => $borrow_date,
            'due_date' => $due_date,
            'return_date' => $return_date,
            'status' => $status
        );
        array_push($transactions_arr['data'], $transaction_item);
    }
    http_response_code(200);
    echo json_encode($transactions_arr);
} else {
    http_response_code(200);
    echo json_encode(['data' => [], 'message' => 'No Transactions Found']);
}
