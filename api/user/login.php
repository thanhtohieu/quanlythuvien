<?php
// Bắt đầu session để có thể lưu trạng thái đăng nhập
session_start();

// Các header cần thiết cho API
header("Access-Control-Allow-Origin: *"); // Cho phép frontend từ domain khác gọi
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../config/db_connect.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

// Validate dữ liệu đầu vào
$username = isset($data->username) ? trim($data->username) : '';
$password = isset($data->password) ? trim($data->password) : '';

if (empty($username) || empty($password)) {
    http_response_code(400); // Bad Request
    echo json_encode(["success" => false, "message" => "Thiếu tên đăng nhập hoặc mật khẩu."]);
    exit();
}

try {
    // Tìm người dùng trong CSDL dựa trên username
    $query = "SELECT user_id, username, password, role FROM users WHERE username = :username LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->execute();

    // Lấy thông tin người dùng
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // BƯỚC QUAN TRỌNG NHẤT: KIỂM TRA NGƯỜI DÙNG VÀ MẬT KHẨU
    // 1. Kiểm tra xem có tìm thấy người dùng không
    // 2. Nếu có, so sánh mật khẩu người dùng nhập với mật khẩu đã băm trong CSDL
    if ($user && password_verify($password, $user['password'])) {
        // Mật khẩu chính xác, đăng nhập thành công

        // Lưu thông tin cần thiết vào session
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        http_response_code(200); // OK
        echo json_encode([
            "success" => true,
            "message" => "Đăng nhập thành công!",
            "user" => [
                "username" => $user['username'],
                "role" => $user['role']
            ]
        ]);
    } else {
        // Không tìm thấy người dùng hoặc sai mật khẩu
        http_response_code(401); // Unauthorized
        echo json_encode(["success" => false, "message" => "Tên đăng nhập hoặc mật khẩu không chính xác."]);
    }
} catch (PDOException $e) {
    // Xử lý các lỗi CSDL khác
    http_response_code(500); // Internal Server Error
    error_log("Login PDO Error: " . $e->getMessage());
    echo json_encode(["success" => false, "message" => "Lỗi máy chủ, không thể xử lý yêu cầu."]);
}
