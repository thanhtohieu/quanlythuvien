<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

include_once '../../config/db_connect.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->username) && !empty($data->password)) {
    $query = "SELECT * FROM users WHERE username = :username LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":username", $data->username);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (password_verify($data->password, $user['password'])) {
            echo json_encode(["success" => true, "user" => [
                "user_id" => $user['user_id'],
                "username" => $user['username'],
                "role" => $user['role']
            ]]);
        } else {
            echo json_encode(["success" => false, "message" => "Sai mật khẩu."]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Không tìm thấy tài khoản."]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Thiếu thông tin đăng nhập."]);
}
?>
