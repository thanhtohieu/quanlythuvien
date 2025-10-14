document.addEventListener('DOMContentLoaded', () => {
    // Lấy các phần tử HTML cần thiết
    const mainContent = document.getElementById('main-content');
    const navLinks = document.querySelectorAll('.nav-link');
    const logoutBtn = document.getElementById('logoutBtn');

    // Cấu hình đường dẫn API
    const API_BASE_URL = '/quanlythuvien/backend';

    // ---- CÁC HÀM ĐỂ TẢI VÀ HIỂN THỊ DỮ LIỆU ----

    // 1. Hàm tải danh sách SÁCH
    async function loadBooks() {
        try {
            const response = await fetch(`${API_BASE_URL}/read.php`);
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            const result = await response.json();

            // Thêm nút "Thêm sách mới"
            let tableHtml = `<h2>Danh sách sách</h2>
                         <button id="add-book-btn" style="margin-bottom: 10px;">+ Thêm sách mới</button>
                         <table>
                             <thead>
                                 <tr>
                                     <th>Mã sách</th>
                                     <th>Tên sách</th>
                                     <th>Tác giả</th>
                                     <th>Tổng số</th>
                                     <th>Còn lại</th>
                                     <th>Hành động</th> </tr>
                             </thead>
                             <tbody>`;

            if (result.data && Array.isArray(result.data)) {
                result.data.forEach(book => {
                    tableHtml += `<tr>
                                  <td>${book.book_id}</td>
                                  <td>${book.book_title}</td>
                                  <td>${book.author}</td>
                                  <td>${book.quantity}</td>
                                  <td>${book.available_quantity}</td>
                                  <td>
                                      <button class="edit-btn" data-id="${book.book_id}">Sửa</button>
                                      <button class="delete-btn" data-id="${book.book_id}">Xóa</button>
                                  </td>
                              </tr>`;
                });
            } else {
                tableHtml += `<tr><td colspan="6">Không có sách nào trong thư viện.</td></tr>`;
            }

            tableHtml += `</tbody></table>`;
            mainContent.innerHTML = tableHtml;

        } catch (error) {
            // ... (phần catch giữ nguyên)
        }
    }
    // 2. Hàm tải danh sách ĐỘC GIẢ
    async function loadReaders() {
        mainContent.innerHTML = '<h2>Danh sách độc giả (Chức năng đang được xây dựng)</h2>';
        // TODO: Viết code tương tự hàm loadBooks(), gọi đến API '/reader/read.php'
        try {
            // Gọi đúng API để lấy danh sách độc giả
            const response = await fetch(`${API_BASE_URL}/reader/read.php`);
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            const result = await response.json();

            // Bắt đầu tạo chuỗi HTML cho bảng
            let tableHtml = `<h2>Danh sách độc giả</h2>
                         <table>
                             <thead>
                                 <tr>
                                     <th>Mã độc giả</th>
                                     <th>Họ và tên</th>
                                     <th>Mã số sinh viên</th>
                                     <th>Thông tin liên hệ</th>
                                 </tr>
                             </thead>
                             <tbody>`;

            // Lặp qua dữ liệu độc giả và tạo các hàng của bảng
            if (result.data && result.data.length > 0) {
                result.data.forEach(reader => {
                    tableHtml += `<tr>
                                  <td>${reader.reader_id}</td>
                                  <td>${reader.name}</td>
                                  <td>${reader.student_id}</td>
                                  <td>${reader.contact_info}</td>
                              </tr>`;
                });
            } else {
                // Hiển thị thông báo nếu không có độc giả nào
                tableHtml += `<tr><td colspan="4">Chưa có độc giả nào trong hệ thống.</td></tr>`;
            }

            tableHtml += `</tbody></table>`;
            // Chèn HTML đã tạo vào trang web
            mainContent.innerHTML = tableHtml;

        } catch (error) {
            // Hiển thị lỗi nếu không tải được dữ liệu
            mainContent.innerHTML = '<p style="color: red;">Lỗi khi tải danh sách độc giả.</p>';
            console.error('Lỗi fetch độc giả:', error);
        }
    }

    // 3. Hàm tải danh sách GIAO DỊCH MƯỢN/TRẢ
    async function loadTransactions() {
        mainContent.innerHTML = '<h2>Quản lý mượn/trả (Chức năng đang được xây dựng)</h2>';
        // TODO: Viết code tương tự hàm loadBooks(), gọi đến API '/transaction/read.php'
        try {
            const response = await fetch(`${API_BASE_URL}/transaction/read.php`);
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            const result = await response.json();

            let tableHtml = `<h2>Lịch sử Mượn/Trả sách</h2>
                         <table>
                             <thead>
                                 <tr>
                                     <th>ID</th>
                                     <th>Tên sách</th>
                                     <th>Tên độc giả</th>
                                     <th>Ngày mượn</th>
                                     <th>Hạn trả</th>
                                     <th>Ngày trả</th>
                                     <th>Trạng thái</th>
                                 </tr>
                             </thead>
                             <tbody>`;

            if (result.data && result.data.length > 0) {
                result.data.forEach(item => {
                    // Thêm class để tạo màu cho từng trạng thái
                    let statusClass = item.status.toLowerCase();
                    // Nếu ngày trả là null thì hiển thị 'Chưa trả'
                    let returnDate = item.return_date ? item.return_date : 'Chưa trả';

                    tableHtml += `<tr>
                                  <td>${item.transaction_id}</td>
                                  <td>${item.book_title}</td>
                                  <td>${item.reader_name}</td>
                                  <td>${item.borrow_date}</td>
                                  <td>${item.due_date}</td>
                                  <td>${returnDate}</td>
                                  <td class="status-${statusClass}">${item.status}</td>
                              </tr>`;
                });
            } else {
                tableHtml += `<tr><td colspan="7">Chưa có giao dịch nào.</td></tr>`;
            }

            tableHtml += `</tbody></table>`;
            mainContent.innerHTML = tableHtml;

        } catch (error) {
            mainContent.innerHTML = '<p style="color: red;">Lỗi khi tải lịch sử giao dịch.</p>';
            console.error('Lỗi fetch giao dịch:', error);
        }


    }


    // ---- XỬ LÝ SỰ KIỆN ----

    // Xử lý khi nhấn vào các link trên menu
    navLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();

            navLinks.forEach(l => l.classList.remove('active'));
            link.classList.add('active');

            const content = link.getAttribute('data-content');

            switch (content) {
                case 'books':
                    loadBooks();
                    break;
                case 'readers':
                    loadReaders();
                    break;
                case 'transactions':
                    loadTransactions();
                    break;
            }
        });
    });

    mainContent.addEventListener('click', async (e) => {
        // Xử lý khi nhấn nút Xóa
        if (e.target.classList.contains('delete-btn')) {
            const bookId = e.target.getAttribute('data-id');

            // Hiển thị hộp thoại xác nhận
            if (confirm(`Bạn có chắc chắn muốn xóa sách có ID: ${bookId}?`)) {
                try {
                    const response = await fetch(`${API_BASE_URL}/book/delete.php`, {
                        method: 'POST', // Hoặc 'DELETE' tùy cấu hình
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ book_id: bookId })
                    });

                    const result = await response.json();
                    alert(result.message);

                    if (response.ok) {
                        loadBooks(); // Tải lại danh sách sách sau khi xóa thành công
                    }
                } catch (error) {
                    console.error('Lỗi khi xóa sách:', error);
                    alert('Đã xảy ra lỗi khi cố gắng xóa sách.');
                }
            }
        }

        // Tạm thời cho nút Sửa và Thêm mới
        if (e.target.classList.contains('edit-btn')) {
            const bookId = e.target.getAttribute('data-id');
            alert(`Chức năng Sửa cho sách ID: ${bookId} sẽ được phát triển!`);
        }

        if (e.target.id === 'add-book-btn') {
            alert('Chức năng Thêm sách mới sẽ được phát triển!');
        }
    });

    // Xử lý khi nhấn nút Đăng xuất
    logoutBtn.addEventListener('click', (e) => {
        e.preventDefault();
        // TODO: Xóa thông tin người dùng khỏi session/localStorage
        alert('Đăng xuất thành công!');
        window.location.href = 'login.html';
    });


    // --- KHỞI CHẠY ---
    // Tải danh sách sách mặc định khi trang vừa được mở
    loadBooks();
});