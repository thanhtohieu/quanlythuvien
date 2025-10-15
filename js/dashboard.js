document.addEventListener('DOMContentLoaded', () => {
    // --- LẤY THÔNG TIN NGƯỜI DÙNG VÀ KIỂM TRA QUYỀN ADMIN ---
    const user = JSON.parse(localStorage.getItem('user'));
    const isAdmin = user && user.role === 'admin';

    if (!user) {
        alert('Vui lòng đăng nhập để tiếp tục.');
        window.location.href = 'login.html';
        return;
    }

    // --- KHAI BÁO BIẾN TOÀN CỤC ---
    const mainContent = document.getElementById('main-content');
    const navLinks = document.querySelectorAll('.nav-link');
    const logoutBtn = document.getElementById('logoutBtn');

    const bookModal = document.getElementById('book-modal');
    const closeModalBtn = document.querySelector('.close-btn');
    const bookForm = document.getElementById('book-form');
    const modalTitle = document.getElementById('modal-title');

    const readerModal = document.getElementById('reader-modal');
    const closeReaderModalBtn = document.querySelector('.close-btn-reader');
    const readerForm = document.getElementById('reader-form');
    const readerModalTitle = document.getElementById('reader-modal-title');

    const borrowModal = document.getElementById('borrow-modal');
    const closeBorrowModalBtn = document.querySelector('.close-btn-borrow');
    const borrowForm = document.getElementById('borrow-form');

    let searchTimeout;

    const API_BASE_URL = '/quanlythuvien/backend';

    // --- CÁC HÀM TẢI DỮ LIỆU ---
    // (Bao gồm loadBooks, loadReaders, loadTransactions đã hoàn thiện)
    async function loadBooks(searchTerm = '') {
        try {
            // URL sẽ thay đổi tùy thuộc vào việc có từ khóa tìm kiếm hay không
            const url = searchTerm
                ? `${API_BASE_URL}/book/search.php?q=${encodeURIComponent(searchTerm)}`
                : `${API_BASE_URL}/read.php`;

            const response = await fetch(url, { credentials: 'include' });
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            const result = await response.json();

            let tableHtml = `<h2>Danh sách sách</h2>`;

            // Thêm ô tìm kiếm vào HTML
            tableHtml += `<div style="margin-bottom: 15px;">
                            <input type="search" id="book-search-input" placeholder="Tìm theo tên sách, tác giả..." value="${searchTerm}" style="padding: 8px; width: 300px; border-radius: 4px; border: 1px solid #ccc;">
                          </div>`;

            if (isAdmin) {
                tableHtml += `<button id="add-book-btn" style="margin-bottom: 10px;">+ Thêm sách mới</button>`;
            }

            tableHtml += `<table>
                                <thead>
                                    <tr>
                                        <th>Mã sách</th>
                                        <th>Tên sách</th>
                                        <th>Tác giả</th>
                                        <th>Tổng số</th>
                                        <th>Còn lại</th>
                                        ${isAdmin ? '<th>Hành động</th>' : '<th>Mượn</th>'}
                                    </tr>
                                </thead>
                                <tbody>`;

            if (result.data && result.data.length > 0) {
                result.data.forEach(book => {
                    let actionCell = '';
                    if (isAdmin) {
                        actionCell = `<td>
                                        <button class="edit-btn" data-id="${book.book_id}">Sửa</button>
                                        <button class="delete-btn" data-id="${book.book_id}">Xóa</button>
                                      </td>`;
                    } else {
                        actionCell = `<td>
                                        <button class="borrow-btn" data-id="${book.book_id}" ${book.available_quantity > 0 ? '' : 'disabled'}>
                                            ${book.available_quantity > 0 ? 'Mượn' : 'Hết sách'}
                                        </button>
                                      </td>`;
                    }

                    tableHtml += `<tr>
                                      <td>${book.book_id}</td>
                                      <td>${book.book_title}</td>
                                      <td>${book.author}</td>
                                      <td>${book.quantity}</td>
                                      <td>${book.available_quantity}</td>
                                      ${actionCell}
                                  </tr>`;
                });
            } else {
                tableHtml += `<tr><td colspan="${isAdmin ? 6 : 6}">Không tìm thấy sách nào.</td></tr>`;
            }
            tableHtml += `</tbody></table>`;
            mainContent.innerHTML = tableHtml;
        } catch (error) {
            mainContent.innerHTML = `<p style="color: red;">Lỗi khi tải danh sách sách.</p>`;
            console.error('Lỗi fetch sách:', error);
        }
    }
    async function loadReaders() {
        if (!isAdmin) return;
        try {
            const response = await fetch(`${API_BASE_URL}/reader/read.php`, { credentials: 'include' });
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            const result = await response.json();

            let tableHtml = `<h2>Danh sách độc giả</h2><button id="add-reader-btn" style="margin-bottom: 10px;">+ Thêm độc giả mới</button><table><thead><tr><th>Mã độc giả</th><th>Họ và tên</th><th>Mã số sinh viên</th><th>Thông tin liên hệ</th><th>Hành động</th></tr></thead><tbody>`;
            if (result.data && result.data.length > 0) {
                result.data.forEach(reader => {
                    tableHtml += `<tr><td>${reader.reader_id}</td><td>${reader.name}</td><td>${reader.student_id}</td><td>${reader.contact_info}</td><td><button class="edit-reader-btn" data-id="${reader.reader_id}">Sửa</button><button class="delete-reader-btn" data-id="${reader.reader_id}">Xóa</button></td></tr>`;
                });
            } else {
                tableHtml += `<tr><td colspan="5">Chưa có độc giả nào.</td></tr>`;
            }
            tableHtml += `</tbody></table>`;
            mainContent.innerHTML = tableHtml;
        } catch (error) {
            mainContent.innerHTML = `<p style="color: red;">Lỗi khi tải danh sách độc giả.</p>`;
            console.error('Lỗi fetch độc giả:', error);
        }
    }

    async function loadTransactions() {
        if (!isAdmin) return;
        try {
            const response = await fetch(`${API_BASE_URL}/transaction/read.php`, { credentials: 'include' });
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            const result = await response.json();

            let tableHtml = `<h2>Lịch sử Mượn/Trả sách</h2>`;
            if (isAdmin) {
                tableHtml += `<button id="add-borrow-btn" style="margin-bottom: 10px;">+ Cho mượn sách</button>`;
            }
            tableHtml += `<table><thead><tr><th>ID</th><th>Tên sách</th><th>Tên độc giả</th><th>Ngày mượn</th><th>Hạn trả</th><th>Ngày trả</th><th>Trạng thái</th></tr></thead><tbody>`;

            if (result.data && result.data.length > 0) {
                result.data.forEach(item => {
                    let returnDate = item.return_date ? item.return_date : 'Chưa trả';
                    let statusHtml;
                    if (isAdmin && item.status !== 'RETURNED') {
                        statusHtml = `<td><select class="status-select" data-id="${item.transaction_id}"><option value="BORROWED" ${item.status === 'BORROWED' ? 'selected' : ''}>BORROWED</option><option value="OVERDUE" ${item.status === 'OVERDUE' ? 'selected' : ''}>OVERDUE</option><option value="RETURNED">RETURNED</option></select></td>`;
                    } else {
                        let statusClass = item.status.toLowerCase();
                        statusHtml = `<td class="status-${statusClass}">${item.status}</td>`;
                    }
                    tableHtml += `<tr><td>${item.transaction_id}</td><td>${item.book_title}</td><td>${item.reader_name}</td><td>${item.borrow_date}</td><td>${item.due_date}</td><td>${returnDate}</td>${statusHtml}</tr>`;
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

    // --- CÁC BỘ LẮNG NGHE SỰ KIỆN (EVENT LISTENERS) ---

    // Chỉ gắn listener cho các chức năng của admin
    if (isAdmin) {
        // Đóng modal Sách
        closeModalBtn.onclick = () => { bookModal.style.display = 'none'; bookForm.reset(); };
        // Đóng modal Độc giả
        closeReaderModalBtn.onclick = () => { readerModal.style.display = 'none'; readerForm.reset(); };
        // Đóng modal Mượn
        closeBorrowModalBtn.onclick = () => { borrowModal.style.display = 'none'; borrowForm.reset(); };

        // Xử lý submit form Sách
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
                    body: JSON.stringify(bookData),
                    credentials: 'include'
                });
                const result = await response.json();
                alert(result.message);
                if (response.ok) {
                    bookModal.style.display = 'none';
                    loadBooks();
                }
            } catch (error) {
                alert('Lỗi khi lưu sách.');
            }
        });

        // Xử lý submit form Độc giả
        readerForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const readerId = document.getElementById('reader_id').value;
            const formData = new FormData(readerForm);
            const readerData = Object.fromEntries(formData.entries());

            const isUpdating = readerId !== '';
            const url = isUpdating ? `${API_BASE_URL}/reader/update.php` : `${API_BASE_URL}/reader/create.php`;

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(readerData),
                    credentials: 'include'
                });
                const result = await response.json();
                alert(result.message);
                if (response.ok) {
                    readerModal.style.display = 'none';
                    loadReaders();
                }
            } catch (error) {
                alert('Lỗi khi lưu thông tin độc giả.');
            }
        });

        // Xử lý submit form Mượn
        borrowForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const bookId = document.getElementById('book-select').value;
            const readerId = document.getElementById('reader-select').value;
            try {
                const response = await fetch(`${API_BASE_URL}/transaction/borrow.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ book_id: bookId, reader_id: readerId }),
                    credentials: 'include'
                });
                const result = await response.json();
                alert(result.message);
                if (response.ok) {
                    borrowModal.style.display = 'none';
                    loadTransactions();
                }
            } catch (error) {
                alert('Lỗi khi tạo phiếu mượn.');
            }
        });
    }

    // Đóng modal khi click ra ngoài (dành cho tất cả user)
    window.onclick = function (event) {
        if (event.target == bookModal || event.target == readerModal || event.target == borrowModal) {
            event.target.style.display = 'none';
        }
    }

    // Chuyển tab trên menu (dành cho tất cả user)
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

    // Đăng xuất (dành cho tất cả user)
    logoutBtn.addEventListener('click', (e) => {
        e.preventDefault();
        localStorage.removeItem('user');
        alert('Đăng xuất thành công!');
        window.location.href = 'login.html';
    });

    // Bộ lắng nghe sự kiện chính cho mainContent (click và change)
    mainContent.addEventListener('click', async (e) => {
        const target = e.target;
        if (!isAdmin && target.classList.contains('borrow-btn')) {
            const bookId = e.target.getAttribute('data-id');
            if (confirm('Bạn có chắc chắn muốn mượn cuốn sách này?')) {
                try {
                    const response = await fetch(`${API_BASE_URL}/transaction/borrow.php`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ book_id: bookId }),
                        credentials: 'include'
                    });
                    const result = await response.json();
                    alert(result.message);
                    if (response.ok) {
                        loadBooks(); // Tải lại danh sách sách để cập nhật số lượng
                    }
                } catch (error) {
                    alert('Lỗi khi mượn sách.');
                }
            }
        }

        // Các nút của bảng Sách
        if (target.id === 'add-book-btn') {
            modalTitle.textContent = 'Thêm sách mới';
            bookForm.reset();
            document.getElementById('book_id').value = '';
            openModal();
        }
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

        // Các nút của bảng Độc giả
        if (target.id === 'add-reader-btn') {
            readerModalTitle.textContent = 'Thêm độc giả mới';
            readerForm.reset();
            document.getElementById('reader_id').value = '';
            readerModal.style.display = 'block';
        }
        if (target.classList.contains('edit-reader-btn')) {
            const readerId = target.getAttribute('data-id');
            try {
                // Gọi API để lấy thông tin chi tiết của độc giả
                const response = await fetch(`${API_BASE_URL}/reader/read_single.php?id=${readerId}`);
                if (!response.ok) {
                    throw new Error('Không tìm thấy thông tin độc giả.');
                }
                const readerData = await response.json();

                // Điền thông tin vào form của độc giả
                readerModalTitle.textContent = 'Sửa thông tin độc giả';
                document.getElementById('reader_id').value = readerData.reader_id;
                document.getElementById('name').value = readerData.name;
                document.getElementById('student_id').value = readerData.student_id;
                document.getElementById('contact_info').value = readerData.contact_info;

                // Mở modal của độc giả
                readerModal.style.display = 'block';

            } catch (error) {
                console.error('Lỗi khi lấy thông tin độc giả:', error);
                alert(error.message);
            }
        }
        if (target.classList.contains('delete-reader-btn')) {
            const readerId = target.getAttribute('data-id');
            if (confirm(`Bạn có chắc muốn xóa độc giả có ID: ${readerId}?`)) {
                try {
                    const response = await fetch(`${API_BASE_URL}/reader/delete.php`, {
                        method: 'POST',
                        body: JSON.stringify({ reader_id: readerId })
                    });
                    const result = await response.json();
                    alert(result.message);
                    if (response.ok) loadReaders();
                } catch (error) {
                    alert('Lỗi khi xóa độc giả.');
                }
            }
        }

        // Nút của bảng Giao dịch
        if (target.id === 'add-borrow-btn') { // Mở modal và tải dữ liệu cho dropdowns
            borrowModal.style.display = 'block';

            // Tải danh sách sách
            const booksResponse = await fetch(`${API_BASE_URL}/read.php`);
            const booksResult = await booksResponse.json();
            const bookSelect = document.getElementById('book-select');
            bookSelect.innerHTML = '<option value="">-- Vui lòng chọn sách --</option>'; // Xóa các tùy chọn cũ
            if (booksResult.data) {
                booksResult.data.forEach(book => {
                    if (book.available_quantity > 0) { // Chỉ hiển thị sách còn
                        bookSelect.innerHTML += `<option value="${book.book_id}">${book.book_title}</option>`;
                    }
                });
            }

            // Tải danh sách độc giả
            const readersResponse = await fetch(`${API_BASE_URL}/reader/read.php`);
            const readersResult = await readersResponse.json();
            const readerSelect = document.getElementById('reader-select');
            readerSelect.innerHTML = '<option value="">-- Vui lòng chọn độc giả --</option>'; // Xóa các tùy chọn cũ
            if (readersResult.data) {
                readersResult.data.forEach(reader => {
                    readerSelect.innerHTML += `<option value="${reader.reader_id}">${reader.name} (${reader.student_id})</option>`;
                });
            }
        }
    });

    mainContent.addEventListener('change', async (e) => {
        if (!isAdmin) return; // Chỉ admin mới có thể thay đổi
        if (e.target.classList.contains('status-select')) {
            const transactionId = e.target.getAttribute('data-id');
            const newStatus = e.target.value;

            if (confirm(`Bạn có chắc chắn muốn đổi trạng thái của giao dịch ID ${transactionId} thành ${newStatus}?`)) {
                try {
                    const response = await fetch(`${API_BASE_URL}/transaction/update.php`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            transaction_id: transactionId,
                            new_status: newStatus
                        }),
                        credentials: 'include' // Đảm bảo gửi session
                    });

                    const result = await response.json();
                    alert(result.message); // Hiển thị thông báo (thành công hoặc thất bại)

                    // --- SỬA LỖI Ở ĐÂY ---
                    // Bất kể API trả về thành công (response.ok) hay thất bại,
                    // chúng ta đều tải lại danh sách để đảm bảo giao diện luôn đúng.
                    loadTransactions();
                    // --- KẾT THÚC SỬA LỖI ---

                } catch (error) {
                    console.error('Lỗi khi cập nhật trạng thái:', error);
                    alert('Đã xảy ra lỗi kết nối.');
                    loadTransactions(); // Cũng tải lại nếu có lỗi mạng
                }
            } else {
                // Nếu người dùng không đồng ý, trả dropdown về trạng thái cũ
                loadTransactions();
            }
        }
    });

    mainContent.addEventListener('input', (e) => {
        if (e.target.id === 'book-search-input') {
            clearTimeout(searchTimeout); // Xóa timeout cũ để tránh gọi API liên tục
            const searchTerm = e.target.value;
            // Chỉ gọi API sau khi người dùng ngừng gõ 300ms
            searchTimeout = setTimeout(() => {
                loadBooks(searchTerm);
            }, 300);
        }
    });

    // --- KHỞI TẠO GIAO DIỆN DỰA TRÊN VAI TRÒ ---
    function initializeUI() {
        if (!isAdmin) {
            // Ẩn các tab của admin
            document.querySelector('a[data-content="readers"]').style.display = 'none';
            document.querySelector('a[data-content="transactions"]').style.display = 'none';
            // Đổi tên tab cho người dùng
            document.querySelector('a[data-content="books"]').textContent = 'Tìm & Mượn sách';
        }
        loadBooks(); // Tải nội dung mặc định
    }

    initializeUI();
});

