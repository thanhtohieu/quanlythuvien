// Cấu hình API backend
const API_URL = "api/users/login.php";

document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("loginForm");
  if (form) {
    form.addEventListener("submit", async (e) => {
      e.preventDefault();

      const username = document.getElementById("username").value.trim();
      const password = document.getElementById("password").value.trim();
      const errorMsg = document.getElementById("errorMsg");
      errorMsg.textContent = ""; // Xóa thông báo lỗi cũ

      if (!username || !password) {
        errorMsg.textContent = "Vui lòng nhập đầy đủ thông tin.";
        return;
      }

      try {
        const response = await fetch(API_URL, {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ username, password }),
        });

        const result = await response.json();

        if (result.success) {
          // Lưu thông tin user vào localStorage để sử dụng sau này
          localStorage.setItem("user", JSON.stringify(result.user));
          // Chuyển hướng đến trang quản lý
          window.location.href = "dashboard.html";
        } else {
          errorMsg.textContent = result.message || "Đăng nhập thất bại!";
        }
      } catch (error) {
        errorMsg.textContent = "Không thể kết nối đến máy chủ!";
        console.error("Login error:", error);
      }
    });
  }
});