-- =============================================
-- DATABASE: perpustakaan
-- Bootcamp UKK RPL - Aplikasi Peminjaman Buku
-- =============================================

CREATE DATABASE IF NOT EXISTS perpustakaan;
USE perpustakaan;

-- Tabel Users
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','user') DEFAULT 'user',
    phone VARCHAR(20)
);

-- Tabel Categories
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL
);

-- Tabel Books
CREATE TABLE IF NOT EXISTS books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    judul VARCHAR(200) NOT NULL,
    penulis VARCHAR(100) NOT NULL,
    tahun_terbit YEAR,
    stok INT DEFAULT 0,
    cover VARCHAR(255),
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Tabel Peminjaman
CREATE TABLE IF NOT EXISTS peminjaman (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    book_id INT,
    tanggal_pinjam DATE NOT NULL,
    qty INT NOT NULL DEFAULT 1,
    status ENUM('dipinjam','dikembalikan') DEFAULT 'dipinjam',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE
);

-- Data Awal: Admin
INSERT INTO users (name, username, password, role, phone) VALUES
('Administrator', 'admin', MD5('admin123'), 'admin', '081234567890'),
('Siswa Satu', 'siswa1', MD5('siswa123'), 'user', '082345678901');

-- Data Awal: Categories
INSERT INTO categories (category_name) VALUES
('Fiksi'),
('Non-Fiksi'),
('Sains'),
('Teknologi'),
('Sejarah');

-- Data Awal: Books
INSERT INTO books (category_id, judul, penulis, tahun_terbit, stok) VALUES
(1, 'Laskar Pelangi', 'Andrea Hirata', 2005, 5),
(1, 'Bumi Manusia', 'Pramoedya Ananta Toer', 1980, 3),
(3, 'A Brief History of Time', 'Stephen Hawking', 1988, 4),
(4, 'Clean Code', 'Robert C. Martin', 2008, 2),
(2, 'Atomic Habits', 'James Clear', 2018, 6);
