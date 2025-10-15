<?php
// Mật khẩu bạn muốn mã hóa
$passwordToHash = 'admin1234';

// Sử dụng thuật toán mã hóa mặc định và an toàn của PHP
$hashedPassword = password_hash($passwordToHash, PASSWORD_DEFAULT);

// In chuỗi mã hóa ra màn hình
echo "Mật khẩu của bạn là: " . $passwordToHash . "<br>";
echo "Chuỗi mã hóa tương ứng (hãy copy chuỗi này):<br>";
echo "<pre>" . htmlspecialchars($hashedPassword) . "</pre>";
?>
```

#### **Bước 2: Chạy file và lấy chuỗi mã hóa**
1. Lưu file `create_hash.php` lại.
2. Mở trình duyệt và truy cập vào địa chỉ:
```
http://localhost/quanlythuvien/create_hash.php
```
*(Nếu bạn đã đổi cổng Apache, hãy dùng `http://localhost:8080/...`)*
3. Bạn sẽ thấy một chuỗi dài bắt đầu bằng `$2y$`. **Hãy copy toàn bộ chuỗi đó.**

#### **Bước 3: Cập nhật lại mật khẩu trong CSDL**
1. Mở **phpMyAdmin**, chọn CSDL `library`, rồi chọn bảng `users`.
2. Nhấn vào tab **"SQL"**.
3. Dán lệnh `UPDATE` sau vào ô lệnh. **Hãy thay thế `CHUỖI_MÃ_HÓA_BẠN_VỪA_COPY` bằng chuỗi bạn đã copy ở Bước 2.**

```sql
UPDATE `users` SET `password` = 'CHUỖI_MÃ_HÓA_BẠN_VỪA_COPY' WHERE `username` = 'admin';