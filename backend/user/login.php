<?php
session_start();
$start_time = microtime(true); // Bắt đầu đo thời gian

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../config/db_connect.php';

$database = new Database();
$db = $database->getConnection();
$connection_time = microtime(true); // Mốc thời gian sau khi kết nối CSDL

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
    $query_time = microtime(true); // Mốc thời gian sau khi truy vấn

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    $time_to_connect = round($connection_time - $start_time, 4);
    $time_to_query = round($query_time - $connection_time, 4);
    $time_to_verify = 0; // Khởi tạo

    if ($user) {
        $verify_start = microtime(true);
        $password_match = password_verify($password, $user['password']);
        $verify_end = microtime(true); // Mốc thời gian sau khi xác thực mk
        $time_to_verify = round($verify_end - $verify_start, 4);
    } else {
        $password_match = false;
    }

    if ($password_match) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        session_write_close();

        http_response_code(200);
        echo json_encode([
            "success" => true,
            "message" => "Đăng nhập thành công!",
            "debug_times" => ["connect" => $time_to_connect, "query" => $time_to_query, "verify" => $time_to_verify]
        ]);
    } else {
        http_response_code(401);
        echo json_encode([
            "success" => false,
            "message" => "Tên đăng nhập hoặc mật khẩu không chính xác.",
            "debug_times" => ["connect" => $time_to_connect, "query" => $time_to_query, "verify" => $time_to_verify]
        ]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    error_log("Login PDO Error: " . $e->getMessage());
    echo json_encode(["success" => false, "message" => "Lỗi máy chủ, không thể xử lý yêu cầu."]);
}
