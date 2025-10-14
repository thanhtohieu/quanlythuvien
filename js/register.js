// js/register.js

document.addEventListener("DOMContentLoaded", () => {
    const registerForm = document.getElementById("registerForm");
    const errorMsg = document.getElementById("errorMsg");

    if (registerForm) {
        registerForm.addEventListener("submit", async (e) => {
            // Ngăn trang tải lại khi nhấn nút
            e.preventDefault();

            // Lấy dữ liệu từ form và xóa khoảng trắng thừa
            const username = document.getElementById("username").value.trim();
            const password = document.getElementById("password").value.trim();
            const confirmPassword = document.getElementById("confirmPassword").value.trim();

            // Xóa thông báo lỗi cũ
            errorMsg.textContent = "";
            errorMsg.style.color = 'red'; // Đảm bảo màu chữ là màu đỏ cho lỗi

            // --- BƯỚC 1: KIỂM TRA DỮ LIỆU PHÍA CLIENT ---
            if (!username || !password || !confirmPassword) {
                errorMsg.textContent = "Vui lòng điền đầy đủ tất cả các trường.";
                return;
            }

            if (password.length < 6) {
                errorMsg.textContent = "Mật khẩu phải có ít nhất 6 ký tự.";
                return;
            }

            if (password !== confirmPassword) {
                errorMsg.textContent = "Mật khẩu xác nhận không khớp.";
                return;
            }

            // --- BƯỚC 2: GỬI YÊU CẦU ĐẾN SERVER ---
            try {
                // ĐỊNH NGHĨA URL ĐẾN API BACKEND. HÃY ĐẢM BẢO ĐƯỜNG DẪN NÀY CHÍNH XÁC!
                const apiUrl = "http://localhost/quanlythuvien/backend/user/register.php";

                const response = await fetch(apiUrl, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                    },
                    body: JSON.stringify({
                        username: username,
                        password: password,
                    }),
                });

                // Lấy dữ liệu JSON từ phản hồi của server
                const result = await response.json();

                // --- BƯỚC 3: XỬ LÝ PHẢN HỒI TỪ SERVER ---
                // response.ok sẽ là true nếu mã trạng thái HTTP là 2xx (ví dụ: 200 OK, 201 Created)
                if (response.ok) {
                    // Đăng ký thành công
                    alert(result.message); // Hiển thị thông báo thành công (ví dụ: "Đăng ký thành công!")
                    window.location.href = "login.html"; // Chuyển đến trang đăng nhập
                } else {
                    // Nếu server trả về lỗi (ví dụ: 400, 409, 500)
                    // `result.message` sẽ chứa thông báo lỗi cụ thể từ PHP
                    // (ví dụ: "Tên đăng nhập đã tồn tại", "Thiếu dữ liệu",...)
                    errorMsg.textContent = result.message || "Đã xảy ra lỗi không xác định từ server.";
                }

            } catch (error) {
                // Lỗi này chỉ xảy ra khi có sự cố về MẠNG
                // Ví dụ: server XAMPP bị tắt, sai URL, không có kết nối,...
                console.error("Fetch Error:", error);
                errorMsg.textContent = "Không thể kết nối đến máy chủ. Vui lòng kiểm tra lại server và đường dẫn API.";
            }
        });
    }
});