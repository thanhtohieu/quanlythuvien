<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
// Bạn có thể cần thêm header này để cho phép frontend gọi từ domain khác
// header("Access-Control-Allow-Origin: *"); 

include_once '../../config/db_connect.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

// BƯỚC 1: VALIDATE DỮ LIỆU ĐẦU VÀO
// Dùng trim để loại bỏ khoảng trắng thừa
$username = isset($data->username) ? trim($data->username) : '';
$password = isset($data->password) ? trim($data->password) : '';

if (empty($username) || empty($password)) {
    http_response_code(400); // Bad Request
    echo json_encode(["success" => false, "message" => "Thiếu tên đăng nhập hoặc mật khẩu."]);
    exit();
}

if (strlen($password) < 6) {
    http_response_code(400); // Bad Request
    echo json_encode(["success" => false, "message" => "Mật khẩu phải có ít nhất 6 ký tự."]);
    exit();
}

// BƯỚC 2: THỰC HIỆN TRUY VẤN VÀ XỬ LÝ LỖI
try {
    $query = "INSERT INTO users (username, password, role) VALUES (:username, :password, :role)";
    $stmt = $db->prepare($query);

    // Băm mật khẩu
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Gán vai trò mặc định nếu không được cung cấp
    $role = $data->role ?? 'reader';

    // Bind các tham số
    $stmt->bindParam(":username", $username);
    $stmt->bindParam(":password", $hashed_password);
    $stmt->bindParam(":role", $role);

    if ($stmt->execute()) {
        http_response_code(201); // Created
        echo json_encode(["success" => true, "message" => "Đăng ký thành công!"]);
    } else {
        // Lỗi không xác định nếu execute trả về false mà không ném exception
        http_response_code(500); // Internal Server Error
        echo json_encode(["success" => false, "message" => "Đã xảy ra lỗi không xác định."]);
    }
} catch (PDOException $e) {
    // BƯỚC 3: BẮT LỖI CỤ THỂ
    if ($e->getCode() == '23000') { // 23000 là mã lỗi cho vi phạm ràng buộc UNIQUE (tên đăng nhập đã tồn tại)
        http_response_code(409); // Conflict
        echo json_encode(["success" => false, "message" => "Tên đăng nhập này đã được sử dụng."]);
    } else {
        // Các lỗi CSDL khác
        http_response_code(500); // Internal Server Error
        // Ghi log lỗi để debug, không hiển thị chi tiết cho người dùng
        // error_log($e->getMessage()); 

        error_log("PDO Error: " . $e->getMessage());

        echo json_encode(["success" => false, "message" => "Lỗi máy chủ, không thể xử lý yêu cầu."]);
    }
}
