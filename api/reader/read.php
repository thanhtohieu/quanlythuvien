<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

include_once '../../config/db_connect.php';

$database = new Database();
$db = $database->getConnection();

// Câu lệnh SQL lấy tất cả thông tin độc giả
$sql = "SELECT reader_id, name, student_id, contact_info 
        FROM readers 
        ORDER BY name ASC";

$stmt = $db->prepare($sql);
$stmt->execute();

$num = $stmt->rowCount();

if ($num > 0) {
    $readers_arr = array();

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row);

        $reader_item = array(
            "reader_id" => $reader_id,
            "name" => $name,
            "student_id" => $student_id,
            "contact_info" => $contact_info
        );

        array_push($readers_arr, $reader_item);
    }

    http_response_code(200); // OK
    echo json_encode($readers_arr);
} else {
    http_response_code(404); // Not Found
    echo json_encode(array("message" => "Không tìm thấy độc giả nào trong hệ thống."));
}
