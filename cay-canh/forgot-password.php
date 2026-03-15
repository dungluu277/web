<?php
$page_title = 'Quên mật khẩu';
require_once 'functions.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email)) {
        $message = 'Vui lòng nhập email của bạn';
        $message_type = 'danger';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Email không hợp lệ';
        $message_type = 'danger';
    } else {
        // Check if email exists
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = 'customer'");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if (!$user) {
            // For security, don't reveal if email exists or not
            $message = 'Nếu email tồn tại trong hệ thống, bạn sẽ nhận được email hướng dẫn đặt lại mật khẩu trong vài phút.';
            $message_type = 'success';
        } else {
            // Generate reset token
            $reset_token = bin2hex(random_bytes(32));
            $reset_expires = date('Y-m-d H:i:s', strtotime('+24 hours'));
            
            // Save token to database
            $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?");
            $stmt->execute([$reset_token, $reset_expires, $user['id']]);
            
            // Send email
            if (sendPasswordResetEmail($user['email'], $reset_token)) {
                // Build reset link for display (especially useful during development)
                $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
                $reset_link = $protocol . "://" . $_SERVER['HTTP_HOST'] . "/cay-canh/reset-password.php?token=" . $reset_token;
                
                $message = 'Link đặt lại mật khẩu đã được gửi. <br><br>';
                $message .= '<strong>Hoặc bạn có thể sử dụng link này:</strong><br>';
                $message .= '<a href="' . htmlspecialchars($reset_link) . '" target="_blank" class="btn btn-sm btn-success mt-2">';
                $message .= 'Đặt lại mật khẩu ngay';
                $message .= '</a>';
                $message_type = 'success';
            } else {
                $message = 'Lỗi khi gửi email. Vui lòng thử lại sau.';
                $message_type = 'danger';
            }
        }
    }
}

include 'header.php';
?>

<div class="row justify-content-center mt-4">
    <div class="col-md-5">
        <div class="card shadow">
            <div class="card-header bg-success text-white">
                <h4 class="mb-0"><i class="fas fa-key"></i> Quên mật khẩu</h4>
            </div>
            <div class="card-body">
                <?php if ($message): ?>
                <div class="alert alert-<?= $message_type ?>">
                    <?= $message ?>
                </div>
                <?php endif; ?>

                <p class="text-muted mb-4">Nhập email của bạn và chúng tôi sẽ gửi cho bạn liên kết để đặt lại mật khẩu.</p>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required autofocus>
                    </div>
                    <button type="submit" class="btn btn-success w-100 btn-lg">
                        <i class="fas fa-envelope"></i> Gửi hướng dẫn đặt lại mật khẩu
                    </button>
                </form>
                
                <hr>
                
                <p class="text-center text-muted">
                    <a href="login.php">← Quay lại đăng nhập</a>
                </p>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
