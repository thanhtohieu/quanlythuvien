-- Bảng Sách
CREATE TABLE books (
    book_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(150),
    publisher VARCHAR(150),
    publication_year YEAR,
    quantity INT DEFAULT 0, -- Tổng số lượng sách
    available_copies INT DEFAULT 0 -- Số lượng sách còn lại có thể mượn
);

-- Bảng Độc Giả
CREATE TABLE readers (
    reader_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    student_id VARCHAR(50) UNIQUE,
    contact_info VARCHAR(100)
);

-- Bảng Giao dịch Mượn/Trả
CREATE TABLE transactions (
    transaction_id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT NOT NULL,
    reader_id INT NOT NULL,
    borrow_date DATE NOT NULL,
    due_date DATE NOT NULL,
    return_date DATE NULL, -- NULL nếu chưa trả
    status ENUM('BORROWED', 'RETURNED', 'OVERDUE') NOT NULL,
    FOREIGN KEY (book_id) REFERENCES books(book_id),
    FOREIGN KEY (reader_id) REFERENCES readers(reader_id)
);  <-- Cần thêm dấu chấm phẩy (;) ở đây
-- Bảng Người dùng để đăng nhập hệ thống
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'reader') DEFAULT 'reader',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);