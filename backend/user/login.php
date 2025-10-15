<?php
session_start();

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../config/db_connect.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

$username = isset($data->username) ? trim($data->username) : '';
$password = isset($data->password) ? trim($data->password) : '';

if (empty($username) || empty($password)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Thiếu tên đăng nhập hoặc mật khẩu."]);
    exit();
}

try {
    $query = "SELECT user_id, username, password, role FROM users WHERE username = :username LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        // Mật khẩu chính xác, đăng nhập thành công
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        session_write_close();

        http_response_code(200);
        // --- DÒNG QUAN TRỌNG NHẤT LÀ ĐÂY ---
        // Đảm bảo trả về đối tượng "user" chứa username và role
        echo json_encode([
            "success" => true,
            "message" => "Đăng nhập thành công!",
            "user" => [
                "username" => $user['username'],
                "role" => $user['role']
            ]
        ]);
        // ------------------------------------

    } else {
        // Sai thông tin đăng nhập
        http_response_code(401);
        echo json_encode([
            "success" => false,
            "message" => "Tên đăng nhập hoặc mật khẩu không chính xác."
        ]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    error_log("Login PDO Error: " . $e->getMessage());
    echo json_encode(["success" => false, "message" => "Lỗi máy chủ, không thể xử lý yêu cầu."]);
}
