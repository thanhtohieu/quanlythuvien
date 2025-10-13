<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

include_once '../../config/db_connect.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->username) && !empty($data->password)) {
    $query = "INSERT INTO users (username, password, role) VALUES (:username, :password, :role)";
    $stmt = $db->prepare($query);

    $hashed_password = password_hash($data->password, PASSWORD_DEFAULT);
    $role = $data->role ?? 'reader';

    $stmt->bindParam(":username", $data->username);
    $stmt->bindParam(":password", $hashed_password);
    $stmt->bindParam(":role", $role);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Đăng ký thành công!"]);
    } else {
        echo json_encode(["success" => false, "message" => "Tên đăng nhập đã tồn tại hoặc lỗi máy chủ."]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Thiếu tên đăng nhập hoặc mật khẩu."]);
}
?>
