<?php
$page_title = 'Quản lý người dùng';
require_once '../functions.php';

// Xử lý khóa/mở khóa
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $user_id = intval($_POST['user_id'] ?? 0);
    
    if ($action === 'toggle_lock' && $user_id > 0) {
        $stmt = $pdo->prepare("SELECT status FROM users WHERE id = ? AND id != ?");
        $stmt->execute([$user_id, $_SESSION['admin_id']]);
        $user = $stmt->fetch();
        if ($user) {
            $new_status = $user['status'] === 'active' ? 'locked' : 'active';
            $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
            $stmt->execute([$new_status, $user_id]);
            setFlash('success', 'Đã cập nhật trạng thái tài khoản');
        }
    }
    
    if ($action === 'reset_password' && $user_id > 0) {
        $new_pass = md5('123456');
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$new_pass, $user_id]);
        setFlash('success', 'Đã khởi tạo mật khẩu về "123456"');
    }
    
    if ($action === 'add') {
        $username = trim($_POST['username'] ?? '');
        $fullname = trim($_POST['fullname'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $role = $_POST['role'] ?? 'customer';
        
        if ($username && $fullname) {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if (!$stmt->fetch()) {
                $stmt = $pdo->prepare("INSERT INTO users (username, password, fullname, email, phone, role, status) VALUES (?, ?, ?, ?, ?, ?, 'active')");
                $stmt->execute([$username, md5('123456'), $fullname, $email, $phone, $role]);
                setFlash('success', 'Thêm tài khoản thành công (mật khẩu mặc định: 123456)');
            } else {
                setFlash('danger', 'Tên đăng nhập đã tồn tại');
            }
        }
    }
    
    header('Location: users.php');
    exit;
}

$users = $pdo->query("SELECT * FROM users ORDER BY role DESC, created_at DESC")->fetchAll();

include 'header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2><i class="fas fa-users"></i> Quản lý người dùng</h2>
    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addUserModal"><i class="fas fa-plus"></i> Thêm tài khoản</button>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-dark">
                <tr><th>ID</th><th>Username</th><th>Họ tên</th><th>Email</th><th>SĐT</th><th>Vai trò</th><th>Trạng thái</th><th>Thao tác</th></tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= $u['id'] ?></td>
                    <td><code><?= htmlspecialchars($u['username']) ?></code></td>
                    <td><?= htmlspecialchars($u['fullname']) ?></td>
                    <td><?= htmlspecialchars($u['email'] ?? '') ?></td>
                    <td><?= htmlspecialchars($u['phone'] ?? '') ?></td>
                    <td><span class="badge bg-<?= $u['role'] === 'admin' ? 'danger' : 'info' ?>"><?= $u['role'] === 'admin' ? 'Admin' : 'Khách hàng' ?></span></td>
                    <td><span class="badge bg-<?= $u['status'] === 'active' ? 'success' : 'secondary' ?>"><?= $u['status'] === 'active' ? 'Hoạt động' : 'Đã khóa' ?></span></td>
                    <td>
                        <?php if ($u['id'] != $_SESSION['admin_id']): ?>
                        <form method="POST" style="display:inline">
                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                            <input type="hidden" name="action" value="toggle_lock">
                            <button type="submit" class="btn btn-sm btn-<?= $u['status'] === 'active' ? 'warning' : 'success' ?>" title="<?= $u['status'] === 'active' ? 'Khóa' : 'Mở khóa' ?>">
                                <i class="fas fa-<?= $u['status'] === 'active' ? 'lock' : 'unlock' ?>"></i>
                            </button>
                        </form>
                        <form method="POST" style="display:inline">
                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                            <input type="hidden" name="action" value="reset_password">
                            <button type="submit" class="btn btn-sm btn-info btn-delete" title="Reset mật khẩu">
                                <i class="fas fa-key"></i>
                            </button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal thêm user -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Thêm tài khoản</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Tên đăng nhập <span class="text-danger">*</span></label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Họ tên <span class="text-danger">*</span></label>
                        <input type="text" name="fullname" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Số điện thoại</label>
                        <input type="tel" name="phone" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Vai trò</label>
                        <select name="role" class="form-select">
                            <option value="customer">Khách hàng</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <small class="text-muted">Mật khẩu mặc định: 123456</small>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Thêm</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>