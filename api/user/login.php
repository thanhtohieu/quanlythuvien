<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Origin: *"); // Cho phép truy cập từ mọi nguồn

include_once '../../config/db_connect.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

if (empty($data->username) || empty($data->password)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Thiếu tên đăng nhập hoặc mật khẩu."]);
    exit();
}

$username = $data->username;
$password = $data->password;

$query = "SELECT user_id, username, password, role FROM users WHERE username = :username LIMIT 1";
$stmt = $db->prepare($query);
$stmt->bindParam(":username", $username);
$stmt->execute();

if ($stmt->rowCount() > 0) {
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $hashed_password = $user['password'];

    // Xác thực mật khẩu
    if (password_verify($password, $hashed_password)) {
        // Không gửi lại password hash
        unset($user['password']);

        http_response_code(200);
        echo json_encode([
            "success" => true,
            "message" => "Đăng nhập thành công!",
            "user" => $user
        ]);
    } else {
        http_response_code(401);
        echo json_encode(["success" => false, "message" => "Sai mật khẩu."]);
    }
} else {
    http_response_code(404);
    echo json_encode(["success" => false, "message" => "Không tìm thấy tài khoản."]);
}
?>