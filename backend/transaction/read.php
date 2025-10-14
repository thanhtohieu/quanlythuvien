<?php
// backend/transaction/read.php

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');

include_once '../../config/db_connect.php';
include_once '../../models/Transaction.php';

$database = new Database();
$db = $database->getConnection();

$transaction = new Transaction($db);

$result = $transaction->read();
$num = $result->rowCount();

if ($num > 0) {
    $transactions_arr = array();
    $transactions_arr['data'] = array();

    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        $transaction_item = array(
            'transaction_id' => $transaction_id,
            'book_title' => $book_title,
            'reader_name' => $reader_name,
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
