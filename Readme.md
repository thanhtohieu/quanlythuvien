# 📘 Hệ thống Quản lý Thư viện

Một dự án web **quản lý thư viện đơn giản**, được xây dựng bằng **PHP thuần** ở phía backend và **HTML, CSS, JavaScript (Vanilla JS)** ở phía frontend.  
Dự án áp dụng mô hình **API RESTful** để giao tiếp giữa frontend và backend.

---

## ✨ Các chức năng chính

Dự án phân chia rõ ràng hai luồng chức năng cho **Quản trị viên (Admin)** và **Độc giả (Reader)**.

---

### 👨‍💼 Chức năng Quản trị viên

| **Chức năng** | **Mô tả chi tiết** |
|----------------|--------------------|
| **Quản lý Sách** | Thêm, sửa, xóa thông tin sách trong thư viện. Cập nhật số lượng. |
| **Quản lý Độc giả** | Thêm, sửa, xóa tài khoản người dùng/độc giả. |
| **Quản lý Mượn/Trả** | Tạo phiếu mượn mới và cập nhật trạng thái giao dịch *(Đang mượn, Quá hạn, Đã trả)*. |
| **Tìm kiếm** | Tìm kiếm sách nhanh chóng theo tên hoặc tác giả. |

---

### 📖 Chức năng Độc giả

| **Chức năng** | **Mô tả chi tiết** |
|----------------|--------------------|
| **Xác thực** | Đăng ký, đăng nhập, đăng xuất tài khoản cá nhân. |
| **Xem & Tìm kiếm** | Xem danh mục sách và tìm kiếm theo tên hoặc tác giả. |
| **Mượn sách** | Gửi yêu cầu mượn một quyển sách còn trong kho. |
| **Lịch sử cá nhân** | Xem lại lịch sử các sách đã và đang mượn. |

---

## 💻 Công nghệ sử dụng

- **Backend:** PHP 8+ *(không sử dụng framework)*  
- **Frontend:** HTML5, CSS3, JavaScript *(ES6+, Vanilla JS)*  
- **Cơ sở dữ liệu:** MySQL / MariaDB  
- **Web Server:** Apache *(thông qua XAMPP)*

---

## 📁 Cấu trúc thư mục

Dự án được tổ chức theo cấu trúc rõ ràng, tách biệt logic:

/quanlythuvien
├── backend/ # Chứa các file API endpoints
│ ├── book/
│ ├── reader/
│ ├── transaction/
│ └── user/
├── config/ # Chứa file kết nối CSDL
├── models/ # Chứa các class xử lý logic CSDL
├── js/ # Chứa các file JavaScript cho frontend
├── create_database.sql # File kịch bản khởi tạo CSDL
├── dashboard.html # Giao diện trang admin
├── user_dashboard.html # Giao diện trang độc giả
└── ...

yaml
Copy code

---

## 🚀 Hướng dẫn Cài đặt & Chạy dự án

Để chạy dự án này trên máy cục bộ, bạn cần có **XAMPP** được cài đặt.

### 1️⃣ Sao chép Project

Clone repository này về máy:

```bash
git clone https://github.com/thanhtohieu/quanlythuvien
Hoặc tải về và giải nén vào thư mục:

makefile
Copy code
C:\xampp\htdocs\quanlythuvien
2️⃣ Khởi tạo Cơ sở dữ liệu
Khởi động Apache và MySQL trong XAMPP Control Panel.

Truy cập: http://localhost/phpmyadmin

Tạo một cơ sở dữ liệu mới với tên là library.

Chọn CSDL library, vào tab Import (Nhập) và tải lên file create_database.sql.

3️⃣ Cấu hình kết nối
Mở file config/db_connect.php và đảm bảo các thông tin sau là chính xác:

php
Copy code
private $host = "127.0.0.1";
private $db_name = "library";
private $username = "root";
private $password = "";
4️⃣ Truy cập trang web
Mở trình duyệt và truy cập vào địa chỉ:

👉 http://localhost/quanlythuvien/login.html

Nếu bạn đã đổi cổng Apache, hãy dùng địa chỉ:
http://localhost:[PORT]/quanlythuvien/

🔑 Tài khoản Admin mặc định
Tên đăng nhập	Mật khẩu
admin	admin1234
