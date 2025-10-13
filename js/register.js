// js/register.js

// Chờ cho toàn bộ trang web được tải xong trước khi chạy mã
document.addEventListener("DOMContentLoaded", () => {
    // Lấy các phần tử cần thiết từ trang HTML
    const registerForm = document.getElementById("registerForm");
    const errorMsg = document.getElementById("errorMsg");

    // Chỉ thực thi mã nếu tìm thấy form đăng ký
    if (registerForm) {
        // Gắn một trình xử lý sự kiện vào form khi nó được gửi đi
        registerForm.addEventListener("submit", async (e) => {
            // Ngăn chặn hành vi mặc định của form (tải lại trang)
            e.preventDefault();

            // Lấy giá trị từ các ô input và loại bỏ khoảng trắng thừa
            const username = document.getElementById("username").value.trim();
            const password = document.getElementById("password").value.trim();
            const confirmPassword = document.getElementById("confirmPassword").value.trim();

            // Xóa mọi thông báo lỗi cũ
            errorMsg.textContent = "";

            // --- BƯỚC 1: KIỂM TRA DỮ LIỆU PHÍA NGƯỜI DÙNG ---
            if (!username || !password || !confirmPassword) {
                errorMsg.textContent = "Vui lòng điền đầy đủ tất cả các trường.";
                return; // Dừng thực thi hàm
            }

            if (password.length < 6) {
                errorMsg.textContent = "Mật khẩu phải có ít nhất 6 ký tự.";
                return; 
            }

            if (password !== confirmPassword) {
                errorMsg.textContent = "Mật khẩu xác nhận không khớp.";
                return;
            }

            // --- BƯỚC 2: GỬI DỮ LIỆU ĐẾN API ---
            try {
                // Sử dụng fetch để gửi yêu cầu POST đến API đăng ký
                const response = await fetch("api/users/register.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json", // Báo cho server biết chúng ta đang gửi dữ liệu dạng JSON
                    },
                    // Chuyển đổi đối tượng JavaScript thành chuỗi JSON
                    body: JSON.stringify({
                        username: username,
                        password: password,
                        role: 'reader' // Mặc định vai trò là 'reader' cho người dùng mới
                    }),
                });

                // Chuyển đổi phản hồi từ server (dạng JSON) thành đối tượng JavaScript
                const result = await response.json();

                // --- BƯỚC 3: XỬ LÝ KẾT QUẢ TRẢ VỀ ---
                if (result.success) {
                    // Nếu đăng ký thành công
                    alert(result.message); // Hiển thị thông báo "Đăng ký thành công!"
                    window.location.href = "login.html"; // Chuyển hướng người dùng đến trang đăng nhập
                } else {
                    // Nếu có lỗi từ server (ví dụ: tên đăng nhập đã tồn tại)
                    errorMsg.textContent = result.message || "Đã xảy ra lỗi. Vui lòng thử lại.";
                }

            } catch (error) {
                // Xử lý lỗi mạng (ví dụ: không kết nối được tới server)
                errorMsg.textContent = "Không thể kết nối đến máy chủ. Vui lòng kiểm tra lại mạng của bạn!";
                console.error("Lỗi đăng ký:", error);
            }
        });
    }
});