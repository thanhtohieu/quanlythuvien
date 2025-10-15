document.addEventListener('DOMContentLoaded', () => {
    // --- KHAI BÁO BIẾN TOÀN CỤC ---
    const mainContent = document.getElementById('main-content');
    const navLinks = document.querySelectorAll('.nav-link');
    const logoutBtn = document.getElementById('logoutBtn');

    const bookModal = document.getElementById('book-modal');
    const closeModalBtn = document.querySelector('.close-btn');
    const bookForm = document.getElementById('book-form');
    const modalTitle = document.getElementById('modal-title');

    const API_BASE_URL = '/quanlythuvien/backend';

    // --- CÁC HÀM MỞ/ĐÓNG MODAL ---
    function openModal() { bookModal.style.display = 'block'; }
    function closeModal() { bookModal.style.display = 'none'; bookForm.reset(); }

    // --- CÁC HÀM TẢI DỮ LIỆU ---

    // 1. Tải danh sách SÁCH
    async function loadBooks() {
        try {
            const response = await fetch(`${API_BASE_URL}/read.php`);
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            const result = await response.json();

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
                                         <th>Hành động</th>
                                     </tr>
                                 </thead>
                                 <tbody>`;

            if (result.data && result.data.length > 0) {
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
            mainContent.innerHTML = `<p style="color: red;">Lỗi khi tải danh sách sách.</p>`;
            console.error('Lỗi fetch sách:', error);
        }
    }

    // 2. Tải danh sách ĐỘC GIẢ (Đã hoàn thiện)
    async function loadReaders() {
        try {
            const response = await fetch(`${API_BASE_URL}/reader/read.php`);
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            const result = await response.json();

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
                tableHtml += `<tr><td colspan="4">Chưa có độc giả nào trong hệ thống.</td></tr>`;
            }
            tableHtml += `</tbody></table>`;
            mainContent.innerHTML = tableHtml;
        } catch (error) {
            mainContent.innerHTML = `<p style="color: red;">Lỗi khi tải danh sách độc giả.</p>`;
            console.error('Lỗi fetch độc giả:', error);
        }
    }

    // 3. Tải danh sách GIAO DỊCH (Đã hoàn thiện)
    async function loadTransactions() {
        try {
            const response = await fetch(`${API_BASE_URL}/transaction/read.php`);
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            const result = await response.json();

            let tableHtml = `<h2>Lịch sử Mượn/Trả sách</h2><table>...`; // Giữ nguyên phần thead

            if (result.data && result.data.length > 0) {
                result.data.forEach(item => {
                    let returnDate = item.return_date ? item.return_date : 'Chưa trả';

                    // Tạo dropdown hoặc hiển thị text tùy vào trạng thái
                    let statusHtml;
                    if (item.status === 'RETURNED') {
                        statusHtml = `<td class="status-returned">${item.status}</td>`;
                    } else {
                        statusHtml = `<td>
                                    <select class="status-select" data-id="${item.transaction_id}">
                                        <option value="BORROWED" ${item.status === 'BORROWED' ? 'selected' : ''}>BORROWED</option>
                                        <option value="OVERDUE" ${item.status === 'OVERDUE' ? 'selected' : ''}>OVERDUE</option>
                                        <option value="RETURNED">RETURNED</option>
                                    </select>
                                </td>`;
                    }

                    tableHtml += `<tr>
                                  <td>${item.transaction_id}</td>
                                  <td>${item.book_title}</td>
                                  <td>${item.reader_name}</td>
                                  <td>${item.borrow_date}</td>
                                  <td>${item.due_date}</td>
                                  <td>${returnDate}</td>
                                  ${statusHtml} 
                              </tr>`;
                });
            } else {
                tableHtml += `<tr><td colspan="7">Chưa có giao dịch nào.</td></tr>`;
            }
            tableHtml += `</tbody></table>`;
            mainContent.innerHTML = tableHtml;
        } catch (error) {
            mainContent.innerHTML = `<p style="color: red;">Lỗi khi tải lịch sử giao dịch.</p>`;
            console.error('Lỗi fetch giao dịch:', error);
        }
    }

    // --- CÁC BỘ LẮNG NGHE SỰ KIỆN (EVENT LISTENERS) ---

    closeModalBtn.onclick = closeModal;
    window.onclick = function (event) {
        if (event.target == bookModal) { closeModal(); }
    }

    // Chuyển tab trên menu
    navLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            navLinks.forEach(l => l.classList.remove('active'));
            link.classList.add('active');
            const content = link.getAttribute('data-content');
            switch (content) {
                case 'books': loadBooks(); break;
                case 'readers': loadReaders(); break;
                case 'transactions': loadTransactions(); break;
            }
        });
    });

    // Đăng xuất
    logoutBtn.addEventListener('click', (e) => {
        e.preventDefault();
        alert('Đăng xuất thành công!');
        window.location.href = 'login.html';
    });

    // === PHẦN CODE BỊ THIẾU TRƯỚC ĐÓ ===
    // Xử lý các nút Thêm/Sửa/Xóa trong bảng
    mainContent.addEventListener('click', async (e) => {
        const target = e.target;

        // Nhấn nút THÊM
        if (target.id === 'add-book-btn') {
            modalTitle.textContent = 'Thêm sách mới';
            bookForm.reset();
            document.getElementById('book_id').value = '';
            openModal();
        }

        // Nhấn nút SỬA
        if (target.classList.contains('edit-btn')) {
            const bookId = target.getAttribute('data-id');
            try {
                const response = await fetch(`${API_BASE_URL}/book/read_single.php?id=${bookId}`);
                if (!response.ok) throw new Error('Không tìm thấy thông tin sách.');
                const bookData = await response.json();

                modalTitle.textContent = 'Sửa thông tin sách';
                document.getElementById('book_id').value = bookData.book_id;
                document.getElementById('title').value = bookData.title;
                document.getElementById('author').value = bookData.author;
                document.getElementById('publisher').value = bookData.publisher;
                document.getElementById('publication_year').value = bookData.publication_year;
                document.getElementById('quantity').value = bookData.quantity;

                openModal();
            } catch (error) {
                console.error('Lỗi khi lấy thông tin sách:', error);
                alert(error.message);
            }
        }

        // Nhấn nút XÓA
        if (target.classList.contains('delete-btn')) {
            const bookId = target.getAttribute('data-id');
            if (confirm(`Bạn có chắc chắn muốn xóa sách có ID: ${bookId}?`)) {
                try {
                    const response = await fetch(`${API_BASE_URL}/book/delete.php`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ book_id: bookId })
                    });
                    const result = await response.json();
                    alert(result.message);
                    if (response.ok) {
                        loadBooks();
                    }
                } catch (error) {
                    console.error('Lỗi khi xóa sách:', error);
                    alert('Đã xảy ra lỗi khi cố gắng xóa sách.');
                }
            }
        }
    });

    mainContent.addEventListener('change', async (e) => {
        // Xử lý khi thay đổi dropdown trạng thái
        if (e.target.classList.contains('status-select')) {
            const transactionId = e.target.getAttribute('data-id');
            const newStatus = e.target.value;

            if (confirm(`Bạn có chắc chắn muốn đổi trạng thái của giao dịch ID ${transactionId} thành ${newStatus}?`)) {
                try {
                    const response = await fetch(`${API_BASE_URL}/transaction/update.php`, {
                        method: 'POST', // hoặc PUT
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            transaction_id: transactionId,
                            new_status: newStatus
                        })
                    });

                    const result = await response.json();
                    alert(result.message);

                    if (response.ok) {
                        loadTransactions(); // Tải lại danh sách giao dịch
                    }
                } catch (error) {
                    console.error('Lỗi khi cập nhật trạng thái:', error);
                    alert('Đã xảy ra lỗi.');
                }
            } else {
                // Nếu người dùng không đồng ý, trả dropdown về trạng thái cũ
                loadTransactions();
            }
        }
    });

    // Xử lý submit form
    bookForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const bookId = document.getElementById('book_id').value;

        const formData = new FormData(bookForm);
        const bookData = Object.fromEntries(formData.entries());

        const isUpdating = bookId !== '';
        const url = isUpdating ? `${API_BASE_URL}/book/update.php` : `${API_BASE_URL}/book/create.php`;

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(bookData)
            });

            const result = await response.json();
            alert(result.message);

            if (response.ok) {
                closeModal();
                loadBooks();
            }
        } catch (error) {
            console.error('Lỗi khi lưu sách:', error);
            alert('Đã có lỗi xảy ra.');
        }
    });
    // ===================================

    // --- KHỞI CHẠY ---
    loadBooks();
});