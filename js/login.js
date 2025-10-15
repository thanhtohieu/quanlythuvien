document.addEventListener("DOMContentLoaded", () => {
  const loginForm = document.getElementById("loginForm");
  const errorMsg = document.getElementById("errorMsg");

  if (loginForm) {
    loginForm.addEventListener("submit", async (e) => {
      e.preventDefault();

      const username = document.getElementById("username").value.trim();
      const password = document.getElementById("password").value.trim();

      errorMsg.textContent = "";

      if (!username || !password) {
        errorMsg.textContent = "Vui lòng điền đầy đủ tên đăng nhập và mật khẩu.";
        return;
      }

      try {
        // Đảm bảo đường dẫn này chính xác (đã đổi api -> backend)
        const apiUrl = "/quanlythuvien/backend/user/login.php";

        const response = await fetch(apiUrl, {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ username, password }),
        });

        const result = await response.json();

        if (response.ok) {
          alert(result.message);

          // --- THAY ĐỔI Ở ĐÂY ---
          // Kiểm tra vai trò của người dùng trả về từ API
          if (result.user && result.user.role === 'admin') {
            // Nếu là admin, chuyển đến trang dashboard đầy đủ chức năng
            window.location.href = "dashboard.html";
          } else {
            // Nếu là độc giả, chuyển đến trang dashboard của người dùng
            window.location.href = "user_dashboard.html";
          }
        } else {
          errorMsg.textContent = result.message || "Đã xảy ra lỗi. Vui lòng thử lại.";
        }

      } catch (error) {
        console.error("Login error:", error);
        errorMsg.textContent = "Lỗi kết nối hoặc URL API không đúng.";
      }
    });
  }
});