// js/login.js

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
        // ĐƯỜNG DẪN ĐÚNG LÀ 'user' KHÔNG CÓ 's'
        const apiUrl = "http://localhost/quanlythuvien/api/user/login.php";

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

        const result = await response.json();

        if (response.ok) {
          alert(result.message);
          // Chuyển hướng đến trang dashboard sau khi đăng nhập thành công
          window.location.href = "dashboard.html";
        } else {
          errorMsg.textContent = result.message || "Đã xảy ra lỗi. Vui lòng thử lại.";
        }

      } catch (error) {
        console.error("Login error:", error);
        errorMsg.textContent = "Không thể kết nối đến máy chủ. Vui lòng kiểm tra lại URL API.";
      }
    });
  }
});