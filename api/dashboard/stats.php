<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');

include_once '../../config/db_connect.php'; // Đảm bảo đường dẫn chính xác

$database = new Database();
$db = $database->getConnection();

try {
    // 1. Lấy tổng số đầu sách (unique book titles)
    $query_total_books = "SELECT COUNT(book_id) FROM books";
    $stmt_total_books = $db->prepare($query_total_books);
    $stmt_total_books->execute();
    $total_books = $stmt_total_books->fetchColumn();

    // 2. Lấy tổng số độc giả
    $query_total_readers = "SELECT COUNT(reader_id) FROM readers";
    $stmt_total_readers = $db->prepare($query_total_readers);
    $stmt_total_readers->execute();
    $total_readers = $stmt_total_readers->fetchColumn();

    // 3. Lấy số sách đang được mượn
    $query_on_loan = "SELECT COUNT(transaction_id) FROM transactions WHERE status = 'BORROWED'";
    $stmt_on_loan = $db->prepare($query_on_loan);
    $stmt_on_loan->execute();
    $books_on_loan = $stmt_on_loan->fetchColumn();

    // 4. Lấy số sách bị quá hạn
    $query_overdue = "SELECT COUNT(transaction_id) FROM transactions WHERE status = 'BORROWED' AND due_date < CURDATE()";
    $stmt_overdue = $db->prepare($query_overdue);
    $stmt_overdue->execute();
    $overdue_count = $stmt_overdue->fetchColumn();

    // 5. Tổng hợp thành một mảng
    $stats_arr = array(
        "total_books" => (int)$total_books,
        "total_readers" => (int)$total_readers,
        "books_on_loan" => (int)$books_on_loan,
        "overdue_count" => (int)$overdue_count
    );

    http_response_code(200);
    echo json_encode($stats_arr);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array("message" => "Lỗi khi lấy dữ liệu thống kê: " . $e->getMessage()));
}
