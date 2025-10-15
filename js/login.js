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
        errorMsg.textContent = "Vui lòng điền đầy đủ thông tin.";
        return;
      }

      try {
        const apiUrl = "/quanlythuvien/backend/user/login.php";

        const response = await fetch(apiUrl, {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ username, password }),
          credentials: 'include' // Quan trọng để quản lý session
        });

        const result = await response.json();

        if (response.ok) {
          // --- BƯỚC QUAN TRỌNG NHẤT NẰM Ở ĐÂY ---
          // Đảm bảo rằng login.php trả về một đối tượng "user"
          if (result.user) {
            // Lưu thông tin người dùng vào localStorage
            localStorage.setItem('user', JSON.stringify(result.user));

            // Chuyển hướng dựa trên vai trò
            if (result.user.role === 'admin') {
              window.location.href = "dashboard.html";
            } else {
              window.location.href = "user_dashboard.html";
            }
          } else {
            errorMsg.textContent = "Lỗi: Dữ liệu người dùng không được trả về từ server.";
          }
        } else {
          errorMsg.textContent = result.message || "Đăng nhập thất bại!";
        }

      } catch (error) {
        console.error("Lỗi đăng nhập:", error);
        errorMsg.textContent = "Không thể kết nối đến máy chủ!";
      }
    });
  }
});
