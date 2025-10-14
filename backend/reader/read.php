<?php
// backend/reader/read.php

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');

include_once '../../config/db_connect.php';
// Giả sử bạn có một file Reader.php trong models
include_once '../../models/Reader.php';

$database = new Database();
$db = $database->getConnection();

$reader = new Reader($db);

$result = $reader->read();
$num = $result->rowCount();

if ($num > 0) {
    $readers_arr = array();
    $readers_arr['data'] = array();

    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        $reader_item = array(
            'reader_id' => $reader_id,
            'name' => $name, // Dựa theo cột trong CSDL của bạn
            'student_id' => $student_id,
            'contact_info' => $contact_info
        );
        array_push($readers_arr['data'], $reader_item);
    }
    http_response_code(200);
    echo json_encode($readers_arr);
} else {
    http_response_code(200);
    echo json_encode(['data' => [], 'message' => 'No Readers Found']);
}
