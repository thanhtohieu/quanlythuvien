<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

include_once '../../config/db_connect.php';

$database = new Database();
$db = $database->getConnection();

// Câu lệnh SQL lấy tất cả thông tin sách
$sql = "SELECT book_id, title, author, publisher, publication_year, quantity, available_copies 
        FROM books 
        ORDER BY title ASC";

$stmt = $db->prepare($sql);
$stmt->execute();

$num = $stmt->rowCount();

if ($num > 0) {
    $books_arr = array();

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row);

        $book_item = array(
            "book_id" => $book_id,
            "title" => $title,
            "author" => $author,
            "publisher" => $publisher,
            "publication_year" => $publication_year,
            "quantity" => $quantity,
            "available_copies" => $available_copies
        );

        array_push($books_arr, $book_item);
    }

    http_response_code(200); // OK
    echo json_encode($books_arr);
} else {
    http_response_code(404); // Not Found
    echo json_encode(array("message" => "Không tìm thấy sách nào trong thư viện."));
}
