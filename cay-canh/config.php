<?php
session_start();

// Database configuration
define('DB_HOST', 'sql110.infinityfree.com');
define('DB_NAME', 'if0_41400292_caycanh');
define('DB_USER', 'if0_41400292');
define('DB_PASS', 'thanhmtpdepzai');

// Site configuration
define('SITE_NAME', 'Cây Cảnh Xanh');
define('ITEMS_PER_PAGE', 8);
define('UPLOAD_DIR', __DIR__ . '/assets/uploads/');

// Email configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'hauthanhnguyenxuan2006@gmail.com'); // Thay bằng email thật
define('SMTP_PASS', 'your-app-password'); // Thay bằng app password
define('SMTP_FROM', 'noreply@caycanhxanh.com');

// Database connection
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("Lỗi kết nối CSDL: " . $e->getMessage());
}