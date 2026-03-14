<?php
$page_title = 'Đăng nhập';
require_once 'functions.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';
$redirect = $_GET['redirect'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $redirect = $_POST['redirect'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Vui lòng nhập đầy đủ thông tin';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND password = ? AND role = 'customer'");
        $stmt->execute([$username, md5($password)]);
        $user = $stmt->fetch();
        
        if (!$user) {
            $error = 'Tên đăng nhập hoặc mật khẩu không đúng';
        } elseif ($user['status'] === 'locked') {
            $error = 'Tài khoản của bạn đã bị khóa';
        } else {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_fullname'] = $user['fullname'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['username'] = $user['username'];
            
            if ($redirect) {
                header('Location: ' . $redirect);
            } else {
                header('Location: index.php');
            }
            exit;
        }
    }
}

include 'header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow">
            <div class="card-header bg-success text-white">
                <h4 class="mb-0"><i class="fas fa-sign-in-alt"></i> Đăng nhập</h4>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>

                <form method="POST" id="loginForm">
                    <input type="hidden" name="redirect" value="<?= clean($redirect) ?>">
                    <div class="mb-3">
                        <label class="form-label">Tên đăng nhập</label>
                        <input type="text" name="username" class="form-control" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mật khẩu</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-success w-100 btn-lg"><i class="fas fa-sign-in-alt"></i> Đăng nhập</button>
                </form>
                <hr>
                <p class="text-center">Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a></p>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>