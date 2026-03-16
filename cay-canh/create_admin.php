<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if (empty($username) || empty($password) || empty($fullname)) {
        die('Vui lòng nhập đầy đủ thông tin');
    }

    // Check if admin already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        die('Tên đăng nhập đã tồn tại');
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (username, password, fullname, email, role, status) VALUES (?, ?, ?, ?, 'admin', 'active')");
    if ($stmt->execute([$username, $hashed_password, $fullname, $email])) {
        echo 'Tài khoản admin đã được tạo thành công!';
    } else {
        echo 'Lỗi tạo tài khoản';
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Tạo Admin</title>
</head>
<body>
    <h2>Tạo tài khoản Admin</h2>
    <form method="POST">
        <label>Tên đăng nhập: <input type="text" name="username" required></label><br>
        <label>Mật khẩu: <input type="password" name="password" required></label><br>
        <label>Họ tên: <input type="text" name="fullname" required></label><br>
        <label>Email: <input type="email" name="email"></label><br>
        <button type="submit">Tạo Admin</button>
    </form>
</body>
</html>