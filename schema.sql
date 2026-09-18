CREATE DATABASE IF NOT EXISTS siswadesk_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE siswadesk_db;

CREATE TABLE IF NOT EXISTS siswa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    kelas VARCHAR(50) NOT NULL,
    status ENUM('Aktif', 'Non-Aktif') NOT NULL DEFAULT 'Aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO admin_users (username, password_hash, nama_lengkap) VALUES
('admin', '$2y$12$3nmZkKgcomLTADou4yr6nOXcU04izUlZqxN0dp.zaAN9fUUP00CeW', 'Administrator')
ON DUPLICATE KEY UPDATE username = VALUES(username), password_hash = VALUES(password_hash), nama_lengkap = VALUES(nama_lengkap);

INSERT INTO siswa (nama, kelas, status) VALUES
('Rizki Putra', 'XII RPL', 'Aktif'),
('Salsa Aulia', 'XI TKJ', 'Aktif'),
('Bima Prakoso', 'X IPA', 'Non-Aktif'),
('Dewi Lestari', 'XI OTKP', 'Aktif')
ON DUPLICATE KEY UPDATE nama = VALUES(nama), kelas = VALUES(kelas), status = VALUES(status);
