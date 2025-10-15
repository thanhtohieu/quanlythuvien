<?php
// backend/reader/read_single.php
header('Content-Type: application/json; charset=UTF-8');

include_once '../../config/db_connect.php';
include_once '../../models/Reader.php';

$database = new Database();
$db = $database->getConnection();
$reader = new Reader($db);

$reader->reader_id = isset($_GET['id']) ? $_GET['id'] : die();

if ($reader->read_single()) {
    $reader_arr = array(
        "reader_id" => $reader->reader_id,
        "name" => $reader->name,
        "student_id" => $reader->student_id,
        "contact_info" => $reader->contact_info
    );
    http_response_code(200);
    echo json_encode($reader_arr);
} else {
    http_response_code(404);
    echo json_encode(["message" => "Không tìm thấy độc giả."]);
}
