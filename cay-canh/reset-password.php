<?php
$page_title = 'Đặt lại mật khẩu';
require_once 'functions.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$message = '';
$message_type = '';
$token = $_GET['token'] ?? '';
$valid_token = false;
$user = null;

// Verify token
if (empty($token)) {
    $message = 'Token không hợp lệ hoặc đã hết hạn.';
    $message_type = 'danger';
} else {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE reset_token = ? AND reset_expires > NOW()");
    $stmt->execute([$token]);
    $user = $stmt->fetch();
    
    if (!$user) {
        $message = 'Token không hợp lệ hoặc đã hết hạn. Vui lòng yêu cầu đặt lại mật khẩu lại.';
        $message_type = 'danger';
    } else {
        $valid_token = true;
    }
}

// Handle password reset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valid_token) {
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    
    $errors = [];
    
    if (empty($password) || empty($confirm)) {
        $errors[] = 'Vui lòng nhập đầy đủ mật khẩu';
    }
    
    if (strlen($password) < 6) {
        $errors[] = 'Mật khẩu tối thiểu 6 ký tự';
    }
    
    if ($password !== $confirm) {
        $errors[] = 'Xác nhận mật khẩu không khớp';
    }
    
    if (empty($errors)) {
        // Update password and clear reset token
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
        $stmt->execute([$hashed_password, $user['id']]);
        
        $message = 'Mật khẩu đã được đặt lại thành công! Bạn có thể đăng nhập ngay bây giờ.';
        $message_type = 'success';
        $valid_token = false;
        
        // Auto redirect after 3 seconds
        echo "<script>setTimeout(function() { window.location.href = 'login.php'; }, 3000);</script>";
    } else {
        $message = implode(', ', $errors);
        $message_type = 'danger';
    }
}

include 'header.php';
?>

<div class="row justify-content-center mt-4">
    <div class="col-md-5">
        <div class="card shadow">
            <div class="card-header bg-success text-white">
                <h4 class="mb-0"><i class="fas fa-lock"></i> Đặt lại mật khẩu</h4>
            </div>
            <div class="card-body">
                <?php if ($message): ?>
                <div class="alert alert-<?= $message_type ?>"><?= $message ?></div>
                <?php endif; ?>

                <?php if ($valid_token): ?>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Mật khẩu mới</label>
                        <input type="password" name="password" class="form-control" required minlength="6" autofocus>
                        <small class="text-muted">Tối thiểu 6 ký tự</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Xác nhận mật khẩu</label>
                        <input type="password" name="confirm_password" class="form-control" required minlength="6">
                    </div>
                    <button type="submit" class="btn btn-success w-100 btn-lg">
                        <i class="fas fa-check"></i> Đặt lại mật khẩu
                    </button>
                </form>
                <?php else: ?>
                <div class="text-center mt-3">
                    <a href="forgot-password.php" class="btn btn-outline-success">
                        <i class="fas fa-redo"></i> Yêu cầu đặt lại mật khẩu lại
                    </a>
                </div>
                <?php endif; ?>
                
                <hr>
                
                <p class="text-center text-muted">
                    <a href="login.php">← Quay lại đăng nhập</a>
                </p>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
