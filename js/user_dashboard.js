document.addEventListener('DOMContentLoaded', () => {
    // --- LẤY THÔNG TIN NGƯỜI DÙNG ---
    const user = JSON.parse(localStorage.getItem('user'));
    let searchTimeout;

    // Nếu không có thông tin người dùng, chuyển về trang đăng nhập
    if (!user) {
        alert('Vui lòng đăng nhập để tiếp tục.');
        window.location.href = 'login.html';
        return; // Dừng thực thi script
    }

    // --- KHAI BÁO BIẾN TOÀN CỤC ---
    const mainContent = document.getElementById('main-content');
    const navLinks = document.querySelectorAll('.nav-link');
    const logoutBtn = document.getElementById('logoutBtn');
    const API_BASE_URL = '/quanlythuvien/backend';

    // --- CÁC HÀM TẢI DỮ LIỆU ---

    // 1. Tải danh sách SÁCH (phiên bản cho độc giả)
    async function loadBooks(searchTerm = '') {
        try {
            // Nếu có từ khóa thì gọi API tìm kiếm, không thì gọi API lấy toàn bộ
            const url = searchTerm
                ? `${API_BASE_URL}/book/search.php?q=${encodeURIComponent(searchTerm)}`
                : `${API_BASE_URL}/read.php`;

            const response = await fetch(url);
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            const result = await response.json();

            let tableHtml = `<h2>Danh sách sách</h2>
                         <div style="margin-bottom: 15px;">
                            <input type="search" id="book-search-input" placeholder="Tìm theo tên sách, tác giả..." value="${searchTerm}" style="padding: 8px; width: 300px; border-radius: 4px; border: 1px solid #ccc;">
                         </div>
                         <table>
                             <thead>
                                 <tr>
                                     <th>Tên sách</th>
                                     <th>Tác giả</th>
                                     <th>Còn lại</th>
                                     <th>Hành động</th>
                                 </tr>
                             </thead>
                             <tbody>`;

            if (result.data && result.data.length > 0) {
                result.data.forEach(book => {
                    let borrowButton = (book.available_quantity > 0)
                        ? `<button class="borrow-btn" data-id="${book.book_id}">Mượn sách</button>`
                        : `<span style="color: #6c757d;">Hết sách</span>`;

                    tableHtml += `<tr>
                                  <td>${book.book_title}</td>
                                  <td>${book.author}</td>
                                  <td>${book.available_quantity}</td>
                                  <td>${borrowButton}</td>
                              </tr>`;
                });
            } else {
                tableHtml += `<tr><td colspan="4">Không có sách nào trong thư viện.</td></tr>`;
            }
            tableHtml += `</tbody></table>`;
            mainContent.innerHTML = tableHtml;
        } catch (error) {
            mainContent.innerHTML = `<p style="color: red;">Lỗi khi tải danh sách sách.</p>`;
            console.error('Lỗi fetch sách:', error);
        }
    }

    // 2. Tải LỊCH SỬ MƯỢN của người dùng
    async function loadMyHistory() {
        try {
            const response = await fetch(`${API_BASE_URL}/transaction/my_history.php`, {
                credentials: 'include' // Quan trọng: Gửi cookie session để backend biết bạn là ai
            });
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            const result = await response.json();

            let tableHtml = `<h2>Lịch sử mượn sách của tôi</h2>
                             <table>
                                 <thead>
                                     <tr>
                                         <th>Tên sách</th>
                                         <th>Ngày mượn</th>
                                         <th>Hạn trả</th>
                                         <th>Ngày trả</th>
                                         <th>Trạng thái</th>
                                     </tr>
                                 </thead>
                                 <tbody>`;

            if (result.data && result.data.length > 0) {
                result.data.forEach(item => {
                    let statusClass = item.status.toLowerCase();
                    let returnDate = item.return_date ? item.return_date : 'Chưa trả';

                    tableHtml += `<tr>
                                      <td>${item.book_title}</td>
                                      <td>${item.borrow_date}</td>
                                      <td>${item.due_date}</td>
                                      <td>${returnDate}</td>
                                      <td class="status-${statusClass}">${item.status}</td>
                                  </tr>`;
                });
            } else {
                tableHtml += `<tr><td colspan="5">Bạn chưa mượn cuốn sách nào.</td></tr>`;
            }
            tableHtml += `</tbody></table>`;
            mainContent.innerHTML = tableHtml;
        } catch (error) {
            mainContent.innerHTML = `<p style="color: red;">Lỗi khi tải lịch sử mượn sách.</p>`;
            console.error('Lỗi fetch lịch sử:', error);
        }
    }

    // --- CÁC BỘ LẮNG NGHE SỰ KIỆN ---

    // Chuyển tab trên menu
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
                case 'history':
                    loadMyHistory();
                    break;
            }
        });
    });

    // Đăng xuất
    logoutBtn.addEventListener('click', (e) => {
        e.preventDefault();
        localStorage.removeItem('user');
        alert('Đăng xuất thành công!');
        window.location.href = 'login.html';
    });

    // Xử lý khi nhấn nút "Mượn sách"
    mainContent.addEventListener('click', async (e) => {
        if (e.target.classList.contains('borrow-btn')) {
            const bookId = e.target.getAttribute('data-id');
            if (confirm('Bạn có chắc chắn muốn mượn cuốn sách này?')) {
                try {
                    const response = await fetch(`${API_BASE_URL}/transaction/borrow.php`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ book_id: bookId }), // Chỉ cần gửi book_id, reader_id sẽ lấy từ session
                        credentials: 'include'
                    });
                    const result = await response.json();
                    alert(result.message);
                    if (response.ok) {
                        loadBooks(); // Tải lại danh sách sách
                    }
                } catch (error) {
                    alert('Đã xảy ra lỗi khi mượn sách.');
                }
            }
        }
    });

    mainContent.addEventListener('input', (e) => {
        if (e.target.id === 'book-search-input') {
            clearTimeout(searchTimeout);
            const searchTerm = e.target.value;
            searchTimeout = setTimeout(() => {
                loadBooks(searchTerm);
            }, 300);
        }
    });

    // --- KHỞI CHẠY ---
    loadBooks();
});
