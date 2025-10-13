// Xử lý Đăng nhập
const loginForm = document.getElementById("loginForm");
if (loginForm) {
  loginForm.addEventListener("submit", async (e) => {
    e.preventDefault();

    const username = document.getElementById("username").value;
    const password = document.getElementById("password").value;

    const res = await fetch("../api/reader/login.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ username, password }),
    });

    const data = await res.json();
    const msg = document.getElementById("message");

    if (data.success) {
      msg.textContent = "Đăng nhập thành công!";
      msg.style.color = "green";
      localStorage.setItem("user", JSON.stringify(data.user));
      setTimeout(() => (window.location.href = "index.html"), 1000);
    } else {
      msg.textContent = data.message || "Sai tài khoản hoặc mật khẩu!";
      msg.style.color = "red";
    }
  });
}

// Xử lý Đăng ký
const registerForm = document.getElementById("registerForm");
if (registerForm) {
  registerForm.addEventListener("submit", async (e) => {
    e.preventDefault();

    const username = document.getElementById("username").value;
    const password = document.getElementById("password").value;

    const res = await fetch("../api/reader/register.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ username, password }),
    });

    const data = await res.json();
    const msg = document.getElementById("message");

    if (data.success) {
      msg.textContent = "Đăng ký thành công! Hãy đăng nhập.";
      msg.style.color = "green";
    } else {
      msg.textContent = data.message;
      msg.style.color = "red";
    }
  });
}
