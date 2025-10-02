<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

include_once '../../config/db_connect.php';

$database = new Database();
$db = $database->getConnection();

// Câu lệnh SQL JOIN 3 bảng: transactions, books, readers
$sql = "SELECT 
            t.transaction_id, 
            t.borrow_date, 
            t.due_date, 
            t.return_date, 
            t.status, 
            b.title AS book_title, 
            r.name AS reader_name,
            r.student_id
        FROM 
            transactions t
        INNER JOIN 
            books b ON t.book_id = b.book_id
        INNER JOIN 
            readers r ON t.reader_id = r.reader_id
        ORDER BY 
            t.borrow_date DESC";

$stmt = $db->prepare($sql);
$stmt->execute();

$num = $stmt->rowCount();

if ($num > 0) {
    $transactions_arr = array();

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row);

        $transaction_item = array(
            "transaction_id" => $transaction_id,
            "borrow_date" => $borrow_date,
            "due_date" => $due_date,
            "return_date" => $return_date,
            "status" => $status,
            "book_title" => $book_title,
            "reader_name" => $reader_name,
            "student_id" => $student_id
        );

        array_push($transactions_arr, $transaction_item);
    }

    http_response_code(200); // OK
    echo json_encode($transactions_arr);
} else {
    http_response_code(404); // Not Found
    echo json_encode(array("message" => "Chưa có giao dịch mượn sách nào."));
}
